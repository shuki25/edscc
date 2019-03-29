<?php

namespace App\Command;

use App\Entity\ImportQueue;
use App\Entity\User;
use App\Repository\CapiQueueRepository;
use App\Repository\Oauth2Repository;
use App\Service\ErrorLogHelper;
use App\Service\OAuth2Helper;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CapiQueueCommand extends Command
{
    protected static $defaultName = 'app:capi-queue';
    /**
     * @var OAuth2Helper
     */
    private $helper;
    /**
     * @var CapiQueueRepository
     */
    private $capiQueueRepository;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var ErrorLogHelper
     */
    private $errorLogHelper;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Oauth2Repository
     */
    private $oauth2Repository;

    public function __construct(OAuth2Helper $helper, CapiQueueRepository $capiQueueRepository, ParameterBagInterface $bag, ErrorLogHelper $errorLogHelper, EntityManagerInterface $entityManager, Oauth2Repository $oauth2Repository)
    {
        parent::__construct();
        $this->helper = $helper;
        $this->capiQueueRepository = $capiQueueRepository;
        $this->bag = $bag;
        $this->errorLogHelper = $errorLogHelper;
        $this->entityManager = $entityManager;
        $this->oauth2Repository = $oauth2Repository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Process Frontier CAPI queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $folder_path = $this->bag->get('command.fileupload.path');
        $capi_journal_url = getenv('CAPI_JOURNAL_API');

        if (!is_readable($folder_path) || !is_dir($folder_path)) {
            $io->error($folder_path . " is not readable or is not a directory.");
            $this->errorLogHelper->addErrorMsgToErrorLog('CapiQueue', $entry->getId(), $e);
            return;
        }

        $max = $this->capiQueueRepository->totalCountInQueue();
        $entry = $this->capiQueueRepository->findOneBy(['progress_code' => 'Q'], ['journal_date' => 'asc']);

        $section1 = $output->section();
        $section2 = $output->section();
        $progressBar = new ProgressBar($section1, $max);
        $downloadBar = new ProgressBar($section2);
        $progressBar->setFormat('very_verbose');
        $downloadBar->setFormat('very_verbose');
        $progressBar->start();
        $downloadBar->start();

        $start_time = microtime(true);

        /**
         * @var User $prev_user
         */
        $prev_user = null;

        while (isset($entry)) {
            $error = 0;

            if (isset($prev_user)) {
                if ($prev_user->getId() == $entry->getUser()->getId()) {
                    $end_time = microtime(true);
                    while ($end_time - $start_time < 0.5) {
                        usleep(1000);
                        $end_time = microtime(true);
                    }
                }
            }

            try {
                $file_name = sprintf("%s.txt", md5(time() . random_bytes(5)));
                $file_path = sprintf("%s/%s", $folder_path, $file_name);
                $fh = fopen($file_path, "w");
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                $this->errorLogHelper->addErrorMsgToErrorLog('CapiQueue', $entry->getId(), $e);
                return;
            }

            $entry->setProgressCode('L');
            $this->entityManager->flush();

            $client = new Client();
            $target_url = sprintf("%s/%s", $capi_journal_url, $entry->getJournalDate()->format("Y/m/d"));

            try {
                $response = $client->request('GET', $target_url, [
                    'headers' => [
                        'Accept-Encoding' => 'application/gzip',
                        'Authorization' => sprintf("%s %s", $entry->getUser()->getOauth2()->getTokenType(), $entry->getUser()->getOauth2()->getAccessToken())
                    ],
                    'progress' => function ($downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes) use ($downloadBar) {
                        $downloadBar->setMaxSteps($downloadTotal);
                        $downloadBar->setProgress($downloadedBytes);
                    },
                    'sink' => $fh,
                    'proxy' => [
                        'https' => 'tcp://192.168.2.85:8888'
                    ],
                    'verify' => false
                ]);
            } catch (RequestException $e) {
                $io->error($e->getMessage() . '\nUnable to fetch data. Aborted.\n');
                fclose($fh);
                $this->errorLogHelper->addErrorMsgToErrorLog('CapiQueue', $entry->getId(), $e);
                $entry->setProgressCode('E');
                $error = 1;
            } finally {
                $downloadBar->finish();
                fclose($fh);
            }

            if (!$error && $response->getStatusCode() == 200) {
                $journal_file = sprintf("CAPI-Journal.%s.log", $entry->getJournalDate()->format('ymdHis'));
                $import_queue = new ImportQueue();
                $import_queue->setUser($entry->getUser())
                    ->setProgressCode('Q')
                    ->setGameDatetime($entry->getJournalDate())
                    ->setOriginalFilename($journal_file)
                    ->setUploadFilename($file_name);
                $this->entityManager->persist($import_queue);
                $entry->setProgressCode('D');
                $entry->getUser()->getOauth2()->setLastFetchedOn($entry->getJournalDate());
            } elseif ($response->getStatusCode() == 204) {
                $entry->setProgressCode('N');
                unlink($file_path);
            } elseif ($response->getStatusCode() == 422) {
                $entry->setProgressCode('E');
                $this->errorLogHelper->addSimpleMsgToErrorLog('CapiQueue', $response->getStatusCode(), $response->getReasonPhrase());
                unlink($file_path);
            } else {
                $this->errorLogHelper->addSimpleMsgToErrorLog('CapiQueue', $response->getStatusCode(), $response->getReasonPhrase());
            }

            $this->entityManager->flush();
            $progressBar->advance();
            unset($client);

            $prev_user = $entry->getUser();
            $entry = $this->capiQueueRepository->findOneBy(['progress_code' => 'Q'], ['journal_date' => 'asc']);
            $start_time = microtime(true);
        }

        $list = $this->oauth2Repository->findBy(['sync_status' => true]);
        foreach ($list as $item) {
            $item->setSyncStatus(false);
            $this->entityManager->flush();
        }

        $io->success('CAPI Queue processed');
    }
}

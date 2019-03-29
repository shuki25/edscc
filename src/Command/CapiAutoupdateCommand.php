<?php

namespace App\Command;

use App\Entity\CapiQueue;
use App\Repository\CapiQueueRepository;
use App\Repository\Oauth2Repository;
use App\Service\ErrorLogHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CapiAutoupdateCommand extends Command
{
    protected static $defaultName = 'app:capi-autoupdate';
    /**
     * @var Oauth2Repository
     */
    private $oauth2Repository;
    /**
     * @var ErrorLogHelper
     */
    private $errorLogHelper;
    /**
     * @var CapiQueueRepository
     */
    private $capiQueueRepository;
    private $utc;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Oauth2Repository $oauth2Repository, ErrorLogHelper $errorLogHelper, CapiQueueRepository $capiQueueRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->oauth2Repository = $oauth2Repository;
        $this->errorLogHelper = $errorLogHelper;
        $this->capiQueueRepository = $capiQueueRepository;
        $this->utc = new \DateTimeZone('UTC');
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Autoupdate Frontier CAPI journal data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $list = $this->oauth2Repository->findBy([
            'connection_flag' => true,
            'auto_download' => true
        ]);

        $progressBar = new ProgressBar($output, count($list));
        $progressBar->setFormat('very_verbose');
        $progressBar->start();

        foreach ($list as $item) {
            for ($i = 24; $i > 0; $i--) {
                $target_date = new \DateTime('now', $this->utc);
                $target_date->setTime(0, 0, 0);
                $interval = sprintf("%d day", $i * -1);
                $target_date->add(\DateInterval::createFromDateString($interval));
                $capi_queue = $this->capiQueueRepository->findOneBy(['user' => $item->getUser(), 'journal_date' => $target_date]);
                if (empty($capi_queue)) {
                    $capi_queue = new CapiQueue();
                    $capi_queue->setUser($item->getUser())
                        ->setJournalDate($target_date)
                        ->setProgressCode('Q');
                    $this->entityManager->persist($capi_queue);
                }
            }
            $this->entityManager->flush();
        }

        $io->success(count($list) . ' users processed. Frontier CAPI Autoupdate queue is in progress.');
    }
}

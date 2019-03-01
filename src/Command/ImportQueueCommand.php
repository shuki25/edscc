<?php

namespace App\Command;

use App\Entity\Commander;
use App\Entity\User;
use App\Repository\CommanderRepository;
use App\Repository\ImportQueueRepository;
use App\Repository\UserRepository;
use App\Service\ParseLogHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImportQueueCommand extends Command
{
    protected static $defaultName = 'app:import-queue';
    /**
     * @var ImportQueueRepository
     */
    private $importQueueRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var CommanderRepository
     */
    private $commanderRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var Commander $commander
     */
    private $commander;
    /**
     * @var User $user
     */
    private $user;

    private $utc;
    /**
     * @var ParseLogHelper
     */
    private $parseLogHelper;

    public function __construct(ImportQueueRepository $importQueueRepository, UserRepository $userRepository, CommanderRepository $commanderRepository, EntityManagerInterface $entityManager, ParameterBagInterface $bag, ParseLogHelper $parseLogHelper)
    {
        parent::__construct();

        $this->importQueueRepository = $importQueueRepository;
        $this->userRepository = $userRepository;
        $this->commanderRepository = $commanderRepository;
        $this->entityManager = $entityManager;
        $this->bag = $bag;
        $this->parseLogHelper = $parseLogHelper;

        $this->utc = new \DateTimeZone('UTC');

    }

    protected function configure()
    {
        $this
            ->setDescription('Process import queue for parsing of Player Journal log files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->entityManager;
        $folder_path = $this->bag->get('command.fileupload.path');
        $num_records = 0;

        if (!is_readable($folder_path) || !is_dir($folder_path)) {
            $io->error($folder_path . " is not readable or is not a directory.");
            return;
        }

        $entry = $this->importQueueRepository->findOneBy(['progress_code' => 'Q'], ['game_datetime' => 'asc']);

        $max = $this->importQueueRepository->totalCountInQueue();
        $progressBar = new ProgressBar($output, $max);
        $progressBar->setFormat('very_verbose');
        $progressBar->start();

        while (isset($entry)) {
            $entry->setProgressCode('L');

            $entry->setTimeStarted();
            $this->commander = $this->commanderRepository->findOneBy(['user' => $entry->getUser()]);
            $this->user = $entry->getUser();

            if (is_null($this->commander)) {
                $this->commander = new Commander();
                $this->commander->setUser($entry->getUser());
            }

            $file_path = sprintf("%s%s", $folder_path, $entry->getUploadFilename());

            try {
                $fh = fopen($file_path, 'r');
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }

            if (!is_readable($file_path) || $fh === false) {
                $io->error($file_path . ' is not readable. Skipping.');
                $entry->setProgressCode('E');
                $em->persist($entry);
                $em->flush();
            } else {
                while (($data = fgets($fh)) !== false) {
                    $this->parseLogHelper->parseEntry($em, $this->user, $this->commander, $data);
                    time_nanosleep(0, 1000000);
                }
                $entry->setProgressCode('P');
                $em->persist($entry);
                $em->persist($this->commander);
                $em->flush();
            }
            time_nanosleep(0, 35000000);
            $progressBar->advance();
            $num_records++;

            $entry = $this->importQueueRepository->findOneBy(['progress_code' => 'Q'], ['game_datetime' => 'asc']);
        }
        $progressBar->finish();
        $io->success(sprintf('%d Player Journal logs processed.', $num_records));
    }
}

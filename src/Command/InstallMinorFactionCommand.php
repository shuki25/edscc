<?php

namespace App\Command;

use App\Entity\MinorFaction;
use App\Repository\CrimeRepository;
use App\Repository\EarningHistoryRepository;
use App\Repository\FactionActivityRepository;
use App\Repository\MinorFactionRepository;
use App\Repository\PowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class InstallMinorFactionCommand extends Command
{
    protected static $defaultName = 'app:install-minor-faction';

    private $utc;
    /**
     * @var MinorFactionRepository
     */
    private $minorFactionRepository;
    /**
     * @var PowerRepository
     */
    private $powerRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var CrimeRepository
     */
    private $crimeRepository;
    /**
     * @var EarningHistoryRepository
     */
    private $earningHistoryRepository;
    /**
     * @var FactionActivityRepository
     */
    private $factionActivityRepository;

    public function __construct(MinorFactionRepository $minorFactionRepository, PowerRepository $powerRepository, EntityManagerInterface $entityManager, ParameterBagInterface $bag, CrimeRepository $crimeRepository, EarningHistoryRepository $earningHistoryRepository, FactionActivityRepository $factionActivityRepository)
    {
        parent::__construct();
        $this->minorFactionRepository = $minorFactionRepository;
        $this->powerRepository = $powerRepository;
        $this->entityManager = $entityManager;

        $this->utc = new \DateTimeZone('UTC');
        $this->bag = $bag;
        $this->crimeRepository = $crimeRepository;
        $this->earningHistoryRepository = $earningHistoryRepository;
        $this->factionActivityRepository = $factionActivityRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Download and import minor factions data')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Update with new records')
            ->addOption('convert', 'c', InputOption::VALUE_NONE, 'Convert old id to new id. Only once.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $uri = getenv('MINOR_FACTION_URL');
        $folder_path = $this->bag->get('command.fileupload.path');
        $file_path = sprintf("%s%s", $folder_path, "factions.jsonl");
        $archive_file = sprintf("%s%s", $folder_path, "factions.jsonl.old");

        if ($input->getOption('convert')) {
            $progressBar = new ProgressBar($output);
            $io->title('Converting eddb minor faction id to the new internal id');
            $progressBar->setFormat('very_verbose');

            $crime = $this->crimeRepository->findAll();
            $totalCount = count($crime);
            $earningHistory = $this->earningHistoryRepository->findMinorFactionNotNull();
            $totalCount += count($earningHistory);
            $factionActivity = $this->factionActivityRepository->findMinorFactionNotNull();
            $totalCount += count($factionActivity);
            $progressBar->setMaxSteps($totalCount);
            $count = 0;
            $progressBar->start();

            /**
             * @var MinorFaction $minorFactionTable
             */
            $minorFactionTable = $this->minorFactionRepository->findAll();
            $minorFactionRef = [];

            foreach ($minorFactionTable as $item) {
                $minorFactionRef[$item->getEddbId()] = $item;
            }

            foreach ($crime as $row) {
                if (!is_null($row->getMinorFaction())) {
                    $row->setMinorFaction($minorFactionRef[$row->getMinorFaction()->getId()]);
                }
                $count++;

                if (!($count % 150)) {
                    $progressBar->setProgress($count);
                }
                if (!($count % 500)) {
                    $this->entityManager->flush();
                }
            }

            foreach ($earningHistory as $row) {
                if (!is_null($row->getMinorFaction())) {
                    $row->setMinorFaction($minorFactionRef[$row->getMinorFaction()->getId()]);
                }
                $count++;

                if (!($count % 150)) {
                    $progressBar->setProgress($count);
                }
                if (!($count % 500)) {
                    $this->entityManager->flush();
                }
            }

            foreach ($factionActivity as $row) {
                if (!is_null($row->getMinorFaction())) {
                    $row->setMinorFaction($minorFactionRef[$row->getMinorFaction()->getId()]);
                }
                if (!is_null($row->getTargetMinorFaction())) {
                    $row->setMinorFaction($minorFactionRef[$row->getTargetMinorFaction()->getId()]);
                }
                $count++;

                if (!($count % 150)) {
                    $progressBar->setProgress($count);
                }
                if (!($count % 500)) {
                    $this->entityManager->flush();
                }
            }

            $this->entityManager->flush();

        } else {

            $io->title("Download and import Minor Faction data into database");

            if (file_exists($file_path)) {
                try {
                    rename($file_path, $archive_file);
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                    die;
                }
            }

            $progressBar = new ProgressBar($output);
            $io->text("Downloading factions.jsonl from eddb.io");
            $progressBar->setFormat('very_verbose');
            $progressBar->start();

            try {
                $fh = fopen($file_path, "w");
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                die;
            }

            $client = new Client();

            try {
                $response = $client->request('GET', $uri, [
                    'headers' => ['Accept-Encoding' => 'gzip'],
                    'progress' => function ($downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes) use ($progressBar) {
                        $progressBar->setMaxSteps($downloadTotal);
                        $progressBar->setProgress($downloadedBytes);
                    },
                    'sink' => $fh,
                ]);
            } catch (RequestException $e) {
                $io->error($e->getMessage() . '\nUnable to download factions.jsonl. Aborted.\n');
                fclose($fh);
                die;
            } finally {
                fclose($fh);
            }

            if ($response->getStatusCode() != 200) {
                $io->error($response->getReasonPhrase() . '\nUnable to download faction.jsonl. Aborted.');
                die;
            }
            $progressBar->finish();
            echo("\n\n");
            $max = filesize($file_path);

            $count = 0;
            $row = 0;
            $progressBar = new ProgressBar($output, $max);
            $io->text('Updating minor faction data in the database');
            $progressBar->setFormat('very_verbose');
            $progressBar->start();

            if (!$input->getOption('update')) {
                $cmd = $this->entityManager->getClassMetadata(MinorFaction::class);
                $connection = $this->entityManager->getConnection();
                $dbPlatform = $connection->getDatabasePlatform();
                $connection->query('SET FOREIGN_KEY_CHECKS=0');
                $q = $dbPlatform->getTruncateTableSQL($cmd->getTableName());
                $connection->executeUpdate($q);
//            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            }

            $powerFaction = $this->powerRepository->findAll();

            foreach ($powerFaction as $row) {
                $minorFaction = null;

                if ($input->getOption('update')) {
                    $minorFaction = $this->minorFactionRepository->findOneBy(['name' => $row->getName()]);
                }
                if (!is_object($minorFaction)) {
                    $minorFaction = new MinorFaction();
                    $minorFaction->setName($row->getName())
                        ->setPlayerFaction(0)
                        ->setEddbId(null);
                    $this->entityManager->persist($minorFaction);
                    $count++;
                }
            }

            try {
                $fh = fopen($file_path, 'r');
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                die;
            }

            $rowCount = 0;

            if (!is_readable($file_path) || $fh === false) {
                $io->error($file_path . ' is not readable. Aborted.');
                die;
            } else {
                while (($data = fgets($fh)) !== false) {
                    $rowCount++;
                    $json = json_decode($data, true);

                    $minorFaction = null;

                    if ($input->getOption('update')) {
                        $minorFaction = $this->minorFactionRepository->findOneBy(['eddb_id' => $json['id']]);
                    }

                    if (!is_object($minorFaction)) {
                        $minorFaction = new MinorFaction();
                        $minorFaction->setName($json['name'])
                            ->setPlayerFaction($json['is_player_faction'] ? 1 : 0)
                            ->setEddbId($json['id']);
                        $this->entityManager->persist($minorFaction);
                    }

                    $count += strlen($data);
                    if (!($rowCount % 250)) {
                        $progressBar->setProgress($count);
                    }
                    if (!($rowCount % 1000)) {
                        $this->entityManager->flush();
                    }
                }
            }
            $this->entityManager->flush();
            $progressBar->finish();
        }

        $io->success('Task Completed.');
    }
}

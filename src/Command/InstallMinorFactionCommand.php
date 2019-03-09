<?php

namespace App\Command;

use App\Entity\MinorFaction;
use App\Repository\MinorFactionRepository;
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
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(MinorFactionRepository $minorFactionRepository, EntityManagerInterface $entityManager, ParameterBagInterface $bag)
    {
        parent::__construct();
        $this->minorFactionRepository = $minorFactionRepository;
        $this->entityManager = $entityManager;

        $this->utc = new \DateTimeZone('UTC');
        $this->bag = $bag;
    }

    protected function configure()
    {
        $this
            ->setDescription('Download and import minor factions data')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Update with new records');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Download and import Minor Faction data into database");

        $uri = getenv('MINOR_FACTION_URL');
        $folder_path = $this->bag->get('command.fileupload.path');
        $file_path = sprintf("%s%s", $folder_path, "factions.jsonl");
        $archive_file = sprintf("%s%s", $folder_path, "factions.jsonl.old");

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

        try {
            $fh = fopen($file_path, 'r');
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            die;
        }

        if (!is_readable($file_path) || $fh === false) {
            $io->error($file_path . ' is not readable. Aborted.');
            die;
        } else {
            while (($data = fgets($fh)) !== false) {
                $row++;
                $json = json_decode($data, true);

                $minorFaction = null;

                if ($input->getOption('update')) {
                    $minorFaction = $this->minorFactionRepository->findOneBy(['id' => $json['id']]);
                }

                if (!is_object($minorFaction)) {
                    $minorFaction = new MinorFaction();
                    $minorFaction->setId($json['id'])
                        ->setName($json['name'])
                        ->setPlayerFaction($json['is_player_faction'] ? 1 : 0);
                    $this->set_entity($minorFaction, $json['id'], $row);
                }

                $count += strlen($data);
                if (!($row % 250)) {
                    $progressBar->setProgress($count);
                }
            }
        }
        $this->entityManager->flush();
        $progressBar->finish();

        $io->success('Task Completed.');
    }

    private function set_entity($entity, $id, $count)
    {
        if ($id) {
            $this->entityManager->persist($entity);
            $metadata = $this->entityManager->getClassMetadata(get_class($entity));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
        } else {
            $this->entityManager->persist($entity);
        }
        if (!($count % 1000)) {
            $this->entityManager->flush();
        }
    }
}

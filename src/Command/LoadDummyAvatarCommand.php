<?php

namespace App\Command;

use GuzzleHttp\Exception\RequestException;
use Nyholm\DSN;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LoadDummyAvatarCommand extends Command
{
    protected static $defaultName = 'app:load-dummy-avatar';

    private $dbh;

    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        parent::__construct();

        $this->bag = $bag;
        $params = $this->bag->get('pdo_connection_string');

        $dsnObject = new DSN($params);

        $dsn = sprintf('%s:host=%s;dbname=%s', $dsnObject->getProtocol(), $dsnObject->getFirstHost(), $dsnObject->getDatabase());

        try {
            $this->dbh = new \PDO($dsn, $dsnObject->getUsername(), $dsnObject->getPassword(), [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING]);
        } catch (\Exception $e) {
            dump($e->getMessage());
            dump($dsnObject);
            dd($dsn);
        }
    }

    protected function configure()
    {
        $this->setDescription('Update user profile with dummy avatars');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $client = new \GuzzleHttp\Client();

        if (!is_object($client)) {
            $io->error('Failed to create GuzzleHttp instance');
            return;
        }

        $sql = "select id from user";
        $rs = $this->dbh->prepare($sql);
        $rs->execute();
        $data = $rs->fetchAll(\PDO::FETCH_ASSOC);
        $numUsers = count($data);

        $progressBar = new ProgressBar($output);
        $progressBar->start($numUsers);

        try {
            $response = $client->request('GET', 'https://uifaces.co/api', [
                'headers' => [
                    'X-API-KEY' => 'c9e5d9d672eac17ff65d34ef9a911f'
                ],
                'query' => [
                    'limit' => $numUsers,
                    'random' => 1
                ]
            ]);
        } catch (RequestException $e) {
            $io->error($e->getMessage());
            return;
        }

        $json = $response->getBody()->getContents();
        $avatar = json_decode($json, true);

        foreach ($data as $i => $row) {
            $sql = "update user set avatar_url=? where id=?";
            $params = [
                $avatar[$i]['photo'],
                $row['id']
            ];
            $rs = $this->dbh->prepare($sql);
            try {
                $rs->execute($params);
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
            $progressBar->advance();
        }
        $progressBar->finish();

        $io->success("Dummy avatars have been updated in users' profile");
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-02-01
 * Time: 10:23
 */

namespace App\Menu;

use App\Entity\User;
use App\Repository\UserRepository;
use Nyholm\DSN;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class MenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var Yaml
     */
    private $yaml;
    private $menu;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    private $dbh;
    private $utc;
    /**
     * @var User
     */
    private $user;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(ParameterBagInterface $bag, Security $security, UserRepository $userRepository)
    {
        $this->yaml = new Yaml();
        try {
            $this->menu = $this->yaml->parseFile('../config/menu.yaml');
        } catch (ParseException $e) {
            echo $e->getMessage();
        }

        $this->bag = $bag;
        $params = $this->bag->get('pdo_connection_string');

        $this->utc = new \DateTimeZone('utc');
        $dsnObject = new DSN($params);

        $dsn = sprintf('%s:host=%s;dbname=%s', $dsnObject->getProtocol(), $dsnObject->getFirstHost(), $dsnObject->getDatabase());

        try {
            $this->dbh = new \PDO($dsn, $dsnObject->getUsername(), $dsnObject->getPassword(), [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"']);
        } catch (\Exception $e) {
            dump($e->getMessage());
            dump($dsnObject);
            dd($dsn);
        }

        $this->security = $security;
        $this->userRepository = $userRepository;
    }

    public function userMenu(array $options = [])
    {
        $data = $this->menu['user'];
        $username = $this->security->getUser()->getUsername();
        $this->user = $this->userRepository->findOneBy(['email' => $username]);
        $userid = $this->user->getId();

        foreach ($data as $i => $item) {
            if (isset($item['counter'])) {
                $rs = $this->dbh->prepare($item['counter_sql']);
                $rs->execute([':user' => $userid]);
                $counter = $rs->fetchColumn(0);
                $data[$i]['counter_value'] = $counter;
            }
        }
        return $data;
    }

    public function adminMenu(array $options = [])
    {
        $data = $this->menu['admin'];
        $username = $this->security->getUser()->getUsername();
        $this->user = $this->userRepository->findOneBy(['email' => $username]);
        $userid = $this->user->getId();

        foreach ($data as $i => $item) {
            if (isset($item['counter'])) {
                $rs = $this->dbh->prepare($item['counter_sql']);
                $rs->execute([':user' => $userid]);
                $counter = $rs->fetchColumn(0);
                $data[$i]['counter_value'] = $counter;
            }
        }

        return $data;
    }
}
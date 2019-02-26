<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Nyholm\DSN;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class MembersController extends AbstractController
{

    private $dbh;
    private $utc;

    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
        $params = $this->bag->get('pdo_connection_string');

        $this->utc = new \DateTimeZone('utc');
        $dsnObject = new DSN($params);

        $dsn = sprintf('%s:host=%s;dbname=%s', $dsnObject->getProtocol(), $dsnObject->getFirstHost(), $dsnObject->getDatabase());

        try {
            $this->dbh = new \PDO($dsn, $dsnObject->getUsername(), $dsnObject->getPassword());
        } catch (\Exception $e) {
            dump($e->getMessage());
            dump($dsnObject);
            dd($dsn);
        }

    }

    /**
     * @Route("/members", name="app_members")
     */
    public function members(UserRepository $userRepository)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $rank = [];
        $total_earned = [];
        $members = $userRepository->findBy(['Squadron' => $user->getSquadron()->getId()], ['commander_name' => 'ASC']);
        foreach ($members as $member) {
            $rank[$member->getId()] = 'Unranked';
            $total_earned[$member->getId()] = 0;
        }

        $sql = "call p_commander_earning_rank(?)";
        try {
            $rs = $this->dbh->prepare($sql);
            $rs->execute([$user->getSquadron()->getId()]);
            $rank_list = $rs->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
            die;
        }

        foreach ($rank_list as $i => $item) {
            $rank[$item['user_id']] = $item['rank'];
            $total_earned[$item['user_id']] = $item['total_earned'];
        }

        return $this->render('members/index.html.twig', [
            'members' => $members,
            'rank' => $rank,
            'total_earned' => $total_earned
        ]);
    }
}

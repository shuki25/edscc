<?php

namespace App\Controller;

use App\Entity\User;
use Nyholm\DSN;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class DashboardController extends AbstractController
{
    private $dbh;
    private $utc;

    /**
     * @var ParameterBagInterface
     */
    private $bag;

    /**
     * @var User $user
     */
    private $user;

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
     * @Route("/", name="dashboard")
     */
    public function squadron_dashboard()
    {
        $this->user = $this->getUser();
        $squadron_id = $this->user->getSquadron()->getId();

        $sql = "select count(id) from user where squadron_id=?";
        $data['members'] = $this->fetch_sql_single_scalar($sql, [$squadron_id]);

        $sql = "select sum(reward) from earning_history where squadron_id=? and earned_on=?";
        $this->fetch_stats($sql, $squadron_id, 'earnings', $data);

        $sql = "select count(eh.id) from earning_history eh join earning_type et on eh.earning_type_id=et.id where squadron_id=? and et.mission_flag='1' and earned_on=?";
        $this->fetch_stats($sql, $squadron_id, 'missions', $data);

        $sql = "select sum(bodies_found) from activity_counter where squadron_id=? and activity_date=?";
        $this->fetch_stats($sql, $squadron_id, 'bodies_found', $data);

        return $this->render('dashboard/index.html.twig', [
            'title' => 'Squadron Dashboard',
            'data' => $data
        ]);
    }

    /**
     * @Route("/player/dashboard", name="player_dashboard")
     */
    public function player_dashboard()
    {
        $this->user = $this->getUser();
        $squadron_id = $this->user->getSquadron()->getId();

        $sql = "select count(id) from user where squadron_id=?";
        $data['members'] = $this->fetch_sql_single_scalar($sql, [$squadron_id]);

        $sql = "select sum(reward) from earning_history where squadron_id=? and earned_on=?";
        $this->fetch_stats($sql, $squadron_id, 'earnings', $data);

        $sql = "select count(eh.id) from earning_history eh join earning_type et on eh.earning_type_id=et.id where squadron_id=? and et.mission_flag='1' and earned_on=?";
        $this->fetch_stats($sql, $squadron_id, 'missions', $data);

        $sql = "select sum(bodies_found) from activity_counter where squadron_id=? and activity_date=?";
        $this->fetch_stats($sql, $squadron_id, 'bodies_found', $data);

        return $this->render('dashboard/player_dashboard.html.twig', [
            'title' => 'Player Dashboard',
            'data' => $data
        ]);
    }

    private function fetch_sql($sql, $params = null)
    {
        $rs = $this->dbh->prepare($sql);
        if (is_array($params)) {
            $rs->execute($params);
        }
        return $rs->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function fetch_sql_single_scalar($sql, $params = null)
    {
        $rs = $this->dbh->prepare($sql);
        if (is_array($params)) {
            $rs->execute($params);
        }
        return $rs->fetchColumn(0);
    }

    private function fetch_stats($sql, $squadron_id, $prefix, &$data)
    {
        $date_yesterday = date_format(new \DateTime('yesterday'), 'Y-m-d');
        $date_today = date_format(new \DateTime('today'), 'Y-m-d');

        $yesterday = $this->fetch_sql_single_scalar($sql, [$squadron_id, $date_yesterday]) ?: 1;
        $today = $this->fetch_sql_single_scalar($sql, [$squadron_id, $date_today]) ?: 0;
        $data[$prefix] = $today;
        $data[$prefix . '_pct'] = number_format((($today - $yesterday) / $yesterday) * 100, 1);
    }
}

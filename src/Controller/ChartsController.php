<?php

namespace App\Controller;

use Nyholm\DSN;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChartsController extends AbstractController
{
    private $dbh;

    /**
     * @var ParameterBagInterface
     */
    private $bag;
    private $utc;
    /**
     * @var TranslatorInterface
     */
    private $translation;

    public function __construct(ParameterBagInterface $bag, TranslatorInterface $translation)
    {
        $this->bag = $bag;
        $params = $this->bag->get('pdo_connection_string');

        $dsnObject = new DSN($params);

        $dsn = sprintf('%s:host=%s;dbname=%s', $dsnObject->getProtocol(), $dsnObject->getFirstHost(), $dsnObject->getDatabase());

        try {
            $this->dbh = new \PDO($dsn, $dsnObject->getUsername(), $dsnObject->getPassword());
        } catch (\Exception $e) {
            dump($e->getMessage());
            dump($dsnObject);
            dd($dsn);
        }

        $this->utc = new \DateTimeZone('UTC');
        $this->translation = $translation;
    }

    /**
     * @Route("/chart/script/{name}", name="chart_script")
     * @IsGranted("ROLE_USER")
     */
    public function fetchScript($name)
    {
        switch ($name) {
            case 'squadron_earningxxx':
                $content = $this->renderView('charts/squadron_earning.js.twig');
                break;
            default:
                $content = $this->renderView('charts/chart_script.js.twig', [
                    'name' => $name,
                    'name_path' => 'chart_' . $name
                ]);
                break;
        }

        $response = new Response($content, 200, ['Content-Type' => 'application/javascript']);

        return $response;
    }

    /**
     * @Route("/chart/daily_earning", name="chart_squadron_earning")
     * @IsGranted("ROLE_USER")
     */
    public function dailyEarning(Request $request)
    {
        $squadron_id = $this->getUser()->getSquadron()->getID();
        $results['label'] = [$this->translation->trans("Your Squadron"), $this->translation->trans("Other Squadrons (Average)")];
        $data = [];

        $sql = "select sum(reward) as y, earned_on as x from earning_history where squadron_id=? and earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $data = [];
        $sql = "select round(avg(total_earned)) as y, earned_on as x from v_squadron_daily_total where earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $results['status'] = 200;
        $jsonResponse = new JsonResponse($results);

        return $jsonResponse;
    }

    /**
     * @Route("/chart/bounty_earning", name="chart_bounty_earning")
     * @IsGranted("ROLE_USER")
     */
    public function bountyEarning(Request $request)
    {
        $squadron_id = $this->getUser()->getSquadron()->getID();
        $results['label'] = [$this->translation->trans("Your Squadron"), $this->translation->trans("Other Squadrons (Average)")];
        $data = [];

        $sql = "select sum(reward) as y, earned_on as x from earning_history where earning_type_id='1' and squadron_id=? and earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $data = [];
        $sql = "select round(avg(total_earned)) as y, earned_on as x from v_squadron_type_total where earning_type_id='1' and earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $results['status'] = 200;
        $jsonResponse = new JsonResponse($results);

        return $jsonResponse;
    }

    /**
     * @Route("/chart/exploration_earning", name="chart_exploration_earning")
     * @IsGranted("ROLE_USER")
     */
    public function explorationEarning(Request $request)
    {
        $squadron_id = $this->getUser()->getSquadron()->getID();
        $results['label'] = [$this->translation->trans("Your Squadron"), $this->translation->trans("Other Squadrons (Average)")];
        $data = [];

        $sql = "select sum(reward) as y, earned_on as x from earning_history where earning_type_id='4' and squadron_id=? and earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $data = [];
        $sql = "select round(avg(total_earned)) as y, earned_on as x from v_squadron_type_total where earning_type_id='4' and earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $results['status'] = 200;
        $jsonResponse = new JsonResponse($results);

        return $jsonResponse;
    }

    /**
     * @Route("/chart/trade_earning", name="chart_trade_earning")
     * @IsGranted("ROLE_USER")
     */
    public function tradeEarning(Request $request)
    {
        $squadron_id = $this->getUser()->getSquadron()->getID();
        $results['label'] = [$this->translation->trans("Your Squadron"), $this->translation->trans("Other Squadrons (Average)")];
        $data = [];

        $sql = "select sum(reward) as y, earned_on as x from earning_history where earning_type_id in ('5','6') and squadron_id=? and earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $sql = "select round(avg(total_earned)) as y, earned_on as x from v_squadron_type_total where earning_type_id='5' and earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $buy[$row['x']] = $row['y'];
        }
        $buy = $this->fillMissingDates($buy, 30);

        $data = [];
        $sql = "select round(avg(total_earned)) as y, earned_on as x from v_squadron_type_total where earning_type_id='6' and earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'] + $buy[$row['x']];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $results['status'] = 200;
        $jsonResponse = new JsonResponse($results);

        return $jsonResponse;
    }

    /**
     * @Route("/chart/mission_earning", name="chart_mission_earning")
     * @IsGranted("ROLE_USER")
     */
    public function missionEarning(Request $request)
    {
        $squadron_id = $this->getUser()->getSquadron()->getID();
        $results['label'] = [$this->translation->trans("Your Squadron"), $this->translation->trans("Other Squadrons (Average)")];
        $data = [];

        $sql = "select total_earned as y, earned_on as x from v_squadron_mission_total where squadron_id=? and earned_on between now() - interval 30 day and now()";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $data = [];
        $sql = "select round(avg(total_earned)) as y, earned_on as x from v_squadron_mission_total where earned_on between now() - interval 30 day and now() group by earned_on";
        $tmp = $this->fetchSql($sql, [$squadron_id]);
        foreach ($tmp as $i => $row) {
            $data[$row['x']] = $row['y'];
        }
        $results['data'][] = $this->fillMissingDates($data, 30);

        $results['status'] = 200;
        $jsonResponse = new JsonResponse($results);

        return $jsonResponse;
    }

    private function fillMissingDates($data, $num_days_ago)
    {
        $current_date = new \DateTime('now', $this->utc);
        $one_day_interval = new \DateInterval('P1D');
        $current_date->sub(new \DateInterval('P30D'));
        $new_data = [];

        for ($d = 0; $d <= $num_days_ago; $d++) {
            $date = $current_date->format('Y-m-d');
            $new_data[$date] = isset($data[$date]) ? $data[$date] : 0;
            $current_date->add($one_day_interval);
        }
        return $new_data;
    }

    private function fetchSql($sql, $params = null)
    {
        $rs = $this->dbh->prepare($sql);
        if (is_array($params)) {
            $rs->execute($params);
        }
        return $rs->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function fetchSqlSingleScalar($sql, $params = null)
    {
        $rs = $this->dbh->prepare($sql);
        if (is_array($params)) {
            $rs->execute($params);
        }
        return $rs->fetchColumn(0);
    }
}

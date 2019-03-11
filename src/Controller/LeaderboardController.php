<?php

namespace App\Controller;

use App\Entity\User;
use Nyholm\DSN;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LeaderboardController extends AbstractController
{

    private $dbh;
    private $utc;

    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ParameterBagInterface $bag, TranslatorInterface $translator)
    {
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

        $this->translator = $translator;
    }

    /**
     * @Route("/leaderboard", name="leaderboard")
     */
    public function leaderboard(Request $request)
    {

        $user = $this->getUser();
        $report_id = ($request->request->get('report')) ?: 1;

        $sql = "select * from x_leaderboard_report order by title";
        $rs = $this->dbh->prepare($sql);
        $rs->execute([]);
        $report_picker = $rs->fetchAll(\PDO::FETCH_ASSOC);

        $sql = "select * from x_leaderboard_report where id=?";
        $rs = $this->dbh->prepare($sql);
        $rs->execute([$report_id]);
        $report = $rs->fetch(\PDO::FETCH_ASSOC);

        $report['header'] = json_decode($report['header']);
        $report['columns'] = json_decode($report['columns']);

        return $this->render('leaderboard/leaderboard_datatables.html.twig', [
            'report' => $report,
            'report_id' => $report_id,
            'report_picker' => $report_picker
        ]);
    }

    /**
     * @Route("/leaderboard/ajax/{slug}/{token}", name="leaderboard_table", methods={"POST"} )
     */
    public function leaderboardTable($slug, $token, Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('ajax_leaderboard', $token)) {
            $datatable = [
                'error' => $translator->trans('Unauthorized')
            ];
            return new JsonResponse($datatable);
        }

        $datatable_params = $request->request->all();
        //dd($datatable_params);

        $sql = "select * from x_leaderboard_report where id=?";
        $rs = $this->dbh->prepare($sql);
        $rs->execute([$slug]);
        $report = $rs->fetch(\PDO::FETCH_ASSOC);

        $report['header'] = json_decode($report['header']);
        $report['columns'] = json_decode($report['columns']);
        $parameters = json_decode($report['parameters']);

        $sql = $report['parameters_sql'];
        $rs = $this->dbh->prepare($sql);
        $rs->execute([$user->getId()]);
        $parameters_data = $rs->fetch(\PDO::FETCH_ASSOC);

        $params = [];
        if (!is_null($parameters[0])) {
            foreach ($parameters[0] as $i => $item) {
                $params[] = $parameters_data[$item];
            }
        }

        $counter_params = [];
        if (!is_null($parameters[1])) {
            foreach ($parameters[1] as $i => $item) {
                $counter_params[] = $parameters_data[$item];
            }
        }

        $order_by = $datatable_params['columns'][$datatable_params['order'][0]['column']]['data'];
        $order_dir = $datatable_params['order'][0]['dir'] == "asc" ? "asc" : "desc";

        if (!is_null($report['cast_columns'])) {
            $cast_columns = json_decode($report['cast_columns'], true);
        }

        if (!is_null($report['sort_columns'])) {
            $sort_columns = json_decode($report['sort_columns'], true);
        }

        try {
            $sql = $report['count_sql'];
            $rs = $this->dbh->prepare($sql);
            $rs->execute($counter_params);
            $rowcount = $rs->fetchColumn(0);

            $order_by_str = " order by `%s` %s";
            if (isset($cast_columns[$order_by])) {
                $order_by_str = " order by cast(`%s` as " . $cast_columns[$order_by] . ") %s";
            }
            $order_by_str .= " limit %d offset %d";

            if (isset($sort_columns[$order_by])) {
                $sql = $report['sql'] . sprintf($order_by_str, $sort_columns[$order_by], $order_dir, (int)$datatable_params['length'], (int)$datatable_params['start']);
            } else {
                $sql = $report['sql'] . sprintf($order_by_str, $order_by, $order_dir, (int)$datatable_params['length'], (int)$datatable_params['start']);
            }

            $rs = $this->dbh->prepare($sql);
            $rs->execute($params);
            $datatable['data'] = $rs->fetchAll(\PDO::FETCH_ASSOC);
            $has_data = $rs->rowCount();
        } catch (\PDOException $e) {
            echo $e->getMessage();
            die;
        }

        if ($has_data) {
            if (!is_null($datatable['data'][0]['commander_name'])) {
                foreach ($datatable['data'] as $i => $row) {
                    $datatable['data'][$i]['commander_name'] = $this->translator->trans('CMDR %name%', ['%name%' => $row['commander_name']]);
                }
            }
        }

        $datatable['recordsFiltered'] = $rowcount;
        $datatable['recordsTotal'] = $rowcount;
        $datatable['draw'] = $datatable_params['draw'];
//        $datatable['order_by'] = $order_by;
//        $datatable['sql'] = $sql;
//        $datatable['params'] = $params;

        // dd($datatable);
        $response = new JsonResponse($datatable);

        return $response;
    }

}

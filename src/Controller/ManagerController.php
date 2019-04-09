<?php

namespace App\Controller;

use App\Entity\CustomFilter;
use App\Entity\User;
use App\Repository\CustomFilterRepository;
use App\Repository\UserRepository;
use App\Service\ErrorLogHelper;
use Nyholm\DSN;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagerController extends AbstractController
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
            $this->dbh = new \PDO($dsn, $dsnObject->getUsername(), $dsnObject->getPassword(), [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"', \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
        } catch (\Exception $e) {
            dump($e->getMessage());
            dump($dsnObject);
            dd($dsn);
        }

        $this->translator = $translator;
    }

    /**
     * @Route("/manager/reports", name="mgr_reports")
     * @IsGranted("CAN_VIEW_REPORTS")
     */
    public function managerReports(Request $request, SessionInterface $session, CustomFilterRepository $customFilterRepository, UserRepository $userRepository)
    {

        /**
         * @var User $mgr_user
         */
        $mgr_user = $this->getUser();
        $squadron_members = $userRepository->findBy(['Squadron' => $mgr_user->getSquadron()->getId()], ['commander_name' => 'ASC']);
        $report_id = ($request->request->get('report')) ?: 1;
        $user_id = ($request->request->get('user')) ?: $mgr_user->getId();
        $filter = $request->request->get('filter');
        $token = $request->request->get('_token');
        $reset = $request->request->get('reset');
        $save_filter = ($request->request->get('save_filter')) ?: 0;
        $save_state = $request->query->get('save_state');
        $em = $this->getDoctrine()->getManager();

        if ($save_filter) {
            $title = ucwords(trim($request->request->get('filter_title')));
            $rule = base64_decode($request->request->get('filter_rule'));

            if ($this->isCsrfTokenValid('filter_save', $token)) {
                $filter = $customFilterRepository->findOneBy(['user' => $mgr_user, 'scope' => 'ManagerReport', 'title' => $title]);

                if (is_null($filter)) {
                    $filter = new CustomFilter();
                    $filter->setUser($mgr_user);
                    $em->persist($filter);
                }

                $filter->setScope('ManagerReport')
                    ->setTitle($title)
                    ->setFilterRule($rule);

                $em->flush();
                $this->addFlash('success', $this->translator->trans('Filter Saved'));
            } else {
                $this->addFlash('alert', $this->translator->trans('Expired CSRF Token. Please try again.'));
            }
            $filter = json_decode($rule, true);
        }

        if ($save_state && !$save_filter) {
            $report_id = $session->get('report_id');
            $filter = $session->get('filter');
        }

        if (!is_null($filter) && !$reset) {
            if ($this->isCsrfTokenValid('report_filter', $token)) {
                if ($session->get('report_id') != $report_id) {
                    $session->remove('filter');
                }
                $session->set('report_id', $report_id);
                $session->set('filter', $filter);
            }
        } else {
            $session->set('report_id', $report_id);
            $session->remove('filter');
            $filter = [];
        }

        $sql = "select * from x_player_report order by title";
        $rs = $this->dbh->prepare($sql);
        $rs->execute([]);
        $report_picker = $rs->fetchAll(\PDO::FETCH_ASSOC);

        $sql = "select * from x_player_report where id=?";
        $rs = $this->dbh->prepare($sql);
        $rs->execute([$report_id]);
        $report = $rs->fetch(\PDO::FETCH_ASSOC);

        $report['header'] = json_decode($report['header']);
        $report['columns'] = json_decode($report['columns']);

        $saved_filter_list = $customFilterRepository->findBy(['user' => $mgr_user->getId(), 'scope' => 'ManagerReport'], ['title' => 'asc']);

        return $this->render('manager/report_datatables.html.twig', [
            'report' => $report,
            'report_id' => $report_id,
            'user_id' => $user_id,
            'report_picker' => $report_picker,
            'squadron_members' => $squadron_members,
            'filter' => $filter,
            'filter_base64' => base64_encode(json_encode($filter)),
            'saved_filter_list' => $saved_filter_list
        ]);
    }

    /**
     * @Route("/manager/remove_custom_filter/{slug}/{report}/{token}", name="mgr_remove_custom_filter", methods={"GET"})
     * @IsGranted("CAN_VIEW_REPORTS")
     */
    public function removeCustomFilter($slug, $report, $token, CustomFilterRepository $customFilterRepository)
    {
        $custom_filter = $customFilterRepository->findOneBy(['user' => $this->getUser()->getId(),
            'id' => $slug,
            'scope' => 'ManagerReport'
        ]);

        $em = $this->getDoctrine()->getManager();

        if ($this->isCsrfTokenValid('mgr_remove_custom_filter', $token)) {
            if (isset($custom_filter)) {
                $em->remove($custom_filter);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('Custom Filter Removed'));
            } else {
                $this->addFlash('alert', $this->translator->trans('Internal Error. Custom Filter was not Removed.'));
            }
        }
        return $this->redirectToRoute('app_player_reports', ['save_state' => true, 'report' => $report]);
    }

    /**
     * @Route("/manager/ajax/custom_filter", name="mgr_get_custom_filter", methods={"POST"})
     * @IsGranted("CAN_VIEW_REPORTS")
     */
    public function getCustomFilter(Request $request, CustomFilterRepository $customFilterRepository)
    {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('mgr_custom_filter', $token)) {

            $data['filter'] = [];

            $custom_filter = $customFilterRepository->findOneBy(['user' => $this->getUser()->getId(),
                'id' => $request->request->get('id'),
                'scope' => 'ManagerReport'
            ]);

            if (is_null($custom_filter)) {
                $data['status'] = 302;
            } else {
                $data['filter'] = json_decode($custom_filter->getFilterRule(), true);
                $data['status'] = 200;
            }
        } else {
            $data['status'] = 401;
        }

        $response = new Response(json_encode($data, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    /**
     * @Route("/manager/ajax/{slug}/{token}", name="mgr_report_table", methods={"POST"} )
     * @IsGranted("CAN_VIEW_REPORTS")
     */
    public function reportTable($slug, $token, Request $request, SessionInterface $session, ErrorLogHelper $errorLogHelper)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $filter = $session->get('filter');

        if (!$this->isCsrfTokenValid('mgr_ajax_report', $token)) {
            $datatable = [
                'error' => $translator->trans('Unauthorized')
            ];
            $response = new Response(json_encode($datatable, JSON_UNESCAPED_UNICODE));
            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            return $response;
        }

        $datatable_params = $request->request->all();
        //dd($datatable_params);

        $sql = "select * from x_player_report where id=?";
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

        $filter_sql = "";
        $filter_rules = json_decode($report['filter_rules'], true);

        if (!is_null($filter_rules)) {
            foreach ($filter_rules as $section => $rule) {
                $prepare_sql = "";
                switch ($section) {
                    case 'date':
                        if ($filter['start_date'] || $filter['end_date']) {
                            foreach ($rule['fields'] as $column) {
                                if ($prepare_sql) {
                                    $prepare_sql .= " or ";
                                }
                                $date_sql = $this->makeDateRangeSql($column, $filter['start_date'], $filter['end_date'], $params);
                                $prepare_sql .= sprintf("(%s)", $date_sql);
                            }
                            if ($filter_sql) {
                                $filter_sql .= sprintf(" and (%s)", $prepare_sql);
                            } else {
                                $filter_sql .= sprintf(" %s (%s)", $rule['operator'], $prepare_sql);
                            }
                        }
                        break;
                    case 'keyword':
                        if (trim($filter['keyword']) != "") {
                            foreach ($rule['fields'] as $column) {
                                if ($prepare_sql) {
                                    $prepare_sql .= " or ";
                                }
                                $prepare_sql .= $column . " like ?";
                                $params[] = "%" . $filter['keyword'] . "%";
                            }
                            $filter_sql .= sprintf(" %s (%s)", $rule['operator'], $prepare_sql);
                        }
                        break;
                    case 'static':
                        $filter_sql .= sprintf(" %s", $rule['string']);
                        break;
                    default:
                        break;
                }
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
        } catch (\PDOException $e) {
            $errorLogHelper->addErrorMsgToErrorLog('ReportTable', $slug, $e, [$sql, $params]);
        }

        $filtered_count = 0;
        if ($filter_sql) {
            $sql = sprintf("select count(*) from (%s) a", sprintf($report['sql'], $filter_sql));
            try {
                $rs = $this->dbh->prepare($sql);
                $rs->execute($params);
                $filtered_count = $rs->fetchColumn(0);
            } catch (\PDOException $e) {
                $errorLogHelper->addErrorMsgToErrorLog('ReportTable', $slug, $e, [$sql, $params]);
            }
        }

        $order_by_str = " order by `%s` %s";
        if (isset($cast_columns[$order_by])) {
            $order_by_str = " order by cast(`%s` as " . $cast_columns[$order_by] . ") %s";
        }
        $order_by_str .= " limit %d offset %d";

        if (isset($sort_columns[$order_by])) {
            if ($filter_sql) {
                $sql = sprintf($report['sql'], $filter_sql) . sprintf($order_by_str, $sort_columns[$order_by], $order_dir, (int)$datatable_params['length'], (int)$datatable_params['start']);
            } else {
                $sql = sprintf($report['sql'], "") . sprintf($order_by_str, $sort_columns[$order_by], $order_dir, (int)$datatable_params['length'], (int)$datatable_params['start']);
            }
        } else {
            if ($filter_sql) {
                $sql = sprintf($report['sql'], $filter_sql) . sprintf($order_by_str, $order_by, $order_dir, (int)$datatable_params['length'], (int)$datatable_params['start']);
            } else {
                $sql = sprintf($report['sql'], "") . sprintf($order_by_str, $order_by, $order_dir, (int)$datatable_params['length'], (int)$datatable_params['start']);
            }
        }

//        $has_data = 0;
//        $datatable['data'] = [];

        try {
            $rs = $this->dbh->prepare($sql);
            $rs->execute($params);
            $datatable['data'] = $rs->fetchAll(\PDO::FETCH_ASSOC);
            $has_data = $rs->rowCount();
        } catch (\PDOException $e) {
            $errorLogHelper->addErrorMsgToErrorLog('ReportTable', $slug, $e, [$sql, $params]);
        }

        if ($has_data) {
            if (isset($datatable['data'][0]['commander_name'])) {
                foreach ($datatable['data'] as $i => $datum) {
                    $datatable['data'][$i]['commander_name'] = $this->translator->trans('CMDR %name%', ['%name%' => $datum['commander_name']]);
                }
            }
            if (isset($report['trans_columns'])) {
                $trans_columns = json_decode($report['trans_columns'], true);
                foreach ($datatable['data'] as $i => $datum) {
                    foreach ($trans_columns as $column) {
                        $datatable['data'][$i][$column] = $this->translator->trans($datatable['data'][$i][$column]);
                    }
                }
            }
        }

        $datatable['recordsFiltered'] = isset($filter) ? $filtered_count : $rowcount;
        $datatable['recordsTotal'] = $rowcount;
        $datatable['draw'] = $datatable_params['draw'];
        $datatable['order_by'] = $order_by;
        $datatable['sql'] = $sql;
        $datatable['params'] = $params;
        $datatable['has_data'] = $has_data;

//        if(isset($filter)) {
//            dd($datatable);
//        }

        $response = new Response(json_encode($datatable, JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    private function makeDateRangeSql($field, $start_date, $end_date, &$params)
    {
        $sql = "";

        if ($start_date) {
            $sql .= sprintf("%s >= ?", $field);
            $params[] = $start_date;
        }

        if ($end_date) {
            if ($sql) {
                $sql .= " and ";
            }
            $sql .= sprintf("%s <= ?", $field);
            $params[] = $end_date;
        }
        return $sql;
    }
}

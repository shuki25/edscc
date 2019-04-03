<?php

namespace App\Controller;

use App\Entity\CustomFilter;
use App\Entity\User;
use App\Repository\CustomFilterRepository;
use App\Repository\UserRepository;
use Nyholm\DSN;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
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
}

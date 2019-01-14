<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AnnouncementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Knp\Bundle\TimeBundle\DateTimeFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjaxController extends AbstractController
{
    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    private function prepareDTOptions($params)
    {
        $orders = $params['order'];
        $columns = $params['columns'];

        foreach ($orders as $key => $order)
        {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        $params['order'] = $orders;

        return $params;

    }

    private function postProcessDTDateData($data, $source, $target)
    {

        $formatter = new DateTimeFormatter($this->translator);

        $dt = $data['data'];

        foreach($dt as $i=>$row) {
            if(isset($row[$source])) {
               $dt[$i] = array_merge($dt[$i],[$target => $formatter->formatDiff($row[$source], new \DateTime('now'))]);
            }
            else {
                switch($target) {
                    case 'last_login_at':
                        $msg = $this->translator->trans('Never');
                        break;

                    default:
                        $msg = $this->translator->trans('No Date Available');
                        break;
                }
                $dt[$i] = array_merge($dt[$i],[$target => $msg]);
            }
        }
        $data['data'] = $dt;

        return $data;
    }

    /**
     * @Route("/ajax/members/{token}", name="ajax_members", methods={"POST"} )
     */
    public function ajax_members($token, Request $request, UserRepository $repository, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $squadron = $user->getSquadron()->getId();
        $this->em = $this->getDoctrine()->getManager();
        $this->translator = $translator;

        if(!$this->isCsrfTokenValid('ajax_members', $token)) {
            $datatable = [
                'error' => $translator->trans('Unauthorized')
            ];
            return new JsonResponse($datatable);
        }

        $params = $request->request->all();
        $params = $this->prepareDTOptions($params);
        $dt = $repository->findAllBySquadronDatatables($squadron, $params);

        // Post process the data to clean up formatting

        $dt = $this->postProcessDTDateData($dt, 'join_date', 'join_date');
        $dt = $this->postProcessDTDateData($dt, 'last_login_at', 'last_login_at');

        foreach ($dt['data'] as $i=>$row) {
            $dt['data'][$i]['commander_name'] = $translator->trans('CMDR %name%',['%name%' => $row['commander_name']]);
            $dt['data'][$i]['status'] = sprintf("<span class=\"label label-%s\">%s</span>",$row['tag'], $translator->trans($row['status']));
            $dt['data'][$i]['action'] = $this->renderView('admin/list_members_action.html.twig', [
                'id' => $row['id']
            ]);
        }

        $datatable = array_merge($dt, [
            'draw' => $params['draw']
        ]);

        $response = new JsonResponse($datatable);

        return $response;
    }

    /**
     * @Route("/ajax/announcements/{token}", name="ajax_announcements", methods={"POST"} )
     */
    public function ajax_announcements($token, Request $request, AnnouncementRepository $repository, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $squadron = $user->getSquadron()->getId();
        $this->em = $this->getDoctrine()->getManager();
        $this->translator = $translator;

        if(!$this->isCsrfTokenValid('ajax_announcements', $token)) {
            $datatable = [
                'error' => $translator->trans('Unauthorized')
            ];
            return new JsonResponse($datatable);
        }

        $params = $request->request->all();
        $params = $this->prepareDTOptions($params);
        $dt = $repository->findAllBySquadronDatatables($squadron, $params);

        // Post process the data to clean up formatting

        $dt = $this->postProcessDTDateData($dt, 'created_in', 'created_in');
        $dt = $this->postProcessDTDateData($dt, 'publish_in', 'publish_in');

        foreach ($dt['data'] as $i=>$row) {
            $dt['data'][$i]['author'] = $translator->trans('CMDR %name%',['%name%' => $row['author']]);
            $dt['data'][$i]['action'] = $this->renderView('admin/list_announcements_action.html.twig', [
                'id' => $row['id']
            ]);
        }

        $datatable = array_merge($dt, [
            'draw' => $params['draw']
        ]);

        $response = new JsonResponse($datatable);

        return $response;
    }
}

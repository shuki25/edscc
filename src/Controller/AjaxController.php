<?php

namespace App\Controller;

use App\Entity\ImportQueue;
use App\Entity\SquadronTags;
use App\Entity\User;
use App\Repository\AnnouncementRepository;
use App\Repository\ImportQueueRepository;
use App\Repository\SquadronRepository;
use App\Repository\SquadronTagsRepository;
use App\Repository\StatusRepository;
use App\Repository\TagsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Knp\Bundle\TimeBundle\DateTimeFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
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

    private $utc;


    public function __construct()
    {
        $this->utc = new \DateTimeZone('UTC');
    }


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

    private function postProcessDTDateData($data, $source, $target, $ago = true)
    {

        $formatter = new DateTimeFormatter($this->translator);
        $dt = $data['data'];

        foreach($dt as $i=>$row) {
            if(isset($row[$source])) {
                if($ago) {
                    $dt[$i] = array_merge($dt[$i],[$target => $formatter->formatDiff($row[$source], new \DateTime('now'))]);
                }
                else {
                    $dt[$i] = array_merge($dt[$i],[$target => date_format($row[$source], 'Y-m-d H:i:s') . " UTC"]);
                }
            }
            else {
                switch($target) {
                    case 'last_login_at':
                        $msg = $this->translator->trans('Never');
                        break;

                    default:
                        $msg = "";
                        break;
                }
                $dt[$i] = array_merge($dt[$i],[$target => $msg]);
            }
        }
        $data['data'] = $dt;

        return $data;
    }

    /**
     * @Route("/ajax/members/list/{token}", name="ajax_members", methods={"POST"} )
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
                'id' => $row['id'],
                'status' => $row['status']
            ]);
        }

        $datatable = array_merge($dt, [
            'draw' => $params['draw']
        ]);

        $response = new JsonResponse($datatable);

        return $response;
    }

    /**
     * @Route("/ajax/members/manage", name="ajax_manage_member", methods={"POST"})
     */
    public function manage_member(Request $request, UserRepository $userRepository, StatusRepository $statusRepository, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $squadron_id = $user->getSquadron()->getId();

        $em = $this->getDoctrine()->getManager();
        $data['status'] = 500;
        $data['require_reason'] = false;

        $token = $request->request->get('_token');
        $action = $request->request->get('action');

        if(!$this->isCsrfTokenValid('manage_member', $token)) {
            $data['status'] = 403;
            $data['errorMessage'] = $translator->trans("Invalid token, please reload the page.");
        }
        else {
            $target_user = $userRepository->findOneBy(['id' => $request->request->get('id'),  'Squadron' => $squadron_id]);
            if(is_object($target_user)) {
                switch ($action) {
                    case 'pending':
                        $status = $statusRepository->findOneBy(['name' => 'Pending']);
                        $target_user->setStatus($status);
                        $target_user->setStatusComment(null);
                        break;
                    case 'approve':
                        $status = $statusRepository->findOneBy(['name' => 'Approved']);
                        $target_user->setStatus($status);
                        $target_user->setStatusComment(null);
                        break;
                    case 'lock':
                        $status = $statusRepository->findOneBy(['name' => 'Lock Out']);
                        $target_user->setStatus($status);
                        $data['require_reason'] = true;
                        break;
                    case 'ban':
                        $status = $statusRepository->findOneBy(['name' => 'Banned']);
                        $target_user->setStatus($status);
                        $data['require_reason'] = true;
                        break;
                    case 'deny':
                        $status = $statusRepository->findOneBy(['name' => 'Denied']);
                        $target_user->setStatus($status);
                        $data['require_reason'] = true;
                        break;
                }
                $data['status'] = 200;
                $em->flush();
            }
            else {
                $data['errorMessage'] = $translator->trans("User not found.");
            }

        }

        $response = new JsonResponse($data);

        return $response;

    }

    /**
     * @Route("/ajax/members/comment", name="ajax_manage_member_comment", methods={"POST"})
     */
    public function manage_member_comment(Request $request, UserRepository $userRepository, StatusRepository $statusRepository, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $squadron_id = $user->getSquadron()->getId();

        $em = $this->getDoctrine()->getManager();
        $data['status'] = 500;

        $token = $request->request->get('_token');

        if(!$this->isCsrfTokenValid('save_comment', $token)) {
            $data['status'] = 403;
            $data['errorMessage'] = $translator->trans("Invalid token, please reload the page.");
        }
        else {
            $target_user = $userRepository->findOneBy(['id' => $request->request->get('id'),  'Squadron' => $squadron_id]);
            if(is_object($target_user)) {
                $target_user->setStatusComment($request->request->get('comment'));
                $data['status'] = 200;
                $em->flush();
            }
            else {
                $data['errorMessage'] = $translator->trans("User not found.");
            }

        }

        $response = new JsonResponse($data);

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

    /**
     * @Route("/ajax/newapi", name="ajax_newapi", methods={"POST"} )
     */
    public function ajax_newapi(Request $request, UserRepository $repository, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $data['status'] = 500;

        $token = $request->request->get('_token');
        if(!$this->isCsrfTokenValid('newapikey', $token)) {
            $data['status'] = 403;
            $data['errorMessage'] = $translator->trans("Unable to generate a new API Key. Invalid token, please reload the page.");
        }
        else {
            $data['status'] = 200;
            $newapi = md5('edmc:' . $user->getUsername() . time() . random_bytes(5));
            $user->setApikey($newapi);
            $data['newapi'] = $newapi;
            $em->flush();
        }

        $response = new JsonResponse($data);

        return $response;
    }

    /**
     * @Route("/ajax/upload", name="ajax_upload", methods={"POST"})
     */
    public function ajax_upload(Request $request, TranslatorInterface $translator, ImportQueueRepository $repository)
    {
        /**
         * @var UploadedFile $file
         */
        $files = $request->files->get('uploadFile');

        /**
         * @var User $user
         */
        $user = $this->getUser();
        $join_date = new \DateTime(date_format($user->getDateJoined(),'Y-m-d H:i:s'),$this->utc);

        $em = $this->getDoctrine()->getManager();
        $folder_path = $this->getParameter('ajax.fileupload.path');
        $token = $request->request->get('_token');
        $acceptable_files = ['text/plain','application/json'];

        if(!$this->isCsrfTokenValid('ajax_upload', $token)) {
            $data['status'] = 403;
            $data['errorMessage'] = $translator->trans("Invalid token. Uploaded files are not processed.");
        }
        else {
            if(is_writable($folder_path) && is_dir($folder_path)) {
                foreach($files as $i=> $file) {
                    $new_name = md5(uniqid()) . '.' . $file->guessExtension();
                    $size = $file->getSize();
                    $mime = $file->getMimeType();
                    $accept = true;

                    $files[$i] = [
                        'original_name' => $file->getClientOriginalName(),
                        'new_name' => $new_name,
                        'size' => $this->formatBytes($size,1),
                        'type' => $mime,
                        'status' => 'Accepted'
                    ];

                    $queue = $repository->findOneBy(['user' => $user, 'original_filename' => $file->getClientOriginalName()]);

                    if(!is_object($queue)) {
                        $queue = new ImportQueue();
                        $queue->setUser($user);
                        $queue->setOriginalFilename($file->getClientOriginalName());
                        $queue->setUploadFilename($new_name);
                        $queue->setProgressCode('Q');

                        if(array_search($mime, $acceptable_files) === false) {
                            $files[$i]['status'] = 'Rejected. Invalid type.';
                            $accept = false;
                        }
                        else {
                            $peek_file = $file->openFile('r');

                            do {
                                $line = json_decode($peek_file->getCurrentLine(), true);
                                if(json_last_error() !== JSON_ERROR_NONE) {
                                    $found = true;
                                    $accept = false;
                                    $files[$i]['status'] = 'Rejected. Not a Journal File.';
                                }
                                elseif($peek_file->eof()) {
                                    $accept = false;
                                    $found = true;
                                    $files[$i]['status'] = 'Rejected. Not a Journal File.';
                                }
                                elseif($line['event'] == "Fileheader") {
                                    $found = true;
                                    $game_datetime = $line['timestamp'];
                                    $log_date = new \DateTime($game_datetime, $this->utc);
                                    if ($join_date > $log_date) {
                                        $accept = false;
                                        $files[$i]['status'] = 'Rejected. Log date before join date.';
                                    }
                                }
                                $peek_file->next();
                            } while (!$found);
                        }
                    }
                    else {
                        $accept = false;
                        $files[$i]['status'] = 'Rejected. Already imported.';
                    }

                    if($accept) {
                        if(!$file->move($folder_path, $new_name)) {
                            $files[$i]['status'] = 'Upload Failed.';
                        }
                        $queue->setGameDatetime(new \DateTime($game_datetime, $this->utc));
                        $em->persist($queue);
                        $em->flush();
                    }
                }
                $data['status'] = 200;
                $data['responseText'] = $this->renderView('ajax/upload_list.html.twig', [
                    'files' => $files
                    ]
                );
            }
            else {
                $data['status'] = 403;
                $data['errorMessage'] = $translator->trans("Permission Denied. Unable to save to the upload directory. Files are not processed.");
            }

        }

        $response = new JsonResponse($data);
        return $response;
    }

    /**
     * @Route("/ajax/queue/{token}", name="ajax_queue_list", methods={"POST"} )
     */
    public function ajax_queue_list($token, Request $request, ImportQueueRepository $repository, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $this->translator = $translator;
        $status_code = [
            'Q' => 'In Queue',
            'P' => 'Processed',
            'E' => 'Error when Processing',
            'R' => 'Rejected'
        ];

        if(!$this->isCsrfTokenValid('ajax_queue', $token)) {
            $datatable = [
                'error' => $translator->trans('Unauthorized')
            ];
            return new JsonResponse($datatable);
        }

        $params = $request->request->all();
        $params = $this->prepareDTOptions($params);
        $dt = $repository->findAllByUserDatatables($user->getId(), $params);

        // Post process the data to clean up formatting

        $dt = $this->postProcessDTDateData($dt, 'game_datetime', 'game_datetime', false);
        $dt = $this->postProcessDTDateData($dt, 'time_started', 'time_started');

        foreach ($dt['data'] as $i=>$row) {
            $code = $dt['data'][$i]['progress_code'];
            $dt['data'][$i]['progress_code'] = $translator->trans($status_code[$code]);
        }

        $datatable = array_merge($dt, [
            'draw' => $params['draw']
        ]);

        $response = new JsonResponse($datatable);

        return $response;
    }

    /**
     * @Route("/ajax/squadron/info", name="ajax_squadron_info", methods={"POST"} )
     */
    public function ajax_squadron_info(Request $request, SquadronRepository $squadronRepository)
    {
        $json_response = new JsonResponse();
        $token = $request->request->get('_token');

        if(!$this->isCsrfTokenValid('squadron_info', $token)) {
            $json_response->setStatusCode(401);
            $response = [
                'status' => 401,
                'errorMessage' => 'Permission denied. Invaild CSRF Token.'
            ];
            return $json_response->setData($response);
        }

        $squadron_id = $request->request->get('id');
        $squadron = $squadronRepository->findOneBy(['id' => $squadron_id]);
        $content = $this->renderView('ajax/squadron_info.html.twig', [
            'squad' => $squadron
        ]);
        $response = [
          'status' => 200,
          'content' => $content
        ];

        return $json_response->setData($response);
    }

    /**
     * @Route("/ajax/tags", name="ajax_tags", methods={"POST"} )
     */
    public function ajax_tags(Request $request, UserRepository $userRepository, TagsRepository $tagsRepository, SquadronTagsRepository $squadronTagsRepository, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $data['status'] = 500;

        $token = $request->request->get('_token');
        if(!$this->isCsrfTokenValid('squadron_tags', $token)) {
            $data['status'] = 403;
            $data['errorMessage'] = $translator->trans("Expired CSRF token, please reload the page.");
        }
        else {
            $the_tag = $tagsRepository->findOneBy(['id' => $request->request->get('id')]);
            $squadron_tag = $squadronTagsRepository->findOneBy(['tag' => $the_tag, 'squadron' => $user->getSquadron()]);

            if($request->request->get('is_checked') == "true") {
                if(!is_object($squadron_tag)) {
                    $squadron_tag = new SquadronTags();
                }
                $squadron_tag->setSquadron($user->getSquadron())->setTag($the_tag);
                $em->persist($squadron_tag);
                $em->flush();
            }
            else {
                if(is_object($squadron_tag)) {
                    dump($squadron_tag);
                    $em->remove($squadron_tag);
                    $em->flush();
                 }
            }
            $data['status'] = 200;
        }

        $response = new JsonResponse($data);

        return $response;
    }


    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

}

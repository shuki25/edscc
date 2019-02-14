<?php

namespace App\Controller;

use App\Entity\Commander;
use App\Entity\Edmc;
use App\Entity\User;
use App\Repository\CommanderRepository;
use App\Repository\UserRepository;
use App\Service\ParseLogHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{

    private $utc;

    public function __construct()
    {
        $this->utc = new \DateTimeZone('UTC');
    }

    /**
     * @Route("/api/edmc", name="api_edmc", schemes={"https"})
     */
    public function api_edmc(Request $request, ParseLogHelper $parseLogHelper, UserRepository $userRepository, CommanderRepository $commanderRepository)
    {

        $json_response = new JsonResponse();
        $api_key = $request->headers->get('x-api-key');

        /**
         * @var User $user
         */
        $user = $userRepository->findOneBy(['apikey' => $api_key]);

        if($request->getMethod() != "POST") {
            $json_response->setStatusCode(405);
        }
        elseif($request->headers->get('Content-Type') != "application/x-www-form-urlencoded") {
            $json_response->setStatusCode(400);
            $response = [
                'status_code' => 400,
                'message' => 'Bad request. Wrong content type.'
            ];
            $json_response->setData($response);
        }
        elseif(!is_object($user) || $api_key == '') {
            $response = [
                'status_code' => 401,
                'message' => 'Unauthorized request. No API key or it was not registered.'
            ];
            $json_response->setData($response);
            $json_response->setStatusCode(401);
        }
        else {
            try {
                $em = $this->getDoctrine()->getManager();
                $data = $request->request->all();

                if($data['fromSoftware'] != "E:D Market Connector") {
                    $response = [
                        'status_code' => 403,
                        'message' => 'Forbidden. Unauthorized Client.'
                    ];
                    $json_response->setStatusCode(403);
                }
                elseif($data['data_type'] != 'journal') {
                    $response = [
                        'status_code' => 200,
                        'message' => 'Data not processed.'
                    ];
                    $json_response->setStatusCode(200);
                }
                else {
                    $edmc = new Edmc();
                    $edmc->setUser($user);
                    $edmc->setEntry($data['data']);
                    $edmc->setEnteredAt(new \DateTime('now', $this->utc));
                    $edmc->setProcessedFlag(true);
                    $em->persist($edmc);
                    $em->flush();

                    /**
                     * @var Commander $commander
                     */
                    $commander = $user->getCommander();
                    if(is_null($commander)) {
                        $commander = new Commander();
                        $commander->setUser($entry->getUser());
                        $em->persist($commander);
                    }

                    $json_data = json_decode($data['data'], true);

                    foreach ($json_data as $json_datum) {
                        $parseLogHelper->parseEntry($em, $user, $commander, $json_datum, true);
                    }

                    $em->flush();

                    $response = [
                        'status_code' => 200,
                        'user' => $user->getCommanderName(),
                        'debug' => $data
                    ];
                    $json_response->setStatusCode(200);
                }
                $json_response->setData($response);
            }
            catch(\Exception $e) {
                $json_response->setStatusCode(500);
                $response = [
                    'status_code' => 500,
                    'message' => 'Internal Server Error. ' . $e->getMessage() . $e->getTraceAsString()
                ];
                $json_response->setData($response);
            }

        }

        return $json_response;
    }
}

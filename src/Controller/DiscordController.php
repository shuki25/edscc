<?php

namespace App\Controller;

use App\Repository\SquadronRepository;
use App\Repository\UserRepository;
use App\Service\ErrorLogHelper;
use Nyholm\DSN;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DiscordController extends AbstractController
{

    private $valid_verb = ['top50', 'top10', 'stats', 'link'];
    private $utc;
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
        $this->utc = new \DateTimeZone('UTC');
    }

    /**
     * @Route("/discord/auth", name="discord_auth")
     */
    public function discordAuth(Request $request, SquadronRepository $squadronRepository, ErrorLogHelper $errorLogHelper)
    {

        $json_response = new JsonResponse();
        $api_key = $request->headers->get('x-api-key');

        $squadron = $squadronRepository->findOneBy(['discord_bot_api' => $api_key]);

        if ($request->getMethod() != "POST") {
            $json_response->setStatusCode(405);
        } elseif ($request->headers->get('Content-Type') != "application/x-www-form-urlencoded") {
            $json_response->setStatusCode(400);
            $response = [
                'status_code' => 400,
                'message' => 'Bad request. Wrong content type.'
            ];
            $json_response->setData($response);
        } elseif (!is_object($squadron) || $api_key == '') {
            $response = [
                'status_code' => 401,
                'message' => 'Unauthorized request. No API key or it was not registered.'
            ];
            $json_response->setData($response);
            $json_response->setStatusCode(401);
        } else {
            try {
                $data = $request->request->all();
                $json_response->setStatusCode(200);

                if (is_string($data['fromSoftware']) && $data['fromSoftware'] != "EDSCC Discord Bot") {
                    $response = [
                        'status_code' => 403,
                        'message' => 'Forbidden. Unauthorized Client.'
                    ];
                    $json_response->setStatusCode(403);
                } else {
                    $response = [
                        'status_code' => 200,
                        'squadron_name' => $squadron->getName(),
                        'owner_name' => $squadron->getAdmin()->getCommanderName(),
                        'valid_verb' => $this->valid_verb
                    ];
                }
                $json_response->setData($response);
            } catch (\Exception $e) {
                $json_response->setStatusCode(500);
                $response = [
                    'status_code' => 500,
                    'message' => 'Internal Server Error. ' . $e->getMessage() . $e->getTraceAsString()
                ];
                $json_response->setData($response);
                $errorLogHelper->addErrorMsgToErrorLog("EDMC", 0, $e);
            }
        }

        return $json_response;
    }

    /**
     * @Route("/discord/api/{verb}", name="discord_api")
     */
    public function discordApi($verb, Request $request, UserRepository $userRepository, SquadronRepository $squadronRepository, ErrorLogHelper $errorLogHelper)
    {
        $json_response = new JsonResponse();
        $api_key = $request->headers->get('x-api-key');

        $squadron = $squadronRepository->findOneBy(['discord_bot_api' => $api_key]);

        if ($request->getMethod() != "POST") {
            $json_response->setStatusCode(405);
        } elseif ($request->headers->get('Content-Type') != "application/x-www-form-urlencoded") {
            $json_response->setStatusCode(400);
            $response = [
                'status_code' => 400,
                'message' => 'Bad request. Wrong content type.'
            ];
            $json_response->setData($response);
        } elseif (!is_object($squadron) || $api_key == '') {
            $response = [
                'status_code' => 401,
                'message' => 'Unauthorized request. No API key or it was not registered.'
            ];
            $json_response->setData($response);
            $json_response->setStatusCode(401);
        } else {
            try {
                $em = $this->getDoctrine()->getManager();
                $data = $request->request->all();
                $json_response->setStatusCode(200);
                $status_code = 200;
                $error = "";
                $verb = strtolower($verb);

                if ($data['fromSoftware'] != "EDSCC Discord Bot") {
                    $response = [
                        'status_code' => 403,
                        'message' => 'Forbidden. Unauthorized Client.'
                    ];
                    $status_code = 403;
                } else {
                    $msg = sprintf("```\nVerb: %s\nParams: %s```", $verb, json_encode($data));

                    if (!in_array($verb, $this->valid_verb)) {
                        $error = "Invalid API Call";
                        $status_code = 404;
                    } else {
                        switch ($verb) {
                            case 'link':
                                $user = $userRepository->findOneBy(['discord_name' => $data['discord_name']]);
                                if (is_object($user)) {
                                    $user->setDiscordId($data['discord_id']);
                                    $em->flush();
                                    $msg = "Your Discord account is now associated to your EDSCC account.";
                                } else {
                                    $status_code = 200;
                                    $msg = "";
                                    $error = "Unable to associate your Discord account with EDSCC.  Please go to EDSCC website and set your Discord username in your profile settings.";
                                }
                        }
                    }

                    $response = [
                        'status_code' => $status_code,
                        'message' => $msg,
                        'error' => $error
                    ];
                }
                $json_response->setStatusCode($status_code);
                $json_response->setData($response);
            } catch (\Exception $e) {
                $json_response->setStatusCode(500);
                $response = [
                    'status_code' => 500,
                    'message' => 'Internal Server Error. ' . $e->getMessage() . $e->getTraceAsString()
                ];
                $json_response->setData($response);
                $errorLogHelper->addErrorMsgToErrorLog("EDMC", 0, $e);
            }
        }

        return $json_response;
    }

    /**
     * @Route("/discord/api/{verb}/{discord_id}", name="discord_api_with_user")
     */
    public function discordApiWithUser($verb, $discord_id, Request $request, UserRepository $userRepository, SquadronRepository $squadronRepository, ErrorLogHelper $errorLogHelper)
    {
        $json_response = new JsonResponse();
        $api_key = $request->headers->get('x-api-key');

        $squadron = $squadronRepository->findOneBy(['discord_bot_api' => $api_key]);

        if ($request->getMethod() != "POST") {
            $json_response->setStatusCode(405);
        } elseif ($request->headers->get('Content-Type') != "application/x-www-form-urlencoded") {
            $json_response->setStatusCode(400);
            $response = [
                'status_code' => 400,
                'message' => 'Bad request. Wrong content type.'
            ];
            $json_response->setData($response);
        } elseif (!is_object($squadron) || $api_key == '') {
            $response = [
                'status_code' => 401,
                'message' => 'Unauthorized request. No API key or it was not registered.'
            ];
            $json_response->setData($response);
            $json_response->setStatusCode(401);
        } else {
            try {
                $em = $this->getDoctrine()->getManager();
                $data = $request->request->all();
                $json_response->setStatusCode(200);
                $status_code = 200;
                $error = "";
                $verb = strtolower($verb);

                if ($data['fromSoftware'] != "EDSCC Discord Bot") {
                    $response = [
                        'status_code' => 403,
                        'message' => 'Forbidden. Unauthorized Client.'
                    ];
                    $status_code = 403;
                } else {
                    $msg = sprintf("```\n*Verb:* %s *User:* %s\n```", $verb, $user);

                    $user = $userRepository->findOneBy(['discord_id' => $discord_id]);

                    if (!is_object($user)) {
                        $error = "Your Discord account is not linked.  Please use `!link` to link your EDSCC account with Discord.";
                        $status_code = 401;
                    }

                    if (!in_array($verb, $this->valid_verb)) {
                        $error = "Invalid API Call";
                        $status_code = 404;
                    }
                    $response = [
                        'status_code' => $status_code,
                        'message' => $msg,
                        'error' => $error
                    ];
                }
                $json_response->setStatusCode($status_code);
                $json_response->setData($response);
            } catch (\Exception $e) {
                $json_response->setStatusCode(500);
                $response = [
                    'status_code' => 500,
                    'message' => 'Internal Server Error. ' . $e->getMessage() . $e->getTraceAsString()
                ];
                $json_response->setData($response);
                $errorLogHelper->addErrorMsgToErrorLog("EDMC", 0, $e);
            }
        }

        return $json_response;
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

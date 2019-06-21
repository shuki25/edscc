<?php

namespace App\Controller;

use App\Entity\Squadron;
use App\Entity\User;
use App\Repository\SquadronRepository;
use App\Repository\UserRepository;
use App\Service\ErrorLogHelper;
use Nyholm\DSN;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class DiscordController extends AbstractController
{

    private $valid_verb = ['top50', 'top10', 'stats', 'link'];
    private $valid_verb_user = ['stats'];
    private $utc;
    private $bag;
    private $dbh;
    /**
     * @var ErrorLogHelper
     */
    private $errorLogHelper;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ParameterBagInterface $bag, ErrorLogHelper $errorLogHelper, TranslatorInterface $translator)
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
        $this->errorLogHelper = $errorLogHelper;
        $this->translator = $translator;
    }

    /**
     * @Route("/discord/auth", name="discord_auth")
     */
    public function discordAuth(Request $request, SquadronRepository $squadronRepository)
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
                $this->errorLogHelper->addErrorMsgToErrorLog("Discord", 0, $e);
            }
        }

        return $json_response;
    }

    /**
     * @Route("/discord/api/{verb}", name="discord_api")
     */
    public function discordApi($verb, Request $request, UserRepository $userRepository, SquadronRepository $squadronRepository)
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
                $app_code = 200;
                $error = "";
                $table = "";
                $title = "";
                $verb = strtolower($verb);

                if ($data['fromSoftware'] != "EDSCC Discord Bot") {
                    $response = [
                        'status_code' => 403,
                        'message' => 'Forbidden. Unauthorized Client.'
                    ];
                    $status_code = 403;
                } else {
                    $msg = sprintf("```\nVerb: %s\nParams: %s```", $verb, json_encode($data));
                    $msg = '';

                    $user = $userRepository->findOneBy(['discord_id' => $data['discord_id']]);

                    if (!is_object($user) && in_array($verb, $this->valid_verb_user)) {
                        $error = "Your Discord account is not linked.  Please use `!link` to associate your EDSCC account with Discord.";
                        $app_code = 401;
                    } elseif (!in_array($verb, $this->valid_verb)) {
                        $error = "Invalid API Call";
                        $app_code = 404;
                    } else {
                        switch ($verb) {
                            case 'link':
                                $user = $userRepository->findOneBy(['discord_name' => $data['discord_name']]);
                                if (is_object($user)) {
                                    $user->setDiscordId($data['discord_id']);
                                    $em->flush();
                                    $msg = "Your Discord account is now associated to your EDSCC account.";
                                } else {
                                    $app_code = 404;
                                    $msg = "";
                                    $error = "Unable to associate your Discord account with EDSCC.  Please go to EDSCC website and set your Discord username in your profile settings.";
                                }
                                break;
                            case 'stats':
                                $table_data = [
                                    [''],
                                    [$this->translator->trans('Combat'), $this->translator->trans($user->getCommander()->getCombat()->getName())],
                                    [$this->translator->trans('Trade'), $this->translator->trans($user->getCommander()->getTrade()->getName())],
                                    [$this->translator->trans('Exploration'), $this->translator->trans($user->getCommander()->getExplore()->getName())],
                                    [$this->translator->trans('Federation'), $this->translator->trans($user->getCommander()->getFederation()->getName())],
                                    [$this->translator->trans('Empire'), $this->translator->trans($user->getCommander()->getEmpire()->getName())],
                                    [$this->translator->trans('CQC'), $this->translator->trans($user->getCommander()->getCqc()->getName())],
                                    [$this->translator->trans('Total Asset'), $user->getCommander()->getAsset()],
                                    [$this->translator->trans('Credits'), $user->getCommander()->getCredits()],
                                    [$this->translator->trans('Loan'), $user->getCommander()->getLoan()],
                                ];
                                $twig_data = [
                                    'user' => $user,
                                    'table' => $table
                                ];
                                $msg = $this->renderView('discord/stats.html.twig', $twig_data);
                                $title = $this->translator->trans("Commander Statistics");
                                $table = [
                                    'title' => $title,
                                    'data' => $table_data
                                ];
                                break;
                            case 'top10':
                                $table = $this->buildReportTable($verb, $squadron, null, $data);
                                $table['title'] = $this->translator->trans($table['title']);
                                break;
                        }
                    }

                    $response = [
                        'app_code' => $app_code,
                        'message' => $msg,
                        'error' => $error
                    ];

                    if (!empty($table)) {
                        $response['table'] = $table;
                    }
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
                $this->errorLogHelper->addErrorMsgToErrorLog("Discord", 0, $e);
            }
        }

        return $json_response;
    }

    private function buildReportTable($verb, ?Squadron $squadron, ?User $user, $params = null)
    {
        if (empty($params['params'])) {
            $sql = "select * from x_discord_report where verb=:verb";
            $the_params = ['verb' => $verb];
        } else {
            $sql = "select * from x_discord_report where verb=:verb and param1=:param1";
            $the_params = ['verb' => $verb, 'param1' => $params['params'][0]];
        }

        $report = $this->fetchSqlSingle($sql, $the_params);
        $has_data = 0;
        $table = [];

        if (is_array($report)) {
            $report['header'] = json_decode($report['header']);
            $report['columns'] = json_decode($report['columns']);
            $parameters = json_decode($report['parameters']);

            $sql = $report['parameters_sql'];
            $rs = $this->dbh->prepare($sql);
            if (is_object($user)) {
                $rs->execute([$user->getId()]);
            } elseif (is_object($squadron)) {
                $rs->execute([$squadron->getId()]);
            }
            $parameters_data = $rs->fetch(\PDO::FETCH_ASSOC);

            $params = [];
            if (!is_null($parameters)) {
                foreach ($parameters as $i => $item) {
                    $params[] = $parameters_data[$item];
                }
            }

            try {
                $rs = $this->dbh->prepare($report['sql']);
                $rs->execute($params);
                $data = $rs->fetchAll(\PDO::FETCH_ASSOC);
                $has_data = $rs->rowCount();
            } catch (\Exception $e) {
                $this->errorLogHelper->addErrorMsgToErrorLog('Discord', 0, $e, [$sql, $params]);
            }

            if ($has_data) {
                if (isset($data[0]['commander_name'])) {
                    foreach ($data as $i => $datum) {
                        $data[$i]['commander_name'] = $this->translator->trans('CMDR %name%', ['%name%' => $datum['commander_name']]);
                    }
                }
                if (isset($report['trans_columns'])) {
                    $trans_columns = json_decode($report['trans_columns'], true);
                    foreach ($data['data'] as $i => $datum) {
                        foreach ($trans_columns as $column) {
                            $data['data'][$i][$column] = $this->translator->trans($data['data'][$i][$column]);
                        }
                    }
                }
            }

            if (!empty($data)) {
                $table = [];

                foreach ($report['header'] as $item) {
                    $table[0][] = $item;
                }

                $i = 1;
                foreach ($data as $datum) {
                    foreach ($report['columns'] as $j => $column) {
                        $table[$i][$j] = $datum[$column];
                    }
                    $i++;
                }
            }
        }

//        dd($report, $parameters, $params, $table);
        return [
            'title' => $report['title'],
            'data' => $table
        ];
    }

    private function fetchSqlSingle($sql, $params = null)
    {
        $rs = $this->dbh->prepare($sql);
        if (is_array($params)) {
            $rs->execute($params);
        }
        return $rs->fetch(\PDO::FETCH_ASSOC);
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

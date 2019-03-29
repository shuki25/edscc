<?php

namespace App\Controller;

use App\Entity\CapiQueue;
use App\Entity\Oauth2;
use App\Entity\User;
use App\Repository\CapiQueueRepository;
use App\Service\OAuth2Helper;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImportController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    private $utc;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->utc = new \DateTimeZone('UTC');
    }

    /**
     * @Route("/import", name="app_import")
     */
    public function index(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $oauth2 = $user->getOauth2();
        $em = $this->getDoctrine()->getManager();

        if (empty($oauth2)) {
            $oauth2 = new Oauth2();
            $oauth2->setUser($user)
                ->setAccessToken('')
                ->setTokenType('Bearer')
                ->setRefreshToken('')
                ->setConnectionFlag(false)
                ->setExpiresIn(time() - 2);
            $em->persist($oauth2);
            $em->flush();
        }
        $current_time = time();
        $tab = $request->query->get('t') ?: 'upload';

        return $this->render('import/index.html.twig', [
            'title' => 'Importing Player Journal Log',
            'upload_max' => ini_get('upload_max_filesize'),
            'oauth2' => $oauth2,
            'current_time' => $current_time,
            'tab' => $tab,
            'user' => $user
        ]);
    }

    /**
     * @Route("/import/capi/auth", name="app_capi_auth")
     */
    public function capi_auth(Request $request, OAuth2Helper $helper)
    {
        $code = $request->query->get('code');
        $state = $request->query->get('state');

        if ($request->getMethod() == "GET" && !$code) {
            $authorizationURL = $helper->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $helper->getState();
            header('Location:' . $authorizationURL);
            exit;
        } elseif (empty($state) || (isset($_SESSION['oauth2state']) && $state !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
        } else {
            try {
                $accessToken = $helper->getAccessToken('authorization_code', ['code' => $code]);
                $resourceOwner = $helper->getResourceOwner($accessToken);
                $helper->saveAccessTokenToDataStore($this->getUser(), $accessToken, $resourceOwner);
            } catch (IdentityProviderException $e) {
                dd($e->getMessage());
            }
        }

        return $this->redirectToRoute('app_import', ['t' => 'ps4_xbox']);
    }

    /**
     * @Route("/import/capi/prefs", name="app_capi_prefs")
     */
    public function save_preferences(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var Oauth2 $oauth2
         */
        $oauth2 = $this->getUser()->getOauth2();
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('capi_prefs', $token)) {
            $capi_daily = $request->request->get('capi_daily') ?: 0;
            $capi_ttl = $capi_daily ? 1 : $request->request->get('capi_ttl') ?: 0;
            $oauth2->setAutoDownload($capi_daily)
                ->setKeepAlive($capi_ttl);
            $em->flush();
            $this->addFlash('success', $this->translator->trans('Your settings have been saved.'));
        } else {
            $this->addFlash('alert', $this->translator->trans('Expired CSRF Token. Please try again.'));
        }

        return $this->redirectToRoute('app_import', ['t' => 'ps4_xbox']);
    }

    /**
     * @Route("/import/capi/disconnect", name="app_capi_disconnect")
     */
    public function capi_disconnect(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $oauth2 = $user->getOauth2();
        $token = $request->query->get('_token');
        if ($this->isCsrfTokenValid('capi_disconnect', $token)) {
            $oauth2->setConnectionFlag(false)
                ->setAutoDownload(false)
                ->setKeepAlive(false);
            $this->getDoctrine()->getManager()->flush();
        } else {
            $this->addFlash('alert', $this->translator->trans('Expired CSRF Token. Please try again.'));
        }

        return $this->redirectToRoute('app_import', ['t' => 'ps4_xbox']);
    }

    /**
     * @Route("/import/capi/sync", name="app_capi_sync")
     */
    public function capi_sync(Request $request, CapiQueueRepository $capiQueueRepository)
    {
        $token = $request->query->get('_token');
        $em = $this->getDoctrine()->getManager();
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $oauth2 = $user->getOauth2();

        if ($this->isCsrfTokenValid('capi_sync', $token)) {
            for ($i = 24; $i > 0; $i--) {
                $target_date = new \DateTime('now', $this->utc);
                $target_date->setTime(0, 0, 0);
                $interval = sprintf("%d day", $i * -1);
                $target_date->add(\DateInterval::createFromDateString($interval));
                $capi_queue = $capiQueueRepository->findOneBy(['user' => $user, 'journal_date' => $target_date]);
                if (empty($capi_queue)) {
                    $capi_queue = new CapiQueue();
                    $capi_queue->setUser($user)
                        ->setJournalDate($target_date)
                        ->setProgressCode('Q');
                    $em->persist($capi_queue);
                }
            }
            $oauth2->setSyncStatus(true);
            $em->flush();
        } else {
            $this->addFlash('alert', $this->translator->trans('Expired CSRF Token. Please try again.'));
        }
        return $this->redirectToRoute('app_import', ['t' => 'ps4_xbox']);
    }
}

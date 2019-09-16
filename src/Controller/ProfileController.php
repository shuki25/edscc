<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CustomRankRepository;
use App\Repository\RankRepository;
use App\Repository\SquadronRepository;
use App\Repository\StatusRepository;
use Nyholm\DSN;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 *
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class ProfileController extends AbstractController
{

    private $dbh;

    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
        $params = $this->bag->get('pdo_connection_string');

        $dsnObject = new DSN($params);

        $dsn = sprintf('%s:host=%s;dbname=%s', $dsnObject->getProtocol(), $dsnObject->getFirstHost(), $dsnObject->getDatabase());

        try {
            $this->dbh = new \PDO($dsn, $dsnObject->getUsername(), $dsnObject->getPassword(), [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"', \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
        } catch (\Exception $e) {
            dump($e->getMessage());
            dump($dsnObject);
            dd($dsn);
        }
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function profile()
    {
        $user = $this->getUser();

        $hash = md5(strtolower(trim($user->getUsername())));
        $gravatar_url = sprintf("https://www.gravatar.com/avatar/%s?d=mp", $hash);

        if ($user->getGoogleFlag() === "Y") {
            $gravatar_url = $user->getAvatarUrl();
        }

        return $this->render('profile/index.html.twig', [
            'title' => 'Commander Profile',
            'description' => '',
        ]);
    }

    /**
     * @Route("/profile/updatepw", name="app_profile_updatepw", methods={"POST"})
     */
    public function updatePw(Request $request, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        if ($this->isCsrfTokenValid('change_password', $data['_token'])) {
            if ($data['new_password'] != $data['verify_password']) {
                $this->addFlash('alert', $translator->trans('Your new password did not match with verify password. Password is not changed.'));
            } else if ($passwordEncoder->isPasswordValid($user, $data['current_password'])) {
                $user->setPassword($passwordEncoder->encodePassword($user, $data['new_password']));
                $this->addFlash('success', $translator->trans('Your password change has been updated'));
                $em->flush();
            } else {
                $this->addFlash('alert', $translator->trans('The current password is incorrect, and your password is not changed'));
            }
        } else {
            $this->addFlash('alert', $translator->trans('Invalid CSRF Token. Please refresh the page to continue.'));
        }
        return $this->redirectToRoute('app_profile');
    }

    /**
     * @Route("/profile/verify/2fa", name="app_profile_verify_2fa", methods={"POST"})
     */
    public function verify2FA(Request $request, TranslatorInterface $translator, GoogleAuthenticatorInterface $googleAuthenticator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        if ($this->isCsrfTokenValid('verify_2fa', $data['_token'])) {
            $user->setGoogleAuthenticatorSecret($data['secret']);
            if ($googleAuthenticator->checkCode($user, $data['google_2fa'])) {
                $em->flush();
                $this->addFlash('success', $translator->trans('Two-Factor Authentication is activated'));
            } else {
                $this->addFlash('alert', $translator->trans('Invalid 2FA code. 2FA is not activated.'));
            }

        } else {
            $this->addFlash('alert', $translator->trans('Invalid CSRF Token. Please refresh the page to continue.'));
        }

        return $this->redirectToRoute('app_profile');
    }

    /**
     * @Route("/profile/deactivate/2fa", name="app_deactivate_2fa", methods={"POST"})
     */
    public function deactivate2FA(Request $request, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        if ($this->isCsrfTokenValid('deactivate_2fa', $data['_token'])) {
            $user->setGoogleAuthenticatorSecret(null);
            $list = $user->getAccessHistories();
            foreach ($list as $i => $item) {
                $item->setGoogle2faTrustFlag(false);
            }
            $em->flush();
            $this->addFlash('alert', $translator->trans('Two-Factor Authentication has been deactivated'));
        } else {
            $this->addFlash('alert', $translator->trans('Invalid CSRF Token. Please refresh the page to continue.'));
        }

        return $this->redirectToRoute('app_profile');
    }

    /**
     * @Route("/profile/leave", name="app_leave_squadron", methods={"POST"})
     */
    public function leaveSquadron(Request $request, SquadronRepository $squadronRepository, StatusRepository $statusRepository, RankRepository $rankRepository, CustomRankRepository $customRankRepository, TranslatorInterface $translator)
    {

        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('leave_squadron', $token)) {
            /**
             * @var User $user
             */
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();

            if ($user->getId() == $user->getSquadron()->getAdmin()->getId()) {
                $this->addFlash('error', $translator->trans('Squadron Owner cannot leave squadron'));
            } else {
                $squadron = $squadronRepository->findOneBy(['id' => 1]);
                $status = $statusRepository->findOneBy(['id' => 1]);
                $rank = $rankRepository->findOneBy(['id' => 1]);
                $custom_rank = $customRankRepository->findOneBy(['id' => 1]);
                $newRoles = array_intersect($user->getRoles(), ['ROLE_SUPERUSER']);
//                dd($user->getRoles(), $newRoles);
                $user->setSquadron($squadron)
                    ->setRoles($newRoles)
                    ->setStatus($status)
                    ->setRank($rank)
                    ->setCustomRank($custom_rank)
                    ->setWelcomeMessageFlag('N');
                $em->flush();
            }
        }
        return $this->redirectToRoute('app_logout');
    }

    /**
     * @Route("/profile/purgedata", name="app_profile_purge_data", methods={"POST"})
     */
    public function purgeData(Request $request, TranslatorInterface $translator)
    {
        $token = $request->request->get('_token');
        $confirmed = $request->request->get('confirmed');
        $errorMessage = "";

        /**
         * @var User $user
         */
        $user = $this->getUser();
        $uid = $user->getId();
        $sid = $user->getSquadron()->getId();
        $param = [$uid, $sid];

        if ($this->isCsrfTokenValid('purge_data', $token)) {
            if ($confirmed && $uid && $sid) {
                $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                try {
                    $this->dbh->beginTransaction();
                    $rs = $this->dbh->prepare("delete from activity_counter where user_id=? and squadron_id=?");
                    $rs->execute($param);
                    $rs = $this->dbh->prepare("update commander set asset='0',credits='0',loan='0',combat_id='6',trade_id='15',explore_id='24',federation_id='33',empire_id='48',cqc_id='63',combat_progress='0',trade_progress='0',explore_progress='0',federation_progress='0',empire_progress='0',cqc_progress='0' where user_id=?");
                    $rs->execute([$uid]);
                    $rs = $this->dbh->prepare("delete from earning_history where user_id=? and squadron_id=?");
                    $rs->execute($param);
                    $rs = $this->dbh->prepare("delete from faction_activity where user_id=? and squadron_id=?");
                    $rs->execute($param);
                    $rs = $this->dbh->prepare("delete from crime where user_id=? and squadron_id=?");
                    $rs->execute($param);
                    $rs = $this->dbh->prepare("delete from edmc where user_id=?");
                    $rs->execute([$uid]);
                    $rs = $this->dbh->prepare("delete from import_queue where user_id=?");
                    $rs->execute([$uid]);
                    $rs = $this->dbh->prepare("delete from capi_queue where user_id=?");
                    $rs->execute([$uid]);
                    $rs = $this->dbh->prepare("delete from thargoid_activity where user_id=?");
                    $rs->execute([$uid]);
                    $rs = $this->dbh->prepare("delete from achievement where user_id=?");
                    $rs->execute([$uid]);
                    $this->dbh->commit();

                } catch (\PDOException $e) {
                    $this->dbh->rollBack();
                    $errorMessage = $e->getMessage();
                }
                if ($errorMessage) {
                    $this->addFlash('alert', "Purge failed. " . $errorMessage);
                } else {
                    $this->addFlash('success', $translator->trans('Commander data has been purged from the system.'));
                }
            } else {
                $this->addFlash('alert', $translator->trans('Something went wrong. Purge cancelled.'));
            }
        } else {
            $this->addFlash('alert', $translator->trans('Invalid CSRF Token. Please refresh the page to continue.'));
        }

        return $this->redirectToRoute('app_profile');
    }
}

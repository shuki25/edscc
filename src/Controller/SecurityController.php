<?php

namespace App\Controller;

use App\Entity\Commander;
use App\Entity\CustomRank;
use App\Entity\Squadron;
use App\Entity\User;
use App\Entity\VerifyToken;
use App\Form\SquadronType;
use App\Repository\CustomRankRepository;
use App\Repository\LanguageRepository;
use App\Repository\MotdRepository;
use App\Repository\RankRepository;
use App\Repository\SquadronRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Repository\VerifyTokenRepository;
use App\Service\NotificationHelper;
use Faker\Factory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private $utc;
    private $router;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->utc = new \DateTimeZone('utc');
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, MotdRepository $motdRepository, LanguageRepository $languageRepository)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        if ($request->get('email')) {
            $lastUsername = $request->get('email');
        }
        $motd = $motdRepository->findBy(['show_login' => true], ['id' => 'desc']);
        $locale = $languageRepository->findBy(['has_translation' => true], ['name' => 'asc']);

        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
            'title' => 'Login',
            'description' => 'Squadron Member Login',
            'error' => $error,
            'last_username' => $lastUsername,
            'motd' => $motd,
            'locale' => $locale,
            'user_locale' => $request->getLocale()
        ]);
    }

    /**
     * @Route("/settings-api", name="app_get_api")
     */
    public function getApi()
    {
        return $this->render('security/get_api.html.twig', [
            'title' => 'Your API Key',
            'description' => '',
            'error' => ''
        ]);
    }

    /**
     * @Route("/forgot", name="app_forgot_pw")
     */
    public function forgotPw(Request $request, UserRepository $userRepository, NotificationHelper $notificationHelper)
    {
        $token = $request->request->get('_token');
        $faker = Factory::create();

        if ($request->getMethod() == "POST" && $this->isCsrfTokenValid('forgot_password', $token)) {
            $user = $userRepository->findOneBy(['email' => $request->request->get('email')]);
            if (is_object($user)) {
                $em = $this->getDoctrine()->getManager();
                $tmp_pw = $faker->password(8);
                $params = [
                    'tmp_password' => $tmp_pw
                ];
                $user->setTmpPassword(sha1($tmp_pw));
                $em->flush();
                $notificationHelper->userForgotPassword($user, $params);
            }
            $this->addFlash('success', 'An e-mail has been sent with a temporary password.');
            return $this->render('security/reset_pw.html.twig', [
                'title' => 'Reset Password',
                'description' => 'Reset Password',
                'email' => $request->request->get('email'),
                'error' => ''
            ]);
        }

        return $this->render('security/forgot_pw.html.twig', [
            'title' => 'Forgot Password',
            'description' => 'Password Recovery',
            'error' => ''
        ]);
    }

    /**
     * @Route("/reset_pw", name="app_reset_pw", methods={"POST"})
     */
    public function resetPw(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $data = $request->request->all();

        if ($this->isCsrfTokenValid('reset_pw', $data['_token']) && isset($data['email'])) {
            $user = $userRepository->findOneBy(['email' => $data['email']]);
            $em = $this->getDoctrine()->getManager();
            $encoded_tmp_pw = sha1($data['current_password']);

            if ($data['new_password'] == $data['verify_password'] && $user->getTmpPassword() == $encoded_tmp_pw) {
                $user->setTmpPassword(null);
                $user->setPassword($passwordEncoder->encodePassword($user, $data['new_password']));
                $this->addFlash('success', $this->translator->trans('Your password has been reset'));
                $em->flush();

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('alert', $this->translator->trans('Invalid temporary password or mismatched new/verify password pairs.'));
            }
        } else {
            $this->addFlash('alert', $this->translator->trans('Expired CSRF Token. Please try again.'));
        }
        return $this->render('security/reset_pw.html.twig', [
            'title' => 'Reset Password',
            'description' => 'Reset Password',
            'email' => $request->request->get('email'),
            'error' => ''
        ]);
    }

    /**
     * @Route("/join/{slug}", name="app_invite_join")
     */
    public function inviteToJoin($slug, Request $request, SquadronRepository $squadronRepository, UserPasswordEncoderInterface $passwordEncoder, RankRepository $rankRepository, CustomRankRepository $customRankRepository, StatusRepository $statusRepository, UserRepository $userRepository, NotificationHelper $notificationHelper)
    {
        $error = "";

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('security/invalid_invite.html.twig', [
                'title' => $this->translator->trans('Invite to Join'),
                'description' => ''
            ]);
        }

        $squadron = $squadronRepository->findOneBy(['id_code' => $slug]);

        if (!is_object($squadron) || !$squadron->getInviteLink()) {
            return $this->render('security/invalid_invite.html.twig', [
                'title' => $this->translator->trans('Invite to Join'),
                'description' => ''
            ]);
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $token = $data['_token'];

            $pwd1 = trim($data['password1']);
            $pwd2 = trim($data['password2']);

            $existing_commander = $userRepository->findOneBy(['commander_name' => $data['commander_name']]);
            $existing_email = $userRepository->findOneBy(['email' => $data['email']]);

            $check_ok = !is_object($existing_commander) && !is_object($existing_email);

            if (!$this->isCsrfTokenValid('invite_join', $token)) {
                $error = $this->translator->trans("Expired CSRF Token. Please try again.");
            } elseif (!isset($data['_terms'])) {
                $error = $this->translator->trans("You did not agree to the terms. The account was not created.");
            } elseif ($pwd1 == $pwd2 && $pwd1 != "" && $squadron->getId() == $data['squadron_id'] && $check_ok) {
                $user = new User();
                $commander = new Commander();
                $rank = $rankRepository->findOneBy(['id' => 1]);
                $status = $statusRepository->findOneBy(['name' => 'Pending']);
                $custom_rank = $customRankRepository->findOneBy(['order_id' => 0, 'squadron' => $squadron]);

                $user->setCommanderName($data['commander_name'])
                    ->setEmail($data['email'])
                    ->setEmailVerify('N')
                    ->setGoogleFlag('N')
                    ->setGravatarFlag('Y')
                    ->setSquadron($squadron)
                    ->setCommander($commander)
                    ->setRank($rank)
                    ->setCustomRank($custom_rank)
                    ->setStatus($status)
                    ->setApikey(md5('edmc' . $data['email'] . time()))
                    ->setPassword($passwordEncoder->encodePassword($user, $pwd1));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $token = new VerifyToken();
                $token->setUser($user);
                $token->setToken();
                $token->setExpiresAt(new \DateTime("+24 hour"));

                $em->persist($token);
                $em->flush();

                $twig_params = [
                    'token' => $token->getToken(),
                    'user' => $user,
                    'expires_at' => $token->getExpiresAt()
                ];

                $notificationHelper->userEmailVerification($data['email'], $twig_params);

                return $this->redirectToRoute('app_confirm_email', [
                    'email' => $data['email']
                ]);
            } else {
                if (is_object($existing_email)) {
                    $error .= "* " . $this->translator->trans("Sorry, the email address is in use by another user.  Please use a different email address.") . "\n";
                }
                if (is_object($existing_commander)) {
                    $error .= "* " . $this->translator->trans("Sorry, the commander name is in use.  Pick a different commander name.");
                }
            }
        }

        return $this->render('security/join_squadron.html.twig', [
            'title' => $this->translator->trans('register_join_squad', ['_SQUADRON_' => $squadron->getName()]),
            'description' => $this->translator->trans('Registering a New Squadron Member'),
            'squad' => $squadron,
            'error' => $this->translator->trans($error)
        ]);
    }

    /**
     * @Route("/new_member", name="app_new_member")
     */
    public function newMember(Request $request, UserPasswordEncoderInterface $passwordEncoder, CsrfTokenManagerInterface $csrfToken, SquadronRepository $squadronRepository, RankRepository $rankRepository, CustomRankRepository $customRankRepository, StatusRepository $statusRepository, NotificationHelper $notificationHelper, UserRepository $userRepository)
    {

        $error = "";

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
//            $passwordStatus = (new PasswordExposedChecker())->passwordExposed($data['password1']);
            $csrf_token = new CsrfToken('new_acct', $request->request->get('_csrf_token'));

            $pwd1 = trim($data['password1']);
            $pwd2 = trim($data['password2']);

            $existing_commander = $userRepository->findOneBy(['commander_name' => $data['commander_name']]);
            $existing_email = $userRepository->findOneBy(['email' => $data['email']]);

            $check_ok = !is_object($existing_commander) && !is_object($existing_email);

            if (!$csrfToken->isTokenValid($csrf_token)) {
                $error = "Invalid CSRF Token";
            } elseif (!isset($data['_terms'])) {
                $error = "You did not agree to the terms. The account was not created.";
            } //            elseif(($data['password1'] === $data['password2']) && $passwordStatus != PasswordStatus::EXPOSED) {
            elseif ($pwd1 == $pwd2 && $pwd1 != "" && $check_ok) {
                $squadron = $squadronRepository->findOneBy(['id' => 1]);

                $user = new User();
                $commander = new Commander();
                $rank = $rankRepository->findOneBy(['id' => 1]);
                $status = $statusRepository->findOneBy(['name' => 'Pending']);
                $custom_rank = $customRankRepository->findOneBy(['id' => 1]);

                $user->setCommanderName($data['commander_name'])
                    ->setEmail($data['email'])
                    ->setEmailVerify('N')
                    ->setGoogleFlag('N')
                    ->setGravatarFlag('Y')
                    ->setSquadron($squadron)
                    ->setCommander($commander)
                    ->setRank($rank)
                    ->setCustomRank($custom_rank)
                    ->setStatus($status)
                    ->setApikey(md5('edmc' . $data['email'] . time()))
                    ->setPassword($passwordEncoder->encodePassword($user, $pwd1));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $token = new VerifyToken();
                $token->setUser($user);
                $token->setToken();
                $token->setExpiresAt(new \DateTime("+24 hour"));

                $em->persist($token);
                $em->flush();

                $twig_params = [
                    'token' => $token->getToken(),
                    'user' => $user,
                    'expires_at' => $token->getExpiresAt()
                ];

                $notificationHelper->userEmailVerification($data['email'], $twig_params);

                return $this->redirectToRoute('app_confirm_email', [
                    'email' => $data['email']
                ]);
            } else {
                if (is_object($existing_email)) {
                    $error .= "* " . $this->translator->trans("Sorry, the email address is in use by another user.  Please use a different email address.") . "\n";
                }
                if (is_object($existing_commander)) {
                    $error .= "* " . $this->translator->trans("Sorry, the commander name is in use.  Pick a different commander name.");
                }
            }
        }

        return $this->render('security/new_acct.html.twig', [
            'title' => 'Registration',
            'description' => 'Registering a New Squadron Member',
            'error' => $error
        ]);
    }

    /**
     * @Route("/verify_email", name="app_confirm_email")
     */
    public function verifyEmail(Request $request, CsrfTokenManagerInterface $csrfToken, UserRepository $userRepository, VerifyTokenRepository $tokenRepository)
    {

        $error = '';
        $email = $request->query->get('email');

        if ($this->isGranted('ROLE_DENIED')) {
            $email = $this->getUser()->getEmail();
        }

        if ($request->isMethod('POST')) {
            $form = $request->request->all();
        } else {
            $form = $request->query->all();
            $form['_csrf_token'] = '';
        }

        $csrf_token = new CsrfToken('verify_email', $form['_csrf_token']);

        if ($request->isMethod('POST') && !$csrfToken->isTokenValid($csrf_token)) {
            $error = "Invalid CSRF Token";
        } elseif ($request->isMethod('POST') || ($request->isMethod('GET') && isset($form['_token']))) {
            $user = $userRepository->findOneBy(['email' => $form['email']]);

            if ($user->getEmailVerify() === 'N') {
                $users = $userRepository->findValidTokens($user->getId());
                $tokens = $users[0]->getVerifyTokens();

                foreach ($tokens as $token) {
                    if (trim($form['_token']) == $token->getToken()) {
                        $user->setEmailVerify('Y');
                        $tk = $tokenRepository->findOneBy(['User' => $user->getId()]);
                        $user->removeVerifyToken($tk);
                        $em = $this->getDoctrine()->getManager();
                        $em->flush();

                        $this->addFlash('success', 'Your account has been activated.  Please login to continue.');

                        return new RedirectResponse($this->router->generate('app_login'));
                    } else {
                        $error = "The token entered is invalid.";
                    }
                }
            } else {
                $this->addFlash('success', 'Your account is already activated.');
                return new RedirectResponse($this->router->generate('app_login'));
            }
        }

        return $this->render('security/verify_email.html.twig', [
            'title' => 'New Account Creation',
            'description' => 'Verify E-mail account',
            'email' => $email,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/resend_token", name="app_resend_token")
     */
    public function resendToken(Request $request, UserRepository $userRepository, NotificationHelper $notificationHelper)
    {
        $email = $request->query->get('email');
        /*
         * @var User $user
         */
        $user = $userRepository->findOneBy(['email' => $email]);
        $em = $this->getDoctrine()->getManager();

        if (isset($user) && isset($email)) {
            $tokenKey = $user->getNewestVerifyTokens()->getToken();

            if (is_null($tokenKey)) {
                $token = new VerifyToken();
                $token->setUser($user);
                $token->setToken();
                $token->setExpiresAt(new \DateTime("+24 hour"));

                $em->persist($token);
                $em->flush();

                $tokenKey = $token->getToken();
            }

            $twig_params = ['token' => $tokenKey ? $tokenKey : 'Error: No token code was generated.',
                'user' => $user,
                'expires_at' => $user->getNewestVerifyTokens()->getExpiresAt()
            ];

            $notificationHelper->userEmailVerification($email, $twig_params);

            $this->addFlash('success', 'Your activation code has been resent. Check your INBOX.');
        } else {
            $this->addFlash('alert', 'Please enter your email');
        }
        return $this->redirectToRoute('app_confirm_email', [
            'email' => $email
        ]);
    }

    /**
     * @Route("/select_squadron", name="app_select_squadron")
     * @IsGranted("ROLE_PENDING")
     */
    public function selectSquadron(Request $request, SquadronRepository $squadronRepository, StatusRepository $statusRepository, RankRepository $rankRepository, CustomRankRepository $customRankRepository, NotificationHelper $notificationHelper)
    {
        $squadrons = $squadronRepository->findAllActiveSquadrons();
        foreach ($squadrons as $index => $squadron) {
            $platform_tags = [];
            $tags = $squadron->getSquadronTags();
            foreach ($tags as $tag) {
                if ($tag->getTag()->getGroupCode() == "platform") {
                    $platform_tags[] = $tag->getTag()->getName();
                }
            }
            rsort($platform_tags);
            $squadrons_platforms[$index] = $platform_tags;
        }

        if ($request->getMethod() == "POST" && $request->request->get('complete_registration') == "1") {
            $token = $request->request->get('_csrf_token');

            if ($this->isCsrfTokenValid('select_squadron', $token)) {
                /**
                 * @var User $user
                 */
                $user = $this->getUser();
                $em = $this->getDoctrine()->getManager();

                $squadron = $squadronRepository->findOneBy(['id' => $request->request->get('id')]);
                $status_key = $squadron->getRequireApproval() == "Y" ? "Pending" : "Approved";
                $status = $statusRepository->findOneBy(['name' => $status_key]);
                $rank = $rankRepository->findOneBy(['group_code' => 'squadron', 'name' => 'Rookie']);
                $custom_rank = $customRankRepository->findOneBy(['squadron' => $squadron->getId(), 'order_id' => $rank->getAssignedId()]);

                if (is_object($squadron) && is_object($status)) {
                    $user->setWelcomeMessageFlag('N');
                    $user->setSquadron($squadron);
                    $user->setRank($rank);
                    $user->setCustomRank($custom_rank);
                    $user->setStatus($status);
                    $user->setDateJoined(new \DateTime('now', $this->utc));
                    $em->flush();
                    if ($status_key == "Approved") {
                        $providerKey = 'main';
                        $token = new PostAuthenticationGuardToken($user, $providerKey, $user->getRoles());
                        $this->get("security.token_storage")->setToken($token);
                        return $this->redirectToRoute('app_welcome');
                    } else {
                        $notificationHelper->adminApprovalNotice($squadron);
                    }
                    return $this->redirectToRoute('app_pending_access');
                }
            }
        } elseif ($request->getMethod() == "POST" && $request->request->get('create_squadron') == "1") {
            return $this->redirectToRoute('app_create_squadron');
        }

        return $this->render('security/select_squadron.html.twig', [
            'title' => 'Completing your registration',
            'description' => 'Selecting your Squadron',
            'squadrons' => $squadrons,
            'squadrons_platform' => $squadrons_platforms,
            'error' => ''
        ]);
    }

    /**
     * @Route("/create_squadron", name="app_create_squadron")
     * @IsGranted("ROLE_PENDING")
     */
    public function createSquadron(Request $request, StatusRepository $statusRepository, RankRepository $rankRepository, CustomRankRepository $customRankRepository)
    {

        $em = $this->getDoctrine()->getManager();

        /**
         * @var User $user
         */
        $user = $this->getUser();

        /**
         * @var Squadron data
         */
        $data = new Squadron();
        $data->setAdmin($user);

        $form = $this->createForm(SquadronType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var Squadron $squad
             */
            $squad = $form->getData();

            $em->persist($squad);
            $em->flush();

            $ranks = $rankRepository->findBy(['group_code' => 'squadron'], ['assigned_id' => 'asc']);

            foreach ($ranks as $i => $row) {
                $custom_rank = new CustomRank();
                $custom_rank->setOrderId($row->getAssignedId())
                    ->setName($row->getName());
                $em->persist($custom_rank);
                $squad->addCustomRank($custom_rank);
                $rank = $row;
            }
            $em->flush();

            $custom_ranks = $squad->getCustomRanks();
            $custom_rank = $custom_ranks->last();

            $this->addFlash('success', $this->translator->trans('New Squadron Created.'));
            $user->setSquadron($squad);
            $status = $statusRepository->findOneBy(['name' => 'Approved']);
            $user->setStatus($status);
            $user->setRank($rank);
            $user->setCustomRank($custom_rank);
            $user->setWelcomeMessageFlag('N');
            $user->setRoles(['ROLE_ADMIN']);
            $user->setDateJoined(new \DateTime('now', $this->utc));

            $providerKey = 'main';
            $token = new PostAuthenticationGuardToken($user, $providerKey, $user->getRoles());
            $this->get("security.token_storage")->setToken($token);

            $em->flush();

            return $this->redirectToRoute('app_welcome');
        }

        return $this->render('security/create_new_squadron.html.twig', [
            'form_template' => $form->createView(),
            'title' => 'Creating a new Squadron',
            'description' => 'About your Squadron',
            'squad' => $data
        ]);
    }

    /**
     * @Route("/welcome", name="app_welcome")
     */
    public function welcomeMessage(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($request->query->get('read') == "1") {
            $user->setWelcomeMessageFlag('Y');
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        $message = $user->getSquadron()->getWelcomeMessage();

        return $this->render('security/welcome_message.html.twig', [
            'title' => 'Welcome Message',
            'description' => $user->getSquadron()->getName(),
            'message' => $message,
            'error' => ''
        ]);
    }

    /**
     * @Route("/pending_access", name="app_pending_access")
     */
    public function pendingAccess()
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $message = $user->getSquadron()->getWelcomeMessage();

        return $this->render('security/pending_access.html.twig', [
            'title' => 'Privacy Policy',
            'description' => 'Privacy Policy for EDSCC',
            'error' => ''
        ]);
    }

    /**
     * @Route("/privacy_policy", name="app_private_policy")
     */
    public function privacyPolicy()
    {
        return $this->render('privacy_policy.html.twig', [
            'title' => 'Privacy Policy',
            'description' => 'Privacy Policy for EDSCC',
            'error' => ''
        ]);
    }

    /**
     * @Route("/register_google", name="app_new_member_google")
     */
    public function newMemberGoogle()
    {
        return $this->render('security/new_acct.html.twig', [
            'title' => 'Registration',
            'description' => 'Registering a New Squadron Member',
            'error' => ''
        ]);
    }

    /**
     * @Route("/lockscreen", name="app_lockscreen")
     */
    public function lockscreen(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $hash = md5(strtolower(trim($lastUsername)));
        $gravatar_url = sprintf("https://www.gravatar.com/avatar/%s?d=mp", $hash);

        return $this->render('security/lockscreen.html.twig', [
            'title' => 'Lockscreen',
            'description' => 'Enter Password to Login',
            'error' => $error,
            'username' => $lastUsername,
            'commander_name' => 'CMDR Shuki25',
            'gravatar_url' => $gravatar_url
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new Exception('will be intercepted before getting here.');
    }
}

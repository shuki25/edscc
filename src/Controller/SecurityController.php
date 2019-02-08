<?php

namespace App\Controller;

use App\Entity\Commander;
use App\Entity\Squadron;
use App\Entity\User;
use App\Entity\VerifyToken;
use App\Form\SquadronType;
use App\Repository\RankRepository;
use App\Repository\SquadronRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Repository\VerifyTokenRepository;
use DivineOmega\PasswordExposed\PasswordExposedChecker;
use DivineOmega\PasswordExposed\PasswordStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{

    private $router;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
            'title' => 'Login',
            'description' => 'Squadron Member Login',
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    /**
     * @Route("/settings-api", name="app_get_api")
     */
    public function get_api()
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
    public function forgot_pw()
    {
        return $this->render('security/forgot_pw.html.twig', [
           'title' => 'Forgot Password',
            'description' => 'Password Recovery',
            'error' => ''
        ]);
    }

    /**
     * @Route("/new_member", name="app_new_member")
     */
    public function new_member(Request $request, UserPasswordEncoderInterface $passwordEncoder, CsrfTokenManagerInterface $csrfToken, SquadronRepository $squadronRepository, \Swift_Mailer $mailer, RankRepository $rankRepository, StatusRepository $statusRepository)
    {

        $error = "";

        if($request->isMethod('POST')) {
            $data = $request->request->all();
//            $passwordStatus = (new PasswordExposedChecker())->passwordExposed($data['password1']);
            $csrf_token = new CsrfToken('new_acct', $request->request->get('_csrf_token'));

            if(!$csrfToken->isTokenValid($csrf_token)) {
                $error = "Invalid CSRF Token";
            }
            elseif(!isset($data['_terms'])) {
                $error = "You did not agree to the terms. The account was not created.";
            }
//            elseif(($data['password1'] === $data['password2']) && $passwordStatus != PasswordStatus::EXPOSED) {
            elseif($data['password1'] === $data['password2']) {
                $squadron = $squadronRepository->findOneBy(['id'=> 1]);

                $user = new User();
                $commander = new Commander();
                $rank = $rankRepository->findOneBy(['id' => 1]);
                $status = $statusRepository->findOneBy(['name' => 'Pending']);

                $user->setCommanderName($data['commander_name'])
                    ->setEmail($data['email'])
                    ->setEmailVerify('N')
                    ->setGoogleFlag('N')
                    ->setGravatarFlag('Y')
                    ->setSquadron($squadron)
                    ->setCommander($commander)
                    ->setRank($rank)
                    ->setStatus($status)
                    ->setApikey(md5('edmc' . $data['email'] . time()))
                    ->setPassword($passwordEncoder->encodePassword($user, $data['password1']));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $token = new VerifyToken();
                $token->setUser($user);
                $token->setToken();
                $token->setExpiresAt(new \DateTime("+24 hour"));

                $em->persist($token);
                $em->flush();

                $message = (new \Swift_Message('Activation Code for ED:SCC'))
                    ->setFrom('edscc.donotreply@gmail.com')
                    ->setTo($data['email'])
                    ->setBody(
                        $this->renderView('emails/registration_verification_min.html.twig',
                            array('token' => $token->getToken(),
                                'user' => $user,
                                'expires_at' => $token->getExpiresAt()
                            )
                        ), 'text/html'
                    );

                $mailer->send($message);

                return $this->redirectToRoute('app_confirm_email', [
                    'email' => $data['email']
                ]);
            } else {
                $error = $passwordStatus == PasswordStatus::EXPOSED ? "The password you chose has been exposed in a data breach.  Please visit haveibeenpwned.com for further information.  Please choose a different password." : "The passwords did not match.  Please re-type them carefully.";
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
    public function verify_email (Request $request, CsrfTokenManagerInterface $csrfToken, UserRepository $userRepository, VerifyTokenRepository $tokenRepository) {

        $error = '';
        $email = $request->query->get('email');

        if($this->isGranted('ROLE_DENIED')) {
            $email = $this->getUser()->getEmail();
        }

        if($request->isMethod('POST')) {
            $form = $request->request->all();
        } else {
            $form = $request->query->all();
            $form['_csrf_token'] = '';
        }

        $csrf_token = new CsrfToken('verify_email', $form['_csrf_token']);

        if($request->isMethod('POST') && !$csrfToken->isTokenValid($csrf_token)) {
            $error = "Invalid CSRF Token";
        }

        elseif($request->isMethod('POST') || ($request->isMethod('GET') && isset($form['_token']))) {
            $user = $userRepository->findOneBy(['email'=>$form['email']]);

            if($user->getEmailVerify() === 'N') {
                $users = $userRepository->findValidTokens($user->getId());
                $tokens = $users[0]->getVerifyTokens();

                foreach($tokens as $token) {
                    if(trim($form['_token']) == $token->getToken()) {
                        $user->setEmailVerify('Y');
                        $tk = $tokenRepository->findOneBy(['User' => $user->getId()]);
                        $user->removeVerifyToken($tk);
                        $em = $this->getDoctrine()->getManager();
                        $em->flush();

                        $this->addFlash('success', 'Your account has been activated.  Please login to continue.');

                        return new RedirectResponse($this->router->generate('app_login'));
                    }
                    else {
                        $error = "The token entered is invalid.";
                    }
                }
            }
            else {
                $this->addFlash('success','Your account is already activated.');
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
    public function resend_token(Request $request, UserRepository $userRepository, \Swift_Mailer $mailer)
    {
        $email = $request->query->get('email');
        /*
         * @var User $user
         */
        $user = $userRepository->findOneBy(['email' => $email]);
        $em = $this->getDoctrine()->getManager();

        if (isset($user) && isset($email)) {
            $tokenKey = $user->getNewestVerifyTokens()->getToken();

            if(is_null($tokenKey)) {
                $token = new VerifyToken();
                $token->setUser($user);
                $token->setToken();
                $token->setExpiresAt(new \DateTime("+24 hour"));

                $em->persist($token);
                $em->flush();

                $tokenKey = $token->getToken();
            }

            $message = (new \Swift_Message('Activation Code for ED:SCC'))
                ->setFrom('edscc.donotreply@gmail.com')
                ->setTo($email)
                ->setBody(
                    $this->renderView('emails/registration_verification_min.html.twig',
                        array('token' => $tokenKey ? $tokenKey : 'Error: No token code was generated.',
                            'user' => $user,
                            'expires_at' => $user->getNewestVerifyTokens()->getExpiresAt()
                        )
                    ), 'text/html'
                );

            $mailer->send($message);

            $this->addFlash('success','Your activation code has been resent. Check your INBOX.');
        }
        else {
            $this->addFlash('alert','Please enter your email');
        }
        return $this->redirectToRoute('app_confirm_email', [
            'email' => $email
        ]);
    }

    /**
     * @Route("/select_squadron", name="app_select_squadron")
     * @IsGranted("ROLE_PENDING")
     */
    public function select_squadron(Request $request, SquadronRepository $squadronRepository, StatusRepository $statusRepository)
    {
        $squadrons = $squadronRepository->findAllActiveSquadrons();

        if($request->getMethod() == "POST" && $request->request->get('complete_registration') == "1") {
            $token = $request->request->get('_csrf_token');

            if($this->isCsrfTokenValid('select_squadron',$token)) {
                /**
                 * @var User $user
                 */
                $user = $this->getUser();
                $em = $this->getDoctrine()->getManager();

                $squadron = $squadronRepository->findOneBy(['id' => $request->request->get('id')]);
                $status_key = $squadron->getRequireApproval() == "Y" ? "Pending" : "Approved";
                $status = $statusRepository->findOneBy(['name' => $status_key]);

                if(is_object($squadron) && is_object($status)) {
                    $user->setWelcomeMessageFlag('N');
                    $user->setSquadron($squadron);
                    $user->setStatus($status);
                    $em->flush();
                    if($status_key == "Approved") {
                        $providerKey = 'main';
                        $token = new PostAuthenticationGuardToken($user, $providerKey, $user->getRoles());
                        $this->get("security.token_storage")->setToken($token);
                        return $this->redirectToRoute('app_welcome');
                    }
                    return $this->redirectToRoute('app_pending_access');
                }
            }
        }
        elseif($request->getMethod() == "POST" && $request->request->get('create_squadron') == "1") {
            return $this->redirectToRoute('app_create_squadron');
        }

        return $this->render('security/select_squadron.html.twig',[
            'title' => 'Completing your registration',
            'description' => 'Selecting your Squadron',
            'squadrons' => $squadrons,
            'error' => ''
        ]);
    }

    /**
     * @Route("/create_squadron", name="app_create_squadron")
     * @IsGranted("ROLE_PENDING")
     */
    public function create_squadron(Request $request, StatusRepository $statusRepository)
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

        if($form->isSubmitted() && $form->isValid()) {

            /**
             * @var Squadron $squad
             */
            $squad = $form->getData();

            $em->persist($squad);
            $em->flush();

            $this->addFlash('success',$this->translator->trans('New Squadron Created.'));
            $user->setSquadron($squad);
            $status = $statusRepository->findOneBy(['name' => 'Approved']);
            $user->setStatus($status);
            $user->setWelcomeMessageFlag('N');
            $user->setRoles(['ROLE_ADMIN']);

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
    public function welcome_message(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if($request->query->get('read') == "1") {
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
    public function pending_access()
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
    public function privacy_policy()
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
    public function new_member_google()
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
        $gravatar_url = sprintf("https://www.gravatar.com/avatar/%s?d=mp",$hash);

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

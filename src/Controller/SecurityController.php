<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\VerifyToken;
use App\Repository\SquadronRepository;
use App\Repository\UserRepository;
use App\Repository\VerifyTokenRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController
{

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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
    public function new_member(Request $request, UserPasswordEncoderInterface $passwordEncoder, CsrfTokenManagerInterface $csrfToken, SquadronRepository $squadronRepository, \Swift_Mailer $mailer)
    {

        $error = "";

        if($request->isMethod('POST')) {
            $data = $request->request->all();

            $csrf_token = new CsrfToken('new_acct', $request->request->get('_csrf_token'));

            if(!$csrfToken->isTokenValid($csrf_token)) {
                $error = "Invalid CSRF Token";
            }
            elseif(!isset($data['_terms'])) {
                $error = "You did not agree to the terms. The account was not created.";
            }
            elseif($data['password1'] === $data['password2']) {

                $squadron = $squadronRepository->findOneBy(['id'=> 1]);

                $user = new User();
                $user->setCommanderName($data['commander_name'])
                    ->setEmail($data['email'])
                    ->setEmailVerify('N')
                    ->setGoogleFlag('N')
                    ->setGravatarFlag('Y')
                    ->setSquadron($squadron)
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
                $error = "The passwords did not match.  Please re-type them carefully.";
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
                    if($form['_token'] === $token->getToken()) {
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
            'email' => $request->query->get('email'),
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

        if (isset($user) && isset($email)) {
            $tokenKey = $user->getNewestVerifyTokens()->getToken();

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

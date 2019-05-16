<?php

namespace App\Security;

use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\AccessHistoryHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $userRepository;
    private $router;
    private $csrfTokenManager;
    private $userPasswordEncoder;
    private $manager;
    /**
     * @var StatusRepository
     */
    private $statusRepository;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var AccessHistoryHelper
     */
    private $accessHistoryHelper;

    public function __construct(UserRepository $userRepository, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $userPasswordEncoder, EntityManagerInterface $manager, StatusRepository $statusRepository, TranslatorInterface $translator, AccessHistoryHelper $accessHistoryHelper)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->manager = $manager;
        $this->statusRepository = $statusRepository;
        $this->translator = $translator;
        $this->accessHistoryHelper = $accessHistoryHelper;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'app_login' && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token')
        ];

        $request->getSession()->set(Security::LAST_USERNAME, $credentials['email']);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $csrf_token = new CsrfToken('authenticate', $credentials['csrf_token']);

        if (!$this->csrfTokenManager->isTokenValid($csrf_token)) {
            throw new InvalidCsrfTokenException();
        }
        return $this->userRepository->findOneBy(['email' => $credentials['email']]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->userPasswordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $this->userRepository->findOneBy(['email' => $request->request->get('email')]);
        $user->setLastLoginAt();
        $remote_addr_label = getenv('APP_REMOTE_ADDR');
        $remote_ip = getenv($remote_addr_label);
        if (!$this->accessHistoryHelper->hasLoggedInBefore($user, $remote_ip)) {
            $access_history = $this->accessHistoryHelper->addAccessHistory($user, $remote_ip);
            $this->accessHistoryHelper->notifyUser($user, $access_history);
        } else {
            $this->accessHistoryHelper->updateAccessHistoryTimestamp($user, $remote_ip);
        }

        $this->manager->flush();
        $targetPath = "";
        $status = $user->getStatus()->getName();
        $message = $this->translator->trans('Access Denied: Account status is %status%', ['%status%' => $status]);

        switch ($status) {
            case 'Lock Out':
            case 'Banned':
            case 'Denied':
                throw new AccessDeniedException($message);
                break;
        }

        if ($user->getSquadron()->getId() == 1) {
            $targetPath = $this->router->generate('app_select_squadron', ['_locale' => $request->getLocale()]);
        } elseif ($user->getWelcomeMessageFlag() == "N" && $user->getStatus()->getName() == "Approved") {
            $targetPath = $this->router->generate('app_welcome', ['_locale' => $request->getLocale()]);
        }
        return new RedirectResponse($targetPath ?: $this->router->generate('dashboard', ['_locale' => $request->getLocale()]));
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function supportsRememberMe()
    {
        return true;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }

}

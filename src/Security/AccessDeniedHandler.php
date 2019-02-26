<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-01-31
 * Time: 10:47
 */

namespace App\Security;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var Security $security
     */
    private $security;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(LoggerInterface $logger, Security $security, RouterInterface $router, \Twig_Environment $twig)
    {
        $this->logger = $logger;
        $this->security = $security;
        $this->router = $router;
        $this->twig = $twig;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        if ($user->getEmailVerify() == "N") {
            $url = $this->router->generate('app_confirm_email', ['email' => $user->getEmail()]);
            return new RedirectResponse($url);
        }

        switch ($user->getStatus()->getName()) {
            case 'Pending':
                if ($user->getSquadron()->getId() == "1") {
                    $url = $this->router->generate('app_select_squadron');
                } else {
                    $url = $this->router->generate('app_pending_access');
                }
                return new RedirectResponse($url);
            case 'Banned':
                $content = $this->twig->render('security/banned.html.twig', [
                    'title' => "Account is Banned",
                    'description' => ""
                ]);
                break;
            case 'Lock Out':
                $content = $this->twig->render('security/lock_out.html.twig', [
                    'title' => "Account is Locked Out",
                    'description' => ""
                ]);
                break;
            case 'Denied':
                $content = $this->twig->render('security/application_denied.html.twig', [
                    'title' => "Account is Locked Out",
                    'description' => ""
                ]);
                break;
            default:
                $content = $this->twig->render('security/access_denied.html.twig', [
                    'title' => "Access Denied",
                    'description' => "Not Authorized",
                    'url' => $request->getRequestUri(),
                    'code' => $accessDeniedException->getCode(),
                    'message' => $accessDeniedException->getMessage()
                ]);
                break;
        }

        return new Response($content, 403);
    }

}
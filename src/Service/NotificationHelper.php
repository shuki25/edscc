<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-02-22
 * Time: 15:35
 */

namespace App\Service;


use App\Entity\Squadron;
use App\Entity\User;
use App\Repository\SquadronRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationHelper
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var SquadronRepository
     */
    private $squadronRepository;
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $from;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(UserRepository $userRepository, SquadronRepository $squadronRepository, \Swift_Mailer $mailer, \Twig_Environment $twig, TranslatorInterface $translator, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->squadronRepository = $squadronRepository;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->from = getenv('MAILER_FROM');
        $this->em = $em;
    }

    public function admin_approval_notice(Squadron $squadron)
    {
        $leader = $this->userRepository->findOneBy(['id' => $squadron->getAdmin()->getId()]);
        $body = $this->twig->render('emails/approval_notification_admin.html.twig', [
            'leader' => $leader
        ]);

        $message = (new \Swift_Message($this->translator->trans('ED:SCC Approval Requested')))
            ->setFrom($this->from)
            ->setTo($leader->getEmail())
            ->setBody($body, 'text/html');
        $this->mailer->send($message);
    }

    public function user_email_verification($email, $twig_params)
    {
        $body = $this->twig->render('emails/registration_verification.html.twig', $twig_params);
        $message = (new \Swift_Message($this->translator->trans('Activation Code for ED:SCC')))
            ->setFrom($this->from)
            ->setTo($email)
            ->setBody($body, 'text/html');
        $this->mailer->send($message);
    }

    public function user_status_change(User $user)
    {
        $twig_params = [
            'approved' => $user->getStatus()->getName() == "Approved" ? "Y" : "N",
            'user' => $user
        ];
        $body = $this->twig->render('emails/approval_notification_user.html.twig', $twig_params);
        $message = (new \Swift_Message($this->translator->trans('Squadron Join Request Update')))
            ->setFrom($this->from)
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');
        $this->mailer->send($message);
    }
}
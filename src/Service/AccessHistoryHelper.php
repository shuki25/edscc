<?php

namespace App\Service;


use App\Entity\AccessHistory;
use App\Entity\User;
use App\Repository\AccessHistoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use WhichBrowser\Parser;

class AccessHistoryHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var AccessHistoryRepository
     */
    private $accessHistoryRepository;
    /**
     * @var IP2LocationHelper
     */
    private $IP2LocationHelper;
    /**
     * @var NotificationHelper
     */
    private $notificationHelper;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, AccessHistoryRepository $accessHistoryRepository, IP2LocationHelper $IP2LocationHelper, NotificationHelper $notificationHelper)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->accessHistoryRepository = $accessHistoryRepository;
        $this->IP2LocationHelper = $IP2LocationHelper;
        $this->notificationHelper = $notificationHelper;
    }

    public function addAccessHistory(User $user, $ip_addr): ?AccessHistory
    {
        $browser = new Parser($_SERVER['HTTP_USER_AGENT']);

        $access_history = new AccessHistory();
        $access_history->setUser($user)
            ->setRemoteIp($ip_addr)
            ->setBrowser($browser->browser->toString())
            ->setPlatform($browser->os->toString())
            ->setDevice($browser->device->toString());

        $this->IP2LocationHelper->updateAccessHistory($access_history, $ip_addr);
        $this->entityManager->persist($access_history);
        $this->entityManager->flush();

        return $access_history;
    }

    public function hasLoggedInBefore(User $user, $ip_addr)
    {
        $browser = new Parser($_SERVER['HTTP_USER_AGENT']);

        $access_history = $this->accessHistoryRepository->findOneBy([
            'user' => $user->getId(),
            'remote_ip' => $ip_addr,
            'browser' => $browser->browser->toString(),
            'platform' => $browser->os->toString(),
            'device' => $browser->device->toString()
        ]);

        return is_object($access_history);
    }

    public function updateAccessHistoryTimestamp(User $user, $ip_addr)
    {
        $browser = new Parser($_SERVER['HTTP_USER_AGENT']);

        $access_history = $this->accessHistoryRepository->findOneBy([
            'user' => $user->getId(),
            'remote_ip' => $ip_addr,
            'browser' => $browser->browser->toString(),
            'platform' => $browser->os->toString(),
            'device' => $browser->device->toString()
        ]);

        $access_history->setUpdatedAt(new \DateTime('now'));
        $this->entityManager->flush();
    }

    public function update2FATrust(User $user, $ip_addr, $flag = false)
    {
        $browser = new Parser($_SERVER['HTTP_USER_AGENT']);

        $access_history = $this->accessHistoryRepository->findOneBy([
            'user' => $user->getId(),
            'remote_ip' => $ip_addr,
            'browser' => $browser->browser->toString(),
            'platform' => $browser->os->toString(),
            'device' => $browser->device->toString()
        ]);

        $access_history->setGoogle2faTrustFlag($flag);
        $this->entityManager->flush();
    }

    public function check2FATrust(User $user, $ip_addr): bool
    {
        $browser = new Parser($_SERVER['HTTP_USER_AGENT']);

        $access_history = $this->accessHistoryRepository->findOneBy([
            'user' => $user->getId(),
            'remote_ip' => $ip_addr,
            'browser' => $browser->browser->toString(),
            'platform' => $browser->os->toString(),
            'device' => $browser->device->toString()
        ]);

        return (is_object($access_history)) ? $access_history->getGoogle2faTrustFlag() : false;
    }

    public function notifyUser(User $user, AccessHistory $accessHistory)
    {
        $this->notificationHelper->userNewLoginLocation($user, $accessHistory);
    }
}
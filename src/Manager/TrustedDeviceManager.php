<?php

namespace App\Manager;


use App\Service\AccessHistoryHelper;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManagerInterface;

class TrustedDeviceManager implements TrustedDeviceManagerInterface
{
    /**
     * @var AccessHistoryHelper
     */
    private $accessHistoryHelper;
    private $remote_ip;

    public function __construct(AccessHistoryHelper $accessHistoryHelper)
    {
        $remote_addr_label = getenv('APP_REMOTE_ADDR');
        $this->remote_ip = getenv($remote_addr_label);
        $this->accessHistoryHelper = $accessHistoryHelper;
    }

    public function addTrustedDevice($user, string $firewallName): void
    {
        $this->accessHistoryHelper->update2FATrust($user, $this->remote_ip, true);
    }

    public function isTrustedDevice($user, string $firewallName): bool
    {
        return $this->accessHistoryHelper->check2FATrust($user, $this->remote_ip);
    }

}
<?php

namespace App\Command;

use App\Repository\Oauth2Repository;
use App\Service\ErrorLogHelper;
use App\Service\OAuth2Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CapiRefreshTokenCommand extends Command
{
    protected static $defaultName = 'app:capi-refresh-token';
    /**
     * @var OAuth2Helper
     */
    private $OAuth2Helper;
    /**
     * @var Oauth2Repository
     */
    private $oauth2Repository;
    /**
     * @var ErrorLogHelper
     */
    private $errorLogHelper;

    public function __construct(OAuth2Helper $OAuth2Helper, Oauth2Repository $oauth2Repository, ErrorLogHelper $errorLogHelper)
    {
        parent::__construct();
        $this->OAuth2Helper = $OAuth2Helper;
        $this->oauth2Repository = $oauth2Repository;
        $this->errorLogHelper = $errorLogHelper;
    }

    protected function configure()
    {
        $this
            ->setDescription('Refresh CAPI access tokens');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = 0;

        $io = new SymfonyStyle($input, $output);

        $expiring_soon = time() + 1000;
        $list = $this->oauth2Repository->findBy(['keep_alive' => true, 'connection_flag' => true]);

        $progressBar = new ProgressBar($output, count($list));
        $progressBar->start();
        $progressBar->setFormat('very_verbose');

        foreach ($list as $item) {
            if ($item->getExpiresIn() < $expiring_soon) {
                $oauth2 = $item->getUser()->getOauth2();
                $newAccessToken = $this->OAuth2Helper->getAccessToken('refresh_token', [
                    'refresh_token' => $oauth2->getRefreshToken()
                ]);
                $resourceOwner = $this->OAuth2Helper->getResourceOwner($newAccessToken);
                $this->OAuth2Helper->saveAccessTokenToDataStore($item->getUser(), $newAccessToken, $resourceOwner);

                $count++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $io->success($count . ' CAPI Access Tokens Refreshed');
    }
}

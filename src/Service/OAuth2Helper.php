<?php
/**
 * Copyright (c) 2019. Joshua Butler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-03-27
 * Time: 23:36
 */

namespace App\Service;


use App\Entity\Oauth2;
use App\Entity\User;
use App\Repository\Oauth2Repository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;

class OAuth2Helper extends GenericProvider
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Oauth2Repository
     */
    private $oauth2Repository;

    public function __construct(EntityManagerInterface $entityManager, Oauth2Repository $oauth2Repository)
    {
        parent::__construct([
            'scope' => 'auth capi',
            'clientId' => getenv('CAPI_CLIENT_ID'),
            'clientSecret' => getenv('CAPI_CLIENT_SECRET'),
            'redirectUri' => getenv('CAPI_CALLBACK_URL'),
            'urlAuthorize' => getenv('CAPI_AUTH_API') . '/auth',
            'urlAccessToken' => getenv('CAPI_AUTH_API') . '/token',
            'urlResourceOwnerDetails' => getenv('CAPI_AUTH_API') . '/decode',
//            'proxy' => '192.168.2.85:8888',
//            'verify' => false
        ]);

        $this->entityManager = $entityManager;
        $this->oauth2Repository = $oauth2Repository;
    }

    public function saveAccessTokenToDataStore(User $user, AccessToken $accessToken, GenericResourceOwner $resourceOwner)
    {
        $oauth2 = $this->oauth2Repository->findOneBy(['user' => $user->getId()]);

        if (empty($oauth2)) {
            $oauth2 = new Oauth2();
            $oauth2->setUser($user);
            $this->entityManager->persist($oauth2);
        }

        $oauth2->setAccessToken($accessToken->getToken())
            ->setTokenType('Bearer')
            ->setRefreshToken($accessToken->getRefreshToken())
            ->setExpiresIn($accessToken->getExpires())
            ->setConnectionFlag(true);

        $this->entityManager->flush();
    }
}

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
 * Date: 2019-04-09
 * Time: 18:12
 */

namespace App\Service;


use App\Entity\AccessHistory;
use App\Repository\AccessHistoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class IP2LocationHelper
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
     * @var ErrorLogHelper
     */
    private $errorLogHelper;
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, AccessHistoryRepository $accessHistoryRepository, ErrorLogHelper $errorLogHelper, ParameterBagInterface $bag)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->accessHistoryRepository = $accessHistoryRepository;
        $this->errorLogHelper = $errorLogHelper;
        $this->bag = $bag;
    }

    public function getIP2Location($ip_addr)
    {
        $database_path = getenv('IP2LOCATION_PATH');
        try {
            $db = new \IP2Location\Database($database_path, \IP2Location\Database::FILE_IO);
        } catch (\Exception $e) {
            $this->errorLogHelper->addErrorMsgToErrorLog('IP2Location', '0', $e);
            return [];
        }

        $records = [];

        try {
            $records = $db->lookup($ip_addr, \IP2Location\Database::ALL);
        } catch (RequestException $e) {
            $this->errorLogHelper->addErrorMsgToErrorLog('IP2Location', '0', $e);
        }

        return $records;
    }

    public function updateAccessHistory(AccessHistory &$accessHistory, $ip_addr)
    {
        $data = $this->getIP2Location($ip_addr);

        if (is_object($data)) {
            $accessHistory->setCountryCode($data['countryCode'])
                ->setCountryName($data['countryName'])
                ->setCityName($data['cityName'])
                ->setRegionName($data['regionName'])
                ->setLatitude($data['latitude'])
                ->setLongitude($data['longitude'])
                ->setZipCode($data['zipCode']);
        } else {
            $accessHistory->setCountryCode('-')
                ->setCountryName('-')
                ->setCityName('-')
                ->setRegionName('-')
                ->setLatitude('0')
                ->setLongitude('0')
                ->setZipCode('-');
        }

    }
}
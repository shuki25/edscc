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

namespace App\Service;

use App\Entity\ErrorLog;
use Doctrine\ORM\EntityManagerInterface;

class ErrorLogHelper
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addErrorMsgToErrorLog($scope, $eid, \Exception $e, $debug = null, $data_trace = null)
    {
        $error_log = new ErrorLog();
        $error_id = sprintf("%s-%d: %d", $scope, $eid, $e->getCode());
        $error_msg = sprintf("%s(%d): %s", $e->getFile(), $e->getLine(), $e->getMessage());
        $debug = isset($debug) ? json_encode($debug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        $error_log->setScope($scope)
            ->setErrorId($error_id)
            ->setErrorMsg($error_msg)
            ->setStackTrace($e->getTraceAsString())
            ->setDebugInfo($debug)
            ->setDataTrace($data_trace);

        $this->entityManager->persist($error_log);
        $this->entityManager->flush();
    }

    public function addSimpleMsgToErrorLog($scope, $rid, $eid, $msg = null, $debug = null)
    {
        $error_log = new ErrorLog();
        $error_id = sprintf("%s-%d: %d", $scope, $rid, $eid);
        $error_msg = $msg;
        $debug = isset($debug) ? json_encode($debug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        $error_log->setScope($scope)
            ->setErrorId($error_id)
            ->setErrorMsg($error_msg)
            ->setStackTrace(null)
            ->setDebugInfo($debug)
            ->setDataTrace(null);

        $this->entityManager->persist($error_log);
        $this->entityManager->flush();
    }
}

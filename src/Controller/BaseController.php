<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-01-01
 * Time: 02:43
 */

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    protected function getUser(): User
    {
        return parent::getUser();
    }
}
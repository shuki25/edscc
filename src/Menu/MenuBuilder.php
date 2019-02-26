<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-02-01
 * Time: 10:23
 */

namespace App\Menu;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class MenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var Yaml
     */
    private $yaml;
    private $menu;

    public function __construct()
    {
        $this->yaml = new Yaml();
        try {
            $this->menu = $this->yaml->parseFile('../config/menu.yaml');
        } catch (ParseException $e) {
            echo $e->getMessage();
        }
    }

    public function userMenu(array $options = [])
    {
        $data = $this->menu['user'];
        return $data;
    }

    public function adminMenu(array $options = [])
    {
        $data = $this->menu['admin'];
        return $data;
    }
}
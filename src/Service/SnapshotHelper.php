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


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Nyholm\DSN;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SnapshotHelper
{

    private $utc;
    private $dbh;
    
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $bag, TranslatorInterface $translator)
    {

        $this->entityManager = $entityManager;
        $this->bag = $bag;
        $this->translator = $translator;

        $this->bag = $bag;
        $params = $this->bag->get('pdo_connection_string');

        $this->utc = new \DateTimeZone('utc');
        $dsnObject = new DSN($params);

        $dsn = sprintf('%s:host=%s;dbname=%s', $dsnObject->getProtocol(), $dsnObject->getFirstHost(), $dsnObject->getDatabase());

        try {
            $this->dbh = new \PDO($dsn, $dsnObject->getUsername(), $dsnObject->getPassword(), [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"', \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'', \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        } catch (\Exception $e) {
            dump($e->getMessage());
            dump($dsnObject);
            dd($dsn);
        }
    }

    public function getData(User $user)
    {
        $sql = "select * from x_variable order by id";
        $rs = $this->dbh->prepare($sql);
        $rs->execute([]);
        $commander_dictionary = $rs->fetchAll(\PDO::FETCH_ASSOC);
        $data = [];

        foreach ($commander_dictionary as $i => $entry) {

            $long = $entry['long_format'];

            if ($entry['columns'] != "*") {
                $columns = json_decode($entry['columns']);
                $all = false;
            } else {
                $all = true;
            }

            $parameters = json_decode($entry['parameters']);

            $sql = $entry['sql'];
            $rs = $this->dbh->prepare($sql);

            if ($entry['name'] == 'default') {
                $rs->execute([$user->getId()]);
                $tmp = $rs->fetch(\PDO::FETCH_ASSOC);
            } else {
                $params = [];

                if (!is_null($parameters[0])) {
                    foreach ($parameters as $j => $item) {
                        $params[] = $data[$item];
                    }
                }

                $rs->execute($params);

                if ($long) {
                    $tmp = $rs->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    $tmp = $rs->fetch(\PDO::FETCH_ASSOC);
                }
            }

            foreach ($tmp as $j => $value) {
                if ($long) {
                    $column_name = $entry['long_prefix'] . strtolower($value[$entry['long_column']]);
                    if ($all) {
                        $data[$column_name] = $value['long_value'];
                    } else {
                        if (in_array($value[$entry['long_column']], $columns)) {
                            $data[$column_name] = $value['long_value'];
                        }
                    }
                } else {
                    if ($all) {
                        $data[$j] = $value;
                    } else {
                        if (in_array($j, $columns)) {
                            $data[$j] = $value;
                        }
                    }
                }
            }
        }
        return $data;
    }
}
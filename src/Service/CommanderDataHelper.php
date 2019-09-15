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


use App\Entity\Achievement;
use App\Entity\User;
use App\Repository\AchievementConditionRepository;
use App\Repository\AchievementRepository;
use App\Repository\AchievementRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nyholm\DSN;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommanderDataHelper
{

    private $dbh;
    private $utc;

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
    /**
     * @var AchievementRepository
     */
    private $achievementRepository;
    /**
     * @var AchievementRuleRepository
     */
    private $achievementRuleRepository;
    /**
     * @var AchievementConditionRepository
     */
    private $achievementConditionRepository;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $bag, TranslatorInterface $translator, AchievementRepository $achievementRepository, AchievementRuleRepository $achievementRuleRepository, AchievementConditionRepository $achievementConditionRepository)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;

        $this->bag = $bag;
        $params = $this->bag->get('pdo_connection_string');

        $this->utc = new \DateTimeZone('utc');
        $dsnObject = new DSN($params);

        $this->achievementRepository = $achievementRepository;
        $this->achievementRuleRepository = $achievementRuleRepository;
        $this->achievementConditionRepository = $achievementConditionRepository;

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

    public function checkForNewAchievements(User $user, $data = false)
    {

        if (!$data) {
            $data = $this->getData($user);
        }

//        $achievements = $this->achievementRuleRepository->findAll();
        $achievements = $this->achievementRuleRepository->findLockedAchievements($user->getId());
        dump($achievements);

        foreach ($achievements as $achievement) {
            $result = false;
            $conditions = $this->achievementConditionRepository->findBy(['achievement_rule' => $achievement->getId()]);
            dump($conditions);

            foreach ($conditions as $i => $condition) {
                switch ($condition->getDataType()) {
                    case 'I':
                        $eval_string = sprintf("return (%d %s %d) ? true : false;", $data[$condition->getVariable()], $condition->getOperator(), $condition->getConditionValue());
                }
                dump($eval_string);
                if ($i) {
                    $result = $result & eval($eval_string);
                } else {
                    $result = eval($eval_string);
                }
                dump($result);
            }
            if ($result) {
                $new_achievement = New Achievement();
                $new_achievement->setAchievementRule($achievement)
                    ->setUser($user)
                    ->setDateUnlocked(new \DateTime('now', $this->utc))
                    ->setViewFlag(false);
                $this->entityManager->persist($new_achievement);
                $this->entityManager->flush();
            }
        }
    }
}
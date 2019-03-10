<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-01-29
 * Time: 15:46
 */

namespace App\Service;

use App\Entity\ActivityCounter;
use App\Entity\Commander;
use App\Entity\EarningHistory;
use App\Entity\EarningType;
use App\Entity\FactionActivity;
use App\Entity\User;
use App\Repository\ActivityCounterRepository;
use App\Repository\CommanderRepository;
use App\Repository\EarningHistoryRepository;
use App\Repository\EarningTypeRepository;
use App\Repository\ImportQueueRepository;
use App\Repository\MinorFactionRepository;
use App\Repository\RankRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ParseLogHelper
{
    private $utc;

    /**
     * @var ImportQueueRepository
     */
    private $importQueueRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var CommanderRepository
     */
    private $commanderRepository;
    /**
     * @var RankRepository
     */
    private $rankRepository;
    /**
     * @var EarningTypeRepository
     */
    private $earningTypeRepository;
    /**
     * @var EarningHistoryRepository
     */
    private $earningHistoryRepository;
    /**
     * @var ActivityCounterRepository
     */
    private $activityCounterRepository;

    private $group_code = ['Combat', 'Trade', 'Explore', 'Federation', 'Empire', 'CQC'];

    /**
     * @var EarningType $earning_type_obj
     */
    private $earning_type_obj;

    private $earning_type;

    /**
     * @var ActivityCounter $activity_counter
     */
    private $activity_counter;
    /**
     * @var MinorFactionRepository
     */
    private $minorFactionRepository;

    public function __construct(ImportQueueRepository $importQueueRepository, UserRepository $userRepository, CommanderRepository $commanderRepository, RankRepository $rankRepository, EarningTypeRepository $earningTypeRepository, EarningHistoryRepository $earningHistoryRepository, ActivityCounterRepository $activityCounterRepository, MinorFactionRepository $minorFactionRepository)
    {
        $this->importQueueRepository = $importQueueRepository;
        $this->userRepository = $userRepository;
        $this->commanderRepository = $commanderRepository;
        $this->rankRepository = $rankRepository;
        $this->earningTypeRepository = $earningTypeRepository;
        $this->earningHistoryRepository = $earningHistoryRepository;
        $this->activityCounterRepository = $activityCounterRepository;
        $this->utc = new \DateTimeZone('UTC');

        $this->earning_type_obj = $this->earningTypeRepository->findAll();
        foreach ($this->earning_type_obj as $i => $row) {
            $type = strtolower($row->getName());
            $this->earning_type[$type] = $row;
        }

        $this->minorFactionRepository = $minorFactionRepository;
    }

    public function parseEntry(EntityManagerInterface &$em, User &$user, Commander &$commander, $data, $api = false)
    {
        if ($api) {
            $e = $data;
            $game_datetime = isset($e['timestamp']) ? $e['timestamp'] : date_format(new \DateTime('now', $this->utc), \DateTime::RFC3339);
            $this->activity_counter = $this->activityCounterRepository->findOneBy(['user' => $user, 'squadron' => $user->getSquadron(), 'activity_date' => new \DateTime($game_datetime, $this->utc)]);
            if (!is_object($this->activity_counter)) {
                $this->activity_counter = new ActivityCounter();
                $this->activity_counter->setUser($user)
                    ->setSquadron($user->getSquadron())
                    ->setActivityDate(new \DateTime($game_datetime, $this->utc));
            }
            $em->persist($this->activity_counter);
        } else {
            $e = json_decode($data, true);
            $game_datetime = isset($e['timestamp']) ? $e['timestamp'] : date_format(new \DateTime('now', $this->utc), \DateTime::RFC3339);
        }

        switch ($e['event']) {
            case 'Fileheader':
                $this->activity_counter = $this->activityCounterRepository->findOneBy(['user' => $user, 'squadron' => $user->getSquadron(), 'activity_date' => new \DateTime($game_datetime, $this->utc)]);
                if (!is_object($this->activity_counter)) {
                    $this->activity_counter = new ActivityCounter();
                    $this->activity_counter->setUser($user)
                        ->setSquadron($user->getSquadron())
                        ->setActivityDate(new \DateTime($game_datetime, $this->utc));
                }
                $em->persist($this->activity_counter);
                break;

            case 'LoadGame':
                $commander->setCredits($e['Credits']);
                $commander->setLoan($e['Loan']);
                break;

            case 'Commander':
                if (isset($e['FID'])) {
                    $commander->setPlayerId($e['FID']);
                }
                break;

            case 'Rank':
                foreach ($this->group_code as $key) {
                    $rank = $this->rankRepository->findOneBy(['group_code' => strtolower($key), 'assigned_id' => $e[$key]]);
                    $commander->setRankId($key, $rank);
                }
                break;

            case 'Progress':
                foreach ($this->group_code as $key) {
                    $commander->setRankProgress($key, $e[$key]);
                }
                break;

            case 'Statistics':
                $bank_acct = $e['Bank_Account'];
                $commander->setAsset($bank_acct['Current_Wealth']);
                break;

            case 'Bounty':
                $reward = isset($e['TotalReward']) ? $e['TotalReward'] : $e['Reward'];
                $target_faction = isset($e['VictimFaction']) ? $e['VictimFaction'] : "";
                $this->activity_counter->addBountiesClaimed(1);
                if (isset($e['Rewards'])) {
                    foreach ($e['Rewards'] as $i => $row) {
                        $minor_faction = isset($row['Faction']) ? $row['Faction'] : "";
                        $this->addMinorFactionActivity($em, $user, $e['event'], $game_datetime, $row['Reward'], $minor_faction, $target_faction);
                    }
                } else {
                    $minor_faction = isset($e['Faction']) ? $e['Faction'] : "";
                    $this->addMinorFactionActivity($em, $user, $e['event'], $game_datetime, $reward, $minor_faction, $target_faction);
                }
                break;

            case 'CapShipBond':
            case 'FactionKillBond':
                $minor_faction = isset($e['AwardingFaction']) ? $e['AwardingFaction'] : "";
                $target_faction = isset($e['VictimFaction']) ? $e['VictimFaction'] : "";
                $this->activity_counter->addBountiesClaimed(1);
                $this->addMinorFactionActivity($em, $user, $e['event'], $game_datetime, $e['Reward'], $minor_faction, $target_faction);
                break;

            case 'RedeemVoucher':
                $type = $e['Type'];

                if (isset($e['Factions'])) {
                    foreach ($e['Factions'] as $i => $row) {
                        $minor_faction = isset($row['Faction']) ? $row['Faction'] : "";
                        $this->addEarningHistory($em, $user, $type, $game_datetime, $row['Amount'], $minor_faction);
                    }
                } elseif (isset($e['Faction'])) {
                    $this->addEarningHistory($em, $user, $type, $game_datetime, $e['Amount'], $e['Faction']);
                }
                break;

            case 'MultiSellExplorationData':
                $num_systems = count($e['Discovered']);
                $num_bodies = 0;
                foreach ($e['Discovered'] as $system) {
                    $num_bodies += $system['NumBodies'];
                }
                $crew_wage = $e['BaseValue'] + $e['Bonus'] - $e['TotalEarnings'];
                $this->addEarningHistory($em, $user, 'ExplorationData', $game_datetime, $e['TotalEarnings'], null, $crew_wage);
                $this->activity_counter->addBodiesFound($num_bodies)
                    ->addSystemsScanned($num_systems);
                break;

            case 'SellExplorationData':
                $num_systems = count($e['Systems']);
                $num_bodies = count($e['Discovered']);

                if (isset($e['TotalEarnings'])) {
                    $crew_wage = $e['BaseValue'] + $e['Bonus'] - $e['TotalEarnings'];
                    $this->addEarningHistory($em, $user, 'ExplorationData', $game_datetime, $e['TotalEarnings'], null, $crew_wage);
                } else {
                    $this->addEarningHistory($em, $user, 'ExplorationData', $game_datetime, $e['BaseValue'] + $e['Bonus']);
                }
                $this->activity_counter->addBodiesFound($num_bodies)
                    ->addSystemsScanned($num_systems);
                break;

            case 'SAAScanComplete':
                $efficiency = ($e['ProbesUsed'] <= $e['EfficiencyTarget']);
                $this->activity_counter->addSaaScanCompleted(1)
                    ->addEfficiencyAchieved($efficiency);
                break;

            case 'MarketBuy':
                $this->addEarningHistory($em, $user, $e['event'], $game_datetime, $e['TotalCost'] * -1);
                $this->activity_counter->addMarketBuy($e['Count']);
                break;

            case 'MarketSell':
                $this->addEarningHistory($em, $user, $e['event'], $game_datetime, $e['TotalSale']);
                $this->activity_counter->addMarketSell($e['Count']);
                if (isset($e['StolenGoods'])) {
                    $this->activity_counter->addStolenGoods($e['Count']);
                }
                break;

            case 'MiningRefined':
                $this->activity_counter->addMiningRefined(1);
                break;

            case 'CommunityGoalReward':
                $this->addEarningHistory($em, $user, $e['event'], $game_datetime, $e['Reward']);
                $this->activity_counter->addCgParticipated(1);
                break;

            case 'MissionCompleted':
                $name = isset($e['Name']) ? $e['Name'] : '';
                $pieces = explode('_', $name);
                $name = strtolower(sprintf('%s_%s', ucfirst(strtolower($pieces[0])), $pieces[1]));
                $type = isset($this->earning_type[$name]) ? $name : $e['event'];
                $note = '';
                if ($type == $e['event']) {
                    $note = $name;
                }
                if (isset($e['Reward'])) {
                    $minor_faction = isset($e['Faction']) ? $e['Faction'] : "";
                    $target_faction = isset($e['TargetFaction']) ? $e['TargetFaction'] : "";
                    $this->addEarningHistory($em, $user, $type, $game_datetime, $e['Reward'], $minor_faction, 0, $note);
                    $this->addMinorFactionActivity($em, $user, $type, $game_datetime, $e['Reward'], $minor_faction, $target_faction);
                }
                $this->activity_counter->addMissionsCompleted(1);
                break;

            case 'CommitCrime':
                $this->activity_counter->addCrimesCommitted(1);
                break;

        }
    }

    private function addEarningHistory(EntityManagerInterface &$em, User &$user, $type, $date, $reward, $minor_faction = null, $crew_wage = 0, $notes = '')
    {
        $eh = new EarningHistory();
        $type = strtolower($type);

        $minor_faction_obj = null;
        if (!is_null($minor_faction)) {
            $minor_faction_obj = $this->minorFactionRepository->findOneBy(['name' => $minor_faction]);
        }

        $eh->setUser($user)
            ->setEarningType($this->earning_type[$type])
            ->setSquadron($user->getSquadron())
            ->setEarnedOn(new \DateTime($date, $this->utc))
            ->setReward($reward)
            ->setCrewWage($crew_wage)
            ->setMinorFaction($minor_faction_obj);
        if ($notes) {
            $eh->setNotes($notes);
        }
        $em->persist($eh);
    }

    private function addMinorFactionActivity(EntityManagerInterface &$em, User &$user, $type, $date, $reward, $minor_faction, $target_faction)
    {
        $mfa = new FactionActivity();
        $minor_faction_obj = $this->minorFactionRepository->findOneBy(['name' => $minor_faction]);
        $target_faction_obj = $this->minorFactionRepository->findOneBy(['name' => $target_faction]);
        $type = strtolower($type);

        $mfa->setUser($user)
            ->setEarningType($this->earning_type[$type])
            ->setSquadron($user->getSquadron())
            ->setEarnedOn(new \DateTime($date, $this->utc))
            ->setReward($reward)
            ->setMinorFaction($minor_faction_obj)
            ->setTargetMinorFaction($target_faction_obj);

        $em->persist($mfa);
    }

}
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
use App\Entity\Crime;
use App\Entity\CrimeType;
use App\Entity\EarningHistory;
use App\Entity\EarningType;
use App\Entity\FactionActivity;
use App\Entity\MinorFaction;
use App\Entity\SessionTracker;
use App\Entity\User;
use App\Repository\ActivityCounterRepository;
use App\Repository\CommanderRepository;
use App\Repository\CrimeTypeRepository;
use App\Repository\EarningHistoryRepository;
use App\Repository\EarningTypeRepository;
use App\Repository\ImportQueueRepository;
use App\Repository\MinorFactionRepository;
use App\Repository\RankRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
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

    private $groupCode = ['Combat', 'Trade', 'Explore', 'Federation', 'Empire', 'CQC'];

    /**
     * @var EarningType $earningTypeObj
     */
    private $earningTypeObj;
    private $earningType;

    /**
     * @var CrimeType
     */
    private $crimeTypeObj;
    private $crimeType;
    /**
     * @var ActivityCounter $activityCounter
     */
    private $activityCounter;
    /**
     * @var MinorFactionRepository
     */
    private $minorFactionRepository;
    /**
     * @var CrimeTypeRepository
     */
    private $crimeTypeRepository;

    public function __construct(ImportQueueRepository $importQueueRepository, UserRepository $userRepository, CommanderRepository $commanderRepository, RankRepository $rankRepository, EarningTypeRepository $earningTypeRepository, EarningHistoryRepository $earningHistoryRepository, ActivityCounterRepository $activityCounterRepository, MinorFactionRepository $minorFactionRepository, CrimeTypeRepository $crimeTypeRepository)
    {
        $this->importQueueRepository = $importQueueRepository;
        $this->userRepository = $userRepository;
        $this->commanderRepository = $commanderRepository;
        $this->rankRepository = $rankRepository;
        $this->earningTypeRepository = $earningTypeRepository;
        $this->earningHistoryRepository = $earningHistoryRepository;
        $this->activityCounterRepository = $activityCounterRepository;
        $this->utc = new \DateTimeZone('UTC');

        $this->earningTypeObj = $this->earningTypeRepository->findAll();
        foreach ($this->earningTypeObj as $i => $row) {
            $type = strtolower($row->getName());
            $this->earningType[$type] = $row;
        }

        $this->minorFactionRepository = $minorFactionRepository;
        $this->crimeTypeRepository = $crimeTypeRepository;

        $this->crimeTypeObj = $this->crimeTypeRepository->findAll();
        foreach ($this->crimeTypeObj as $i => $row) {
            $crime_type = strtolower($row->getName());
            $alias = json_decode($row->getAlias(), true);
            $this->crimeType[$crime_type] = $row;
            if (is_array($alias)) {
                foreach ($alias as $key) {
                    $key = strtolower($key);
                    $this->crimeType[$key] = $row;
                }
            }
        }
    }

    public function parseEntry(EntityManagerInterface &$em, User &$user, Commander &$commander, $data, SessionTracker $session_tracker, $api = false)
    {
        if ($api) {
            $e = $data;
            $session = $session_tracker->getSessionData();
            $game_datetime = isset($e['timestamp']) ? $e['timestamp'] : date_format(new \DateTime('now', $this->utc), \DateTime::RFC3339);
            $this->activityCounter = $this->activityCounterRepository->findOneBy(['user' => $user, 'squadron' => $user->getSquadron(), 'activity_date' => new \DateTime($game_datetime, $this->utc)]);
            if (!is_object($this->activityCounter)) {
                $this->activityCounter = new ActivityCounter();
                $this->activityCounter->setUser($user)
                    ->setSquadron($user->getSquadron())
                    ->setActivityDate(new \DateTime($game_datetime, $this->utc));
            }
            $em->persist($this->activityCounter);
        } else {
            $e = json_decode($data, true);
            $session = $session_tracker->getSessionData();
            $game_datetime = isset($e['timestamp']) ? $e['timestamp'] : date_format(new \DateTime('now', $this->utc), \DateTime::RFC3339);
        }

        switch ($e['event']) {
            case 'Fileheader':
                $this->activityCounter = $this->activityCounterRepository->findOneBy(['user' => $user, 'squadron' => $user->getSquadron(), 'activity_date' => new \DateTime($game_datetime, $this->utc)]);
                if (!is_object($this->activityCounter)) {
                    $this->activityCounter = new ActivityCounter();
                    $this->activityCounter->setUser($user)
                        ->setSquadron($user->getSquadron())
                        ->setActivityDate(new \DateTime($game_datetime, $this->utc));
                }
                $em->persist($this->activityCounter);
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
                foreach ($this->groupCode as $key) {
                    $rank = $this->rankRepository->findOneBy(['group_code' => strtolower($key), 'assigned_id' => $e[$key]]);
                    $commander->setRankId($key, $rank);
                }
                break;

            case 'Progress':
                foreach ($this->groupCode as $key) {
                    $commander->setRankProgress($key, $e[$key]);
                }
                break;

            case 'Statistics':
                $bank_acct = $e['Bank_Account'];
                $commander->setAsset($bank_acct['Current_Wealth']);
                break;

            case 'Docked':
                $session = $e;
                $session_tracker->setSessionData($session);
                $em->flush();
                break;

            case 'Undocked':
                $session = $e;
                $session_tracker->setSessionData($session);
                $em->flush();
                break;

            case 'Bounty':
                $reward = isset($e['TotalReward']) ? $e['TotalReward'] : $e['Reward'];
                $target_faction = isset($e['VictimFaction']) ? $e['VictimFaction'] : "";
                $this->activityCounter->addBountiesClaimed(1);
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
                $this->activityCounter->addBountiesClaimed(1);
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

                $minor_faction = isset($session['StationFaction']['Name']) ? $session['StationFaction']['Name'] : (isset($session['StationFaction']) ? $session['StationFaction'] : null);
                $station_name = isset($session['StationName']) ? $session['StationName'] : null;

                $crew_wage = $e['BaseValue'] + $e['Bonus'] - $e['TotalEarnings'];
                $this->addEarningHistory($em, $user, 'ExplorationData', $game_datetime, $e['TotalEarnings'], $minor_faction, $crew_wage, $station_name);
                $this->activityCounter->addBodiesFound($num_bodies)
                    ->addSystemsScanned($num_systems);
                break;

            case 'SellExplorationData':
                $num_systems = count($e['Systems']);
                $num_bodies = count($e['Discovered']);

                $minor_faction = isset($session['StationFaction']['Name']) ? $session['StationFaction']['Name'] : (isset($session['StationFaction']) ? $session['StationFaction'] : null);
                $station_name = isset($session['StationName']) ? $session['StationName'] : null;

                if (isset($e['TotalEarnings'])) {
                    $crew_wage = $e['BaseValue'] + $e['Bonus'] - $e['TotalEarnings'];
                    $this->addEarningHistory($em, $user, 'ExplorationData', $game_datetime, $e['TotalEarnings'], $minor_faction, $crew_wage, $station_name);
                } else {
                    $this->addEarningHistory($em, $user, 'ExplorationData', $game_datetime, $e['BaseValue'] + $e['Bonus'], $minor_faction, null, $station_name);
                }
                $this->activityCounter->addBodiesFound($num_bodies)
                    ->addSystemsScanned($num_systems);
                break;

            case 'SAAScanComplete':
                $efficiency = ($e['ProbesUsed'] <= $e['EfficiencyTarget']);
                $this->activityCounter->addSaaScanCompleted(1)
                    ->addEfficiencyAchieved($efficiency);
                break;

            case 'MarketBuy':
                $this->addEarningHistory($em, $user, $e['event'], $game_datetime, $e['TotalCost'] * -1);
                $this->activityCounter->addMarketBuy($e['Count']);
                break;

            case 'MarketSell':
                $this->addEarningHistory($em, $user, $e['event'], $game_datetime, $e['TotalSale']);
                $this->activityCounter->addMarketSell($e['Count']);
                if (isset($e['StolenGoods'])) {
                    $this->activityCounter->addStolenGoods($e['Count']);
                }
                break;

            case 'MiningRefined':
                $this->activityCounter->addMiningRefined(1);
                break;

            case 'CommunityGoalReward':
                $this->addEarningHistory($em, $user, $e['event'], $game_datetime, $e['Reward']);
                $this->activityCounter->addCgParticipated(1);
                break;

            case 'MissionCompleted':
                $name = isset($e['Name']) ? $e['Name'] : '';
                $pieces = explode('_', $name);
                $name = sprintf('%s_%s', ucfirst(strtolower($pieces[0])), $pieces[1]);
                $name_ci = strtolower($name);
                $type = isset($this->earningType[$name_ci]) ? $name_ci : $e['event'];
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
                $this->activityCounter->addMissionsCompleted(1);
                break;

            case 'CommitCrime':
                $this->activityCounter->addCrimesCommitted(1);
                $crime_committed = isset($e['CrimeType']) ? $e['CrimeType'] : "";
                $minor_faction = isset($e['Faction']) ? $e['Faction'] : null;
                if (isset($e['Victim_Localised'])) {
                    $victim = $e['Victim_Localised'];
                } else {
                    $victim = isset($e['Victim']) ? $e['Victim'] : null;
                }
                $fine = isset($e['Fine']) ? $e['Fine'] : null;
                $bounty = isset($e['Bounty']) ? $e['Bounty'] : null;
                $this->addCrimeHistory($em, $user, $crime_committed, $minor_faction, $victim, $fine, $bounty, $game_datetime);
                break;

        }
    }

    public function getSpecificSession(EntityManagerInterface &$em, User $user, bool $api): ?SessionTracker
    {
        $session = $user->getSessionTrackers();

        foreach ($session as $item) {
            if ($item->getApiFlag() && $api) {
                return $item;
            } elseif (!$item->getApiFlag() && !$api) {
                return $item;
            }
        }
        $item = new SessionTracker();
        $item->setUser($user)
            ->setSessionData([])
            ->setApiFlag($api);

        $em->persist($item);
        $em->flush();
        return $item;
    }

    private function addEarningHistory(EntityManagerInterface &$em, User &$user, $type, $date, $reward, $minorFaction = null, $crewWage = 0, $notes = '')
    {
        $eh = new EarningHistory();
        $type = strtolower($type);

        $minorFactionObj = null;
        if (!is_null($minorFaction)) {
            $minorFactionObj = $this->findMinorFaction($em, $minorFaction);
        }

        $eh->setUser($user)
            ->setEarningType($this->earningType[$type])
            ->setSquadron($user->getSquadron())
            ->setEarnedOn(new \DateTime($date, $this->utc))
            ->setReward($reward)
            ->setCrewWage($crewWage)
            ->setMinorFaction($minorFactionObj);
        if ($notes) {
            $eh->setNotes($notes);
        }
        $em->persist($eh);
    }

    private function addMinorFactionActivity(EntityManagerInterface &$em, User &$user, $type, $date, $reward, $minorFaction, $targetFaction)
    {
        $mfa = new FactionActivity();
        $minorFactionObj = $this->findMinorFaction($em, $minorFaction);
        $targetFactionObj = $this->findMinorFaction($em, $targetFaction);
        $type = strtolower($type);

        $mfa->setUser($user)
            ->setEarningType($this->earningType[$type])
            ->setSquadron($user->getSquadron())
            ->setEarnedOn(new \DateTime($date, $this->utc))
            ->setReward($reward)
            ->setMinorFaction($minorFactionObj)
            ->setTargetMinorFaction($targetFactionObj);

        $em->persist($mfa);
    }

    private function addCrimeHistory(EntityManagerInterface &$em, User &$user, $crimeCommitted, $minorFaction, $victim, $fine, $bounty, $date)
    {
        $crime = new Crime();
        $minorFactionObj = $this->findMinorFaction($em, $minorFaction);
        $crimeCommittedCi = strtolower($crimeCommitted);
        $notes = null;

        if (!isset($this->crimeType[$crimeCommittedCi])) {
            $notes = $crimeCommitted;
            $crimeCommittedCi = "other";
        }

        $crime->setUser($user)
            ->setSquadron($user->getSquadron())
            ->setCrimeType($this->crimeType[$crimeCommittedCi])
            ->setMinorFaction($minorFactionObj)
            ->setVictim($victim)
            ->setFine($fine)
            ->setBounty($bounty)
            ->setCommittedOn(new \DateTime($date, $this->utc))
            ->setNotes($notes);

        $em->persist($crime);
    }

    private function findMinorFaction(EntityManagerInterface &$em, $minorFaction): ?MinorFaction
    {

        if (trim($minorFaction) == '') {
            return null;
        }

        $minorFactionObj = $this->minorFactionRepository->findOneBy(['name' => $minorFaction]);
        if (!is_object($minorFactionObj)) {
            $minorFactionObj = new MinorFaction();
            $minorFactionObj->setName($minorFaction)
                ->setPlayerFaction(0)
                ->setEddbId(null);
            $em->persist($minorFactionObj);
            $em->flush();
        }

        return $minorFactionObj;
    }

}
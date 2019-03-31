SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE x_player_report;
CREATE TABLE IF NOT EXISTS x_player_report
(
  id             int(11) unsigned auto_increment primary key,
  title          varchar(255)  null,
  header         varchar(512)  null,
  columns        varchar(512)  null,
  `sql`          varchar(1024) null,
  count_sql      varchar(512)  null,
  parameters     varchar(512)  null,
  parameters_sql varchar(512)  null,
  order_id       tinyint(11)   null,
  order_dir      varchar(4)    null,
  cast_columns   varchar(512)  null,
  sort_columns   varchar(512)  null,
  trans_columns  varchar(512)  null,
  filter_rules   varchar(512)  null
);
INSERT INTO x_player_report (id, title, `sql`, parameters, parameters_sql, filter_rules)
VALUES (1, 'Earning History (Detailed)',
        'select u.commander_name, eh.user_id, eh.squadron_id, eh.earned_on, if(eh.earning_type_id=''8'' and eh.notes is not null,concat(et.name, '' ('', eh.notes, '')'') ,et.name) as earning_type, mf.name as minor_faction, format(eh.reward,0) as reward, eh.reward as sort_reward, format(eh.crew_wage,0) as crew_wage, eh.crew_wage as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id left join minor_faction mf on eh.minor_faction_id=mf.id where eh.user_id=? %s',
        '[["id"],["id"]]', 'select * from user where id=?',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"keyword":{"operator":"having","fields":["earned_on","earning_type","minor_faction","reward","crew_wage"]}}');
INSERT INTO x_player_report (id, title, `sql`, parameters, parameters_sql, filter_rules)
VALUES (2, 'Earning History Summary',
        'select u.commander_name, eh.user_id, eh.squadron_id, et.name as earning_type, count(eh.id) as num_transactions, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id where eh.user_id=? %s',
        '[["id"],["id"]]', 'select * from user where id=?',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"static":{"string":"group by earning_type_id"},"keyword":{"operator":"having","fields":["earning_type","num_transactions","reward","crew_wage"]}}');
INSERT INTO x_player_report (id, title, `sql`, parameters, parameters_sql, filter_rules)
VALUES (3, 'EDMC Transaction', 'select id, entry, entered_at from edmc where user_id=? %s', '[["id"],["id"]]',
        'select * from user where id=?',
        '{"date":{"operator":"and","fields":["entered_at"]},"keyword":{"operator":"having","fields":["entry"]}}');
INSERT INTO x_player_report (id, title, `sql`, parameters, parameters_sql, filter_rules)
VALUES (4, 'Earning History Summary by Minor Faction',
        'select u.commander_name, eh.user_id, eh.squadron_id, et.name as earning_type, mf.name as minor_faction, count(eh.id) as num_transactions, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id left join minor_faction mf on eh.minor_faction_id = mf.id where eh.user_id=? %s',
        '[["id"],["id"]]', 'select * from user where id=?',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"static":{"string":"group by earning_type_id, minor_faction_id"},"keyword":{"operator":"having","fields":["earning_type","minor_faction","num_transactions","reward","crew_wage"]}}');
INSERT INTO x_player_report (id, title, `sql`, parameters, parameters_sql, filter_rules)
VALUES (5, 'Minor Faction Activities Summary',
        'select u.commander_name, fa.user_id, fa.squadron_id, et.name as earning_type, mf.name as minor_faction, tmf.name as target_minor_faction, count(fa.id) as num_transactions, format(sum(fa.reward),0) as reward, sum(fa.reward) as sort_reward from faction_activity fa right outer join user u on fa.user_id = u.id left join earning_type et on fa.earning_type_id = et.id left join minor_faction mf on fa.minor_faction_id = mf.id left join minor_faction tmf on fa.target_minor_faction_id = tmf.id where fa.user_id=? %s',
        '[["id"],["id"]]', 'select * from user where id=?',
        '{"date":{"operator":"and","fields":["fa.earned_on"]},"static":{"string":"group by earning_type_id, minor_faction_id, target_minor_faction_id"},"keyword":{"operator":"having","fields":["earning_type","minor_faction","target_minor_faction","num_transactions","reward"]}}');
INSERT INTO x_player_report (id, title, `sql`, parameters, parameters_sql, filter_rules)
VALUES (6, 'Criminal History (Detailed)',
        'select c.committed_on as committed_on, if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, mf.name as minor_faction, c.victim as victim, format(c.fine,0) as fine, c.fine as sort_fine, format(c.bounty,0) as bounty, c.bounty as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?',
        '{"date":{"operator":"and","fields":["committed_on"]},"keyword":{"operator":"having","fields":["crime_committed","minor_faction","victim","fine","bounty"]}}');
INSERT INTO x_player_report (id, title, `sql`, parameters, parameters_sql, filter_rules)
VALUES (7, 'Criminal History Summary',
        'select if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, count(c.id) as num_committed, format(sum(c.fine),0) as fine, sum(c.fine) as sort_fine, format(sum(c.bounty),0) as bounty, sum(c.bounty) as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?',
        '{"date":{"operator":"and","fields":["committed_on"]},"static":{"string":"group by crime_committed"},"keyword":{"operator":"having","fields":["crime_committed","num_committed","fine","bounty"]}}');
INSERT INTO x_player_report (id, title, `sql`, parameters, parameters_sql, filter_rules)
VALUES (8, 'Criminal History Summary by Faction',
        'select if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, mf.name as minor_faction, count(c.id) as num_committed, format(sum(c.fine),0) as fine, sum(c.fine) as sort_fine, format(sum(c.bounty),0) as bounty, sum(c.bounty) as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?',
        '{"date":{"operator":"and","fields":["committed_on"]},"static":{"string":"group by crime_committed, c.minor_faction_id"},"keyword":{"operator":"having","fields":["crime_committed","minor_faction","num_committed","fine","bounty"]}}');

DROP TABLE x_leaderboard_report;
CREATE TABLE IF NOT EXISTS x_leaderboard_report
(
  id             int(11) unsigned auto_increment
    primary key,
  title          varchar(255)  null,
  header         varchar(512)  null,
  columns        varchar(512)  null,
  `sql`          varchar(1024) null,
  count_sql      varchar(512)  null,
  parameters     varchar(512)  null,
  parameters_sql varchar(512)  null,
  order_id       tinyint(11)   null,
  order_dir      varchar(4)    null,
  cast_columns   varchar(512)  null,
  sort_columns   varchar(512)  null,
  trans_columns  varchar(512)  null
);
INSERT INTO x_leaderboard_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                                  order_dir, cast_columns, sort_columns, trans_columns)
VALUES (1, 'Overall Earning', '["Rank", "Commander Name", "Total Earned"]',
        '["rank", "commander_name", "total_earned"]',
        'select user_id, commander_name, b.squadron_id, format(total_earned,0) as total_earned, 1+(select count(*) from v_commander_total_earning a where a.total_earned > b.total_earned and a.squadron_id=?) as rank from v_commander_total_earning b left join user u on u.id=b.user_id where b.squadron_id=?',
        'select count(user_id) from v_commander_total_earning b left join user u on u.id=b.user_id where b.squadron_id=?',
        '[["squadron_id","squadron_id"],["squadron_id"]]', 'select * from user where id=?', 0, 'asc',
        '{"total_earned":"signed"}', null, null);
INSERT INTO x_leaderboard_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                                  order_dir, cast_columns, sort_columns, trans_columns)
VALUES (2, 'Overall Exploration',
        '["Commander Name","System Scanned","Bodies Discovered","Complete SAA Scans","Efficient Scans","Efficiency Rate"]',
        '["commander_name","systems_scanned","bodies_found","saa_scan_completed","efficiency_achieved","efficiency_rate"]',
        'select * from v_commander_exploration_total where squadron_id=?',
        'select count(user_id) from v_commander_exploration_total where squadron_id=?',
        '[["squadron_id"],["squadron_id"]]', 'select * from user where id=?', 0, 'asc', null, null, null);
INSERT INTO x_leaderboard_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                                  order_dir, cast_columns, sort_columns, trans_columns)
VALUES (3, 'Overall Trade',
        '["Commander Name","Units Bought","Units Sold","Net Units","Amt Paid","Amt Earned","Net Earned","Cr/Unit"]',
        '["commander_name","units_bought","units_sold","net_units","market_buy","market_sell","total","cr_per_unit"]',
        'select u.commander_name, v1.*, format(v2.market_buy,0) as market_buy, format(v2.market_sell,0) as market_sell, format(v2.total,0) as total, format((v2.total/v1.units_sold),2) as cr_per_unit from v_commander_market_net_units v1 left join v_commander_market_net_earning v2 on v1.user_id = v2.id and v1.squadron_id=v2.squadron_id right outer join user u on v1.user_id=u.id where u.squadron_id=?',
        'select count(*) from user where squadron_id=?', '[["squadron_id"],["squadron_id"]]',
        'select * from user where id=?', 0, 'asc',
        '{"market_buy":"signed","market_sell":"signed","total":"signed","cr_per_unit":"signed"}', null, null);
INSERT INTO x_leaderboard_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                                  order_dir, cast_columns, sort_columns, trans_columns)
VALUES (4, 'Overall Combat (Non-mission)', '["Commander","Num Combat Bonds","Total Reward","Crew Wage"]',
        '["commander_name","num_bonds","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, count(eh.id) as num_bonds, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id where eh.squadron_id=? and earning_type_id in (1,2,3) group by eh.user_id',
        'select count(*) from user where squadron_id=?', '[["squadron_id"],["squadron_id"]]',
        'select * from user where id=?', 0, 'asc', null, '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', null);
INSERT INTO x_leaderboard_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                                  order_dir, cast_columns, sort_columns, trans_columns)
VALUES (5, 'Overall Community Goal', '["Commander","CG Participated","Total Reward","Crew Wage"]',
        '["commander_name","cg_participated","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, count(eh.id) as cg_participated, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id where eh.squadron_id=? and earning_type_id=''7'' group by eh.user_id',
        'select count(*) from user where squadron_id=?', '[["squadron_id"],["squadron_id"]]',
        'select * from user where id=?', 0, 'asc', null, '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', null);
INSERT INTO x_leaderboard_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                                  order_dir, cast_columns, sort_columns, trans_columns)
VALUES (6, 'Overall Missions', '["Commander","Missions Completed","Total Reward","Crew Wage"]',
        '["commander_name","missions_completed","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, count(eh.id) as missions_completed, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id where eh.squadron_id=? and earning_type_id>7 group by eh.user_id',
        'select count(*) from user where squadron_id=?', '[["squadron_id"],["squadron_id"]]',
        'select * from user where id=?', 0, 'asc', null, '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', null);

CREATE OR REPLACE VIEW v_commander_mission_total as
select eh.squadron_id as squadron_id, eh.user_id as user_id, sum(eh.reward) as total_earned, earned_on
from earning_history eh
       left join earning_type et on eh.earning_type_id = et.id
where et.mission_flag = 1
group by eh.squadron_id, eh.user_id, eh.earned_on;

TRUNCATE TABLE earning_type;
INSERT INTO earning_type (id, name, mission_flag)
VALUES (1, 'Bounty', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (2, 'CapShipBond', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (3, 'FactionKillBond', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (4, 'ExplorationData', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (5, 'MarketBuy', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (6, 'MarketSell', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (7, 'CommunityGoalReward', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (8, 'MissionCompleted', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (9, 'Mission_Altruism', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (10, 'Mission_Assassinate', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (11, 'Mission_AssassinateWing', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (12, 'Mission_Collect', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (13, 'Mission_Courier', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (14, 'Mission_Delivery', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (15, 'Mission_DeliveryWing', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (16, 'Mission_Disable', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (17, 'Mission_DS', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (18, 'Mission_Hack', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (19, 'Mission_Massacre', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (20, 'Mission_PassengerBulk', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (21, 'Mission_Salvage', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (22, 'Mission_Scan', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (23, 'Mission_Sightseeing', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (24, 'Mission_TheDead', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (25, 'CombatBond', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (26, 'Trade', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (27, 'Settlement', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (28, 'Scannable', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (29, 'Codex', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (30, 'SearchAndRescue', 0);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (31, 'Mission_Rescue', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (32, 'Mission_PassengerVIP', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (33, 'Mission_Mining', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (34, 'Mission_MassacreWing', 1);

TRUNCATE TABLE crime_type;
INSERT INTO crime_type (id, name, alias)
VALUES (1, 'Assault', null);
INSERT INTO crime_type (id, name, alias)
VALUES (2, 'Murder', null);
INSERT INTO crime_type (id, name, alias)
VALUES (3, 'Piracy', null);
INSERT INTO crime_type (id, name, alias)
VALUES (4, 'Interdiction', null);
INSERT INTO crime_type (id, name, alias)
VALUES (5, 'IllegalCargo', null);
INSERT INTO crime_type (id, name, alias)
VALUES (6, 'DisobeyPolice', null);
INSERT INTO crime_type (id, name, alias)
VALUES (7, 'FireInNoFireZone', null);
INSERT INTO crime_type (id, name, alias)
VALUES (8, 'FireInStation', null);
INSERT INTO crime_type (id, name, alias)
VALUES (9, 'DumpingDangerous', null);
INSERT INTO crime_type (id, name, alias)
VALUES (10, 'DumpingNearStation', null);
INSERT INTO crime_type (id, name, alias)
VALUES (11, 'DockingMinorBlockingAirlock', '["DockingMinor_BlockingAirlock"]');
INSERT INTO crime_type (id, name, alias)
VALUES (12, 'DockingMajorBlockingAirlock', '["DockingMajor_BlockingAirlock"]');
INSERT INTO crime_type (id, name, alias)
VALUES (13, 'DockingMinorBlockingLandingPad', '["DockingMinor_BlockingLandingPad"]');
INSERT INTO crime_type (id, name, alias)
VALUES (14, 'DockingMajorBlockingLandingPad', '["DockingMajor_BlockingLandingPad"]');
INSERT INTO crime_type (id, name, alias)
VALUES (15, 'DockingMinorTrespass', '["DockingMinorTresspass","DockingMinor_Trespass","DockingMinor_Tresspass"]');
INSERT INTO crime_type (id, name, alias)
VALUES (16, 'DockingMajorTrespass', '["DockingMajorTresspass","DockingMajor_Trespass","DockingMajor_Tresspass"]');
INSERT INTO crime_type (id, name, alias)
VALUES (17, 'CollidedAtSpeedInNoFireZone', null);
INSERT INTO crime_type (id, name, alias)
VALUES (18, 'CollidedAtSpeedInNoFireZoneHullDamage', '["CollidedAtSpeedInNoFireZone_HullDamage"]');
INSERT INTO crime_type (id, name, alias)
VALUES (19, 'RecklessWeaponsDischarge', null);
INSERT INTO crime_type (id, name, alias)
VALUES (20, 'Other', null);

UPDATE rank
SET group_code='squadron'
where group_code = 'service';

SET FOREIGN_KEY_CHECKS = 1;
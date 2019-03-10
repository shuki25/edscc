SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE x_player_report;
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns)
VALUES (1, 'Earning History (Complete)', '["Date","Type","Paying Minor Faction","Amount","Crew Paid"]',
        '["earned_on","earning_type","minor_faction","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, eh.earned_on, if(eh.earning_type_id=''8'' and eh.notes is not null,concat(et.name, '' ('', eh.notes, '')'') ,et.name) as earning_type, mf.name as minor_faction, format(eh.reward,0) as reward, eh.reward as sort_reward, format(eh.crew_wage,0) as crew_wage, eh.crew_wage as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id left join minor_faction mf on eh.minor_faction_id=mf.id where eh.user_id=?',
        'select count(*) from earning_history where user_id=?', '[["id"],["id"]]', 'select * from user where id=?', 0,
        'desc', null, '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns)
VALUES (2, 'Earning History Summary', '["Type","Count","Amount","Crew Paid"]',
        '["earning_type","num_transactions","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, et.name as earning_type, count(eh.id) as num_transactions, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id where eh.user_id=? group by earning_type_id',
        'select count(*) from (select id from earning_history where user_id=? group by earning_type_id) a',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns)
VALUES (3, 'EDMC Transaction', '["ID","Data","Timestamp"]', '["id","entry","entered_at"]',
        'select id, entry, entered_at from edmc where user_id=?', 'select count(*) from edmc where user_id=?',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null, null);
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns)
VALUES (4, 'Earning History Summary by Minor Faction', '["Type","Paying Minor Faction","Count","Amount","Crew Paid"]',
        '["earning_type","minor_faction","num_transactions","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, et.name as earning_type, mf.name as minor_faction, count(eh.id) as num_transactions, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id left join minor_faction mf on eh.minor_faction_id = mf.id where eh.user_id=? group by earning_type_id, minor_faction_id',
        'select count(*) from (select id from earning_history where user_id=? group by earning_type_id, minor_faction_id) a',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns)
VALUES (5, 'Minor Faction Activities Summary',
        '["Type","Paying Minor Faction","Targeted Minor Faction","Count","Amount"]',
        '["earning_type","minor_faction","target_minor_faction","num_transactions","reward"]',
        'select u.commander_name, fa.user_id, fa.squadron_id, et.name as earning_type, mf.name as minor_faction, tmf.name as target_minor_faction, count(fa.id) as num_transactions, format(sum(fa.reward),0) as reward, sum(fa.reward) as sort_reward from faction_activity fa right outer join user u on fa.user_id = u.id left join earning_type et on fa.earning_type_id = et.id left join minor_faction mf on fa.minor_faction_id = mf.id left join minor_faction tmf on fa.target_minor_faction_id = tmf.id where fa.user_id=? group by earning_type_id, minor_faction_id, target_minor_faction_id',
        'select count(*) from (select id from faction_activity where user_id=? group by earning_type_id, minor_faction_id, target_minor_faction_id) a',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null, '{"reward":"sort_reward"}');

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

SET FOREIGN_KEY_CHECKS = 1;
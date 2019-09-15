SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE acl;
INSERT INTO acl (id, role_string, description, list_order, admin_flag)
VALUES (1, 'ROLE_ADMIN', 'User has full administrative rights (overrides everything below)', 1, 1);
INSERT INTO acl (id, role_string, description, list_order, admin_flag)
VALUES (2, 'ROLE_EDITOR', 'User can add, edit, delete announcements', 3, 0);
INSERT INTO acl (id, role_string, description, list_order, admin_flag)
VALUES (3, 'CAN_CHANGE_STATUS', 'User can approve, deny, ban or lock out a squadron member', 4, 0);
INSERT INTO acl (id, role_string, description, list_order, admin_flag)
VALUES (4, 'CAN_EDIT_USER', 'User can edit a squadron member account settings', 5, 0);
INSERT INTO acl (id, role_string, description, list_order, admin_flag)
VALUES (5, 'CAN_EDIT_PERMISSIONS', 'User can modify access permissions of a squadron member', 6, 0);
INSERT INTO acl (id, role_string, description, list_order, admin_flag)
VALUES (6, 'CAN_VIEW_HISTORY', 'User can view a history log of a squadron member', 7, 0);
INSERT INTO acl (id, role_string, description, list_order, admin_flag)
VALUES (7, 'CAN_MODIFY_SELF', 'User can modify settings or permissions for himself/herself', 8, 0);
INSERT INTO acl (id, role_string, description, list_order, admin_flag)
VALUES (8, 'CAN_VIEW_REPORTS', 'User can view management reports', 2, 0);

TRUNCATE TABLE language;
INSERT INTO language (id, locale, name, locale_name, has_translation, percent_complete, verified)
VALUES (1, 'en', 'English', 'English', 1, 100, 1);
INSERT INTO language (id, locale, name, locale_name, has_translation, percent_complete, verified)
VALUES (2, 'de', 'German', 'Deustch', 1, 100, 1);
INSERT INTO language (id, locale, name, locale_name, has_translation, percent_complete, verified)
VALUES (3, 'es', 'Spanish', 'Español', 1, 87, 1);
INSERT INTO language (id, locale, name, locale_name, has_translation, percent_complete, verified)
VALUES (4, 'fr', 'French', 'Français', 1, 98, 1);
INSERT INTO language (id, locale, name, locale_name, has_translation, percent_complete, verified)
VALUES (5, 'nl', 'Dutch', 'Nederlands', 1, 96, 1);
INSERT INTO language (id, locale, name, locale_name, has_translation, percent_complete, verified)
VALUES (6, 'pt', 'Portuguese', 'Português', 1, 85, 0);
INSERT INTO language (id, locale, name, locale_name, has_translation, percent_complete, verified)
VALUES (7, 'ru', 'Russian', 'русский', 1, 100, 1);

DROP TABLE IF EXISTS x_player_report;
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
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (1, 'Earning History (Detailed)', '["Date","Type","Paying Minor Faction","Amount","Crew Paid"]',
        '["earned_on","earning_type","minor_faction","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, eh.earned_on, if(eh.earning_type_id=''8'' and eh.notes is not null,concat(et.name, '' ('', eh.notes, '')'') ,et.name) as earning_type, mf.name as minor_faction, format(eh.reward,0) as reward, eh.reward as sort_reward, format(eh.crew_wage,0) as crew_wage, eh.crew_wage as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id left join minor_faction mf on eh.minor_faction_id=mf.id where eh.user_id=? %s',
        'select count(*) from earning_history where user_id=?', '[["id"],["id"]]', 'select * from user where id=?', 0,
        'desc', null, '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', '["earning_type"]',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"keyword":{"operator":"having","fields":["earned_on","earning_type","minor_faction","reward","crew_wage"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (2, 'Earning History Summary', '["Type","Count","Amount","Crew Paid"]',
        '["earning_type","num_transactions","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, et.name as earning_type, count(eh.id) as num_transactions, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id where eh.user_id=? %s',
        'select count(*) from (select id from earning_history where user_id=? group by earning_type_id) a',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', '["earning_type"]',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"static":{"string":"group by earning_type_id"},"keyword":{"operator":"having","fields":["earning_type","num_transactions","reward","crew_wage"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (3, 'EDMC Transaction', '["ID","Data","Timestamp"]', '["id","entry","entered_at"]',
        'select id, entry, entered_at from edmc where user_id=? %s', 'select count(*) from edmc where user_id=?',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null, null, null,
        '{"date":{"operator":"and","fields":["entered_at"]},"keyword":{"operator":"having","fields":["entry"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (4, 'Earning History Summary by Minor Faction', '["Type","Paying Minor Faction","Count","Amount","Crew Paid"]',
        '["earning_type","minor_faction","num_transactions","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, et.name as earning_type, mf.name as minor_faction, count(eh.id) as num_transactions, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id left join minor_faction mf on eh.minor_faction_id = mf.id where eh.user_id=? %s',
        'select count(*) from (select id from earning_history where user_id=? group by earning_type_id, minor_faction_id) a',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', '["earning_type"]',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"static":{"string":"group by earning_type_id, minor_faction_id"},"keyword":{"operator":"having","fields":["earning_type","minor_faction","num_transactions","reward","crew_wage"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (5, 'Minor Faction Activities Summary',
        '["Type","Paying Minor Faction","Targeted Minor Faction","Count","Amount"]',
        '["earning_type","minor_faction","target_minor_faction","num_transactions","reward"]',
        'select u.commander_name, fa.user_id, fa.squadron_id, et.name as earning_type, mf.name as minor_faction, tmf.name as target_minor_faction, count(fa.id) as num_transactions, format(sum(fa.reward),0) as reward, sum(fa.reward) as sort_reward from faction_activity fa right outer join user u on fa.user_id = u.id left join earning_type et on fa.earning_type_id = et.id left join minor_faction mf on fa.minor_faction_id = mf.id left join minor_faction tmf on fa.target_minor_faction_id = tmf.id where fa.user_id=? %s',
        'select count(*) from (select id from faction_activity where user_id=? group by earning_type_id, minor_faction_id, target_minor_faction_id) a',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null, '{"reward":"sort_reward"}',
        '["earning_type"]',
        '{"date":{"operator":"and","fields":["fa.earned_on"]},"static":{"string":"group by earning_type_id, minor_faction_id, target_minor_faction_id"},"keyword":{"operator":"having","fields":["earning_type","minor_faction","target_minor_faction","num_transactions","reward"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (6, 'Criminal History (Detailed)',
        '["Date Committed","Crime","Minor Faction Issued By","Victim","Fine","Bounty"]',
        '["committed_on","crime_committed","minor_faction","victim","fine","bounty"]',
        'select c.committed_on as committed_on, if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, mf.name as minor_faction, c.victim as victim, format(c.fine,0) as fine, c.fine as sort_fine, format(c.bounty,0) as bounty, c.bounty as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        'select count(*) from (select id from crime where user_id=? and squadron_id=?) a',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"fine":"sort_fine","bounty":"sort_bounty"}', '["crime_committed"]',
        '{"date":{"operator":"and","fields":["committed_on"]},"keyword":{"operator":"having","fields":["crime_committed","minor_faction","victim","fine","bounty"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (7, 'Criminal History Summary', '["Crime","Count","Fine","Bounty"]',
        '["crime_committed","num_committed","fine","bounty"]',
        'select if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, count(c.id) as num_committed, format(sum(c.fine),0) as fine, sum(c.fine) as sort_fine, format(sum(c.bounty),0) as bounty, sum(c.bounty) as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        'select count(*) from (select id from crime where user_id=? and squadron_id=? group by crime_type_id) a',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?', 1, 'desc', null,
        '{"fine":"sort_fine","bounty":"sort_bounty"}', '["crime_committed"]',
        '{"date":{"operator":"and","fields":["committed_on"]},"static":{"string":"group by crime_committed"},"keyword":{"operator":"having","fields":["crime_committed","num_committed","fine","bounty"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (8, 'Criminal History Summary by Faction', '["Crime","Minor Faction Issued By","Count","Fine","Bounty"]',
        '["crime_committed","minor_faction","num_committed","fine","bounty"]',
        'select if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, mf.name as minor_faction, count(c.id) as num_committed, format(sum(c.fine),0) as fine, sum(c.fine) as sort_fine, format(sum(c.bounty),0) as bounty, sum(c.bounty) as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        'select count(*) from (select id from crime where user_id=? and squadron_id=? group by crime_type_id, minor_faction_id) a',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?', 0, 'asc', null,
        '{"fine":"sort_fine","bounty":"sort_bounty"}', '["crime_committed"]',
        '{"date":{"operator":"and","fields":["committed_on"]},"static":{"string":"group by crime_committed, c.minor_faction_id"},"keyword":{"operator":"having","fields":["crime_committed","minor_faction","num_committed","fine","bounty"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (9, 'Anti-Xeno Activities Summary', '["Paying Minor Faction","Thargoid Variant","Count","Amount"]',
        '["minor_faction","thargoid_variant","num_transactions","reward"]',
        'select u.commander_name, ta.user_id, ta.squadron_id, mf.name as minor_faction, tv.name as thargoid_variant, count(ta.id) as num_transactions, format(sum(ta.reward),0) as reward, sum(ta.reward) as sort_reward from thargoid_activity ta right outer join user u on ta.user_id = u.id left join thargoid_variant tv on ta.thargoid_id=tv.id left join minor_faction mf on ta.minor_faction_id = mf.id where ta.user_id=? and ta.squadron_id=? %s',
        'select count(*) from (select id from thargoid_activity where user_id=? and squadron_id=? group by thargoid_id) a',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?', 1, 'asc', null,
        '{"reward":"sort_reward"}', null,
        '{"date":{"operator":"and","fields":["ta.earned_on"]},"static":{"string":"group by thargoid_id"},"keyword":{"operator":"having","fields":["minor_faction","thargoid_variant","num_transactions","reward"]}}');
INSERT INTO x_player_report (id, title, header, columns, `sql`, count_sql, parameters, parameters_sql, order_id,
                             order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (10, 'Anti-Xeno Activities (Detailed)', '["Date Killed","Paying Minor Faction","Thargoid Variant","Amount"]',
        '["date_killed","minor_faction","thargoid_variant","reward"]',
        'select u.commander_name, ta.user_id, ta.squadron_id, ta.date_killed, mf.name as minor_faction, tv.name as thargoid_variant, format(ta.reward,0) as reward, ta.reward as sort_reward from thargoid_activity ta right outer join user u on ta.user_id = u.id left join minor_faction mf on ta.minor_faction_id = mf.id left join thargoid_variant tv on ta.thargoid_id=tv.id where ta.user_id=? and ta.squadron_id=? %s',
        'select count(*) from (select id from thargoid_activity where user_id=? and squadron_id=?) a',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"reward":"sort_reward"}', null,
        '{"date":{"operator":"and","fields":["ta.date_killed"]},"keyword":{"operator":"having","fields":["date_killed","minor_faction","thargoid_variant","reward"]}}');

DROP TABLE IF EXISTS x_leaderboard_report;
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

DROP TABLE IF EXISTS x_manager_report;
CREATE TABLE IF NOT EXISTS x_manager_report
(
    id             int(11) unsigned auto_increment
        primary key,
    title          varchar(255)          null,
    per_user       tinyint(11) default 0 not null,
    header         varchar(512)          null,
    columns        varchar(512)          null,
    `sql`          varchar(1024)         null,
    count_sql      varchar(512)          null,
    parameters     varchar(512)          null,
    parameters_sql varchar(512)          null,
    order_id       tinyint(11)           null,
    order_dir      varchar(4)            null,
    cast_columns   varchar(512)          null,
    sort_columns   varchar(512)          null,
    trans_columns  varchar(512)          null,
    filter_rules   varchar(512)          null
);
INSERT INTO x_manager_report (id, title, per_user, header, columns, `sql`, count_sql, parameters, parameters_sql,
                              order_id, order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (1, 'Earning History (Detailed)', 1, '["Date","Type","Paying Minor Faction","Amount","Crew Paid"]',
        '["earned_on","earning_type","minor_faction","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, eh.earned_on, if(eh.earning_type_id=''8'' and eh.notes is not null,concat(et.name, '' ('', eh.notes, '')'') ,et.name) as earning_type, mf.name as minor_faction, format(eh.reward,0) as reward, eh.reward as sort_reward, format(eh.crew_wage,0) as crew_wage, eh.crew_wage as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id left join minor_faction mf on eh.minor_faction_id=mf.id where eh.user_id=? %s',
        'select count(*) from earning_history where user_id=?', '[["id"],["id"]]', 'select * from user where id=?', 0,
        'desc', null, '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', '["earning_type"]',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"keyword":{"operator":"having","fields":["earned_on","earning_type","minor_faction","reward","crew_wage"]}}');
INSERT INTO x_manager_report (id, title, per_user, header, columns, `sql`, count_sql, parameters, parameters_sql,
                              order_id, order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (2, 'Earning History Summary', 0, '["Type","Count","Amount","Crew Paid"]',
        '["earning_type","num_transactions","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, et.name as earning_type, count(eh.id) as num_transactions, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id where squadron_id=? %s',
        'select count(*) from (select id from earning_history where squadron_id=? group by earning_type_id) a',
        '[["squadron_id"],["squadron_id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', '["earning_type"]',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"static":{"string":"group by earning_type_id"},"keyword":{"operator":"having","fields":["earning_type","num_transactions","reward","crew_wage"]}}');
INSERT INTO x_manager_report (id, title, per_user, header, columns, `sql`, count_sql, parameters, parameters_sql,
                              order_id, order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (3, 'EDMC Transaction', 1, '["ID","Data","Timestamp"]', '["id","entry","entered_at"]',
        'select id, entry, entered_at from edmc where user_id=? %s', 'select count(*) from edmc where user_id=?',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null, null, null,
        '{"date":{"operator":"and","fields":["entered_at"]},"keyword":{"operator":"having","fields":["entry"]}}');
INSERT INTO x_manager_report (id, title, per_user, header, columns, `sql`, count_sql, parameters, parameters_sql,
                              order_id, order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (4, 'Earning History Summary by Minor Faction', 0,
        '["Type","Paying Minor Faction","Count","Amount","Crew Paid"]',
        '["earning_type","minor_faction","num_transactions","reward","crew_wage"]',
        'select u.commander_name, eh.user_id, eh.squadron_id, et.name as earning_type, mf.name as minor_faction, count(eh.id) as num_transactions, format(sum(eh.reward),0) as reward, sum(eh.reward) as sort_reward, format(sum(eh.crew_wage),0) as crew_wage, sum(eh.crew_wage) as sort_crew_wage from earning_history eh right outer join user u on eh.user_id = u.id left join earning_type et on eh.earning_type_id = et.id left join minor_faction mf on eh.minor_faction_id = mf.id where eh.user_id=? %s',
        'select count(*) from (select id from earning_history where user_id=? group by earning_type_id, minor_faction_id) a',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"reward":"sort_reward","crew_wage":"sort_crew_wage"}', '["earning_type"]',
        '{"date":{"operator":"and","fields":["eh.earned_on"]},"static":{"string":"group by earning_type_id, minor_faction_id"},"keyword":{"operator":"having","fields":["earning_type","minor_faction","num_transactions","reward","crew_wage"]}}');
INSERT INTO x_manager_report (id, title, per_user, header, columns, `sql`, count_sql, parameters, parameters_sql,
                              order_id, order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (5, 'Minor Faction Activities Summary', 0,
        '["Type","Paying Minor Faction","Targeted Minor Faction","Count","Amount"]',
        '["earning_type","minor_faction","target_minor_faction","num_transactions","reward"]',
        'select u.commander_name, fa.user_id, fa.squadron_id, et.name as earning_type, mf.name as minor_faction, tmf.name as target_minor_faction, count(fa.id) as num_transactions, format(sum(fa.reward),0) as reward, sum(fa.reward) as sort_reward from faction_activity fa right outer join user u on fa.user_id = u.id left join earning_type et on fa.earning_type_id = et.id left join minor_faction mf on fa.minor_faction_id = mf.id left join minor_faction tmf on fa.target_minor_faction_id = tmf.id where fa.user_id=? %s',
        'select count(*) from (select id from faction_activity where user_id=? group by earning_type_id, minor_faction_id, target_minor_faction_id) a',
        '[["id"],["id"]]', 'select * from user where id=?', 0, 'desc', null, '{"reward":"sort_reward"}',
        '["earning_type"]',
        '{"date":{"operator":"and","fields":["fa.earned_on"]},"static":{"string":"group by earning_type_id, minor_faction_id, target_minor_faction_id"},"keyword":{"operator":"having","fields":["earning_type","minor_faction","target_minor_faction","num_transactions","reward"]}}');
INSERT INTO x_manager_report (id, title, per_user, header, columns, `sql`, count_sql, parameters, parameters_sql,
                              order_id, order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (6, 'Criminal History (Detailed)', 1,
        '["Date Committed","Crime","Minor Faction Issued By","Victim","Fine","Bounty"]',
        '["committed_on","crime_committed","minor_faction","victim","fine","bounty"]',
        'select c.committed_on as committed_on, if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, mf.name as minor_faction, c.victim as victim, format(c.fine,0) as fine, c.fine as sort_fine, format(c.bounty,0) as bounty, c.bounty as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        'select count(*) from (select id from crime where user_id=? and squadron_id=?) a',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?', 0, 'desc', null,
        '{"fine":"sort_fine","bounty":"sort_bounty"}', '["crime_committed"]',
        '{"date":{"operator":"and","fields":["committed_on"]},"keyword":{"operator":"having","fields":["crime_committed","minor_faction","victim","fine","bounty"]}}');
INSERT INTO x_manager_report (id, title, per_user, header, columns, `sql`, count_sql, parameters, parameters_sql,
                              order_id, order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (7, 'Criminal History Summary', 0, '["Crime","Count","Fine","Bounty"]',
        '["crime_committed","num_committed","fine","bounty"]',
        'select if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, count(c.id) as num_committed, format(sum(c.fine),0) as fine, sum(c.fine) as sort_fine, format(sum(c.bounty),0) as bounty, sum(c.bounty) as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        'select count(*) from (select id from crime where user_id=? and squadron_id=? group by crime_type_id) a',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?', 1, 'desc', null,
        '{"fine":"sort_fine","bounty":"sort_bounty"}', '["crime_committed"]',
        '{"date":{"operator":"and","fields":["committed_on"]},"static":{"string":"group by crime_committed"},"keyword":{"operator":"having","fields":["crime_committed","num_committed","fine","bounty"]}}');
INSERT INTO x_manager_report (id, title, per_user, header, columns, `sql`, count_sql, parameters, parameters_sql,
                              order_id, order_dir, cast_columns, sort_columns, trans_columns, filter_rules)
VALUES (8, 'Criminal History Summary by Faction', 0, '["Crime","Minor Faction Issued By","Count","Fine","Bounty"]',
        '["crime_committed","minor_faction","num_committed","fine","bounty"]',
        'select if(c.notes is not null, concat(ct.name, '' ('', c.notes, '')''), ct.name) as crime_committed, mf.name as minor_faction, count(c.id) as num_committed, format(sum(c.fine),0) as fine, sum(c.fine) as sort_fine, format(sum(c.bounty),0) as bounty, sum(c.bounty) as sort_bounty from crime c left join crime_type ct on c.crime_type_id = ct.id left join minor_faction mf on c.minor_faction_id = mf.id where c.user_id=? and c.squadron_id=? %s',
        'select count(*) from (select id from crime where user_id=? and squadron_id=? group by crime_type_id, minor_faction_id) a',
        '[["id","squadron_id"],["id","squadron_id"]]', 'select * from user where id=?', 0, 'asc', null,
        '{"fine":"sort_fine","bounty":"sort_bounty"}', '["crime_committed"]',
        '{"date":{"operator":"and","fields":["committed_on"]},"static":{"string":"group by crime_committed, c.minor_faction_id"},"keyword":{"operator":"having","fields":["crime_committed","minor_faction","num_committed","fine","bounty"]}}');

DROP TABLE IF EXISTS x_variable;
CREATE TABLE IF NOT EXISTS x_variable
(
    id          int(11) unsigned auto_increment
        primary key,
    name        varchar(32)           null,
    columns     varchar(255)          null,
    `sql`       varchar(1024)         null,
    parameters  varchar(255)          null,
    long_format smallint(1) default 0 null,
    long_prefix varchar(50)           null,
    long_column varchar(50)           null
);

INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (1, 'default', '*', 'select id, squadron_id, rank_id, custom_rank_id, status_id from user where id=?', '["id"]',
        0, null, null);
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (2, 'activity_total', '*',
        'select a.*, (a.efficiency_achieved/a.saa_scan_completed)*100 as efficiency_percent from (select sum(bounties_claimed) as bounties_claimed , sum(systems_scanned) as systems_scanned, sum(bodies_found) as bodies_found, sum(saa_scan_completed) as saa_scan_completed, sum(efficiency_achieved) as efficiency_achieved, sum(market_buy) as market_buy, sum(market_sell) as market_sell, sum(missions_completed) as missions_completed, sum(mining_refined) as mining_refined, sum(stolen_goods) as stolen_goods, sum(cg_participated) as cg_participated, sum(crimes_committed) as crimes_committed from activity_counter where user_id=? and squadron_id=?) a',
        '["id","squadron_id"]', 0, null, null);
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (3, 'commander', '*', 'select * from commander where id=?', '["id"]', 0, null, null);
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (4, 'crime_count', '*',
        'select b.name, count(a.penalty_fee) as long_value from (select crime_type_id, if(fine, fine, bounty) as penalty_fee from crime where user_id=? and squadron_id=?) a right join crime_type b on b.id=a.crime_type_id group by b.id',
        '["id","squadron_id"]', 1, 'crime_count_', 'name');
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (5, 'earning_sum', '*',
        'select b.name, a.reward as long_value from (select earning_type_id, sum(reward) as reward from earning_history where user_id=? and squadron_id=? group by earning_type_id) a right join earning_type b on b.id=a.earning_type_id',
        '["id","squadron_id"]', 1, 'earning_sum_', 'name');
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (6, 'earning_count', '*',
        'select b.name, a.reward as long_value from (select earning_type_id, count(reward) as reward from earning_history where user_id=? and squadron_id=? group by earning_type_id) a right join earning_type b on b.id=a.earning_type_id',
        '["id","squadron_id"]', 1, 'earning_count_', 'name');
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (7, 'crime_max', '*',
        'select b.name, max(a.penalty_fee) as long_value from (select crime_type_id, if(fine, fine, bounty) as penalty_fee from crime where user_id=? and squadron_id=?) a right join crime_type b on b.id=a.crime_type_id group by b.id',
        '["id","squadron_id"]', 1, 'crime_max_', 'name');
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (8, 'crime_sum', '*',
        'select b.name, sum(a.penalty_fee) as long_value from (select crime_type_id, if(fine, fine, bounty) as penalty_fee from crime where user_id=? and squadron_id=?) a right join crime_type b on b.id=a.crime_type_id group by b.id',
        '["id","squadron_id"]', 1, 'crime_sum_', 'name');
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (9, 'earning_max', '*',
        'select b.name, a.reward as long_value from (select earning_type_id, max(reward) as reward from earning_history where user_id=? and squadron_id=? group by earning_type_id) a right join earning_type b on b.id=a.earning_type_id',
        '["id","squadron_id"]', 1, 'earning_max_', 'name');
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (10, 'thargoid_max_reward', '*',
        'select b.name, max(a.reward) as long_value from (select user_id, squadron_id, earning_type_id, reward from faction_activity where user_id=? and squadron_id=? and target_minor_faction_id in (select id from minor_faction where name=''Thargoids'' or name=''$faction_Thargoid;'')) a right join earning_type b on b.id=a.earning_type_id',
        '["id", "squadron_id"]', 1, 'thargoid_max_', 'name');
INSERT INTO x_variable (id, name, columns, `sql`, parameters, long_format, long_prefix, long_column)
VALUES (11, 'thargoid_count', '*',
        'select b.name, count(a.reward) as long_value from (select user_id, squadron_id, earning_type_id, reward from faction_activity where user_id=? and squadron_id=? and target_minor_faction_id in (select id from minor_faction where name=''Thargoids'' or name=''$faction_Thargoid;'')) a right join earning_type b on b.id=a.earning_type_id',
        '["id", "squadron_id"]', 1, 'thargoid_count_', 'name');

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
INSERT INTO earning_type (id, name, mission_flag)
VALUES (35, 'Chain_WrongTarget', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (36, 'Chain_SeekingAsylum', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (37, 'Chain_SalvageJustice', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (38, 'Chain_SafeTravelling', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (39, 'Chain_RegainFooting', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (40, 'Chain_PlanetaryIncursions', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (41, 'Chain_HelpFinishTheOrder', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (42, 'Chain_FindThePirateLord', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (43, 'Chain_Delivery', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (44, 'Mission_Smuggle', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (45, 'Mission_RS', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (46, 'Mission_MassacreWing', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (47, 'Mission_LongDistanceExpedition', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (48, 'Mission_DisableMegaship', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (49, 'Chain_Assassinate', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (50, 'Chain_MiningToOrder', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (51, 'Chain_Salvage', 1);
INSERT INTO earning_type (id, name, mission_flag)
VALUES (52, 'Mission_MassacreThargoid', 1);

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
INSERT INTO crime_type (id, name, alias)
VALUES (21, 'passengerWanted', '["PassengerWanted"]');

UPDATE `rank`
SET group_code='squadron'
where group_code = 'service';

TRUNCATE TABLE tags;
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (1, 'activities', 'Anti-Xeno Activists', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (2, 'activities', 'Bounty Hunters', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (3, 'activities', 'Explorers', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (4, 'activities', 'Faction Supporters', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (5, 'activities', 'Humanitarian Aid Providers', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (6, 'activities', 'Pirates', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (7, 'activities', 'Power Supporters', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (8, 'activities', 'Traders', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (9, 'activities', 'Miners', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (10, 'activities', 'Fuel Rats', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (11, 'activities', 'Seals', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (12, 'availability', 'Occasional', 'bg-orange');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (13, 'availability', 'Weekdays', 'bg-orange');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (14, 'availability', 'Weekends', 'bg-orange');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (15, 'availability', 'Weeknights', 'bg-orange');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (16, 'game_mode', 'Relaxed', 'bg-green');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (17, 'game_mode', 'Family', 'bg-green');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (18, 'game_mode', 'Devoted', 'bg-green');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (19, 'play_style', 'PvE', 'bg-olive');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (20, 'play_style', 'PvP', 'bg-olive');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (21, 'play_style', 'Roleplay', 'bg-olive');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (22, 'language', 'English', 'bg-navy');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (23, 'language', 'Portuguese', 'bg-navy');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (24, 'language', 'German', 'bg-navy');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (25, 'language', 'French', 'bg-navy');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (26, 'language', 'Spanish', 'bg-navy');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (27, 'language', 'Russian', 'bg-navy');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (28, 'attitude', 'Solo', 'bg-default');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (29, 'attitude', 'Open', 'bg-default');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (30, 'attitude', 'Private Group', 'bg-default');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (31, 'activities', 'FA off', 'bg-blue');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (32, 'platform', 'PC', 'bg-purple');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (33, 'platform', 'XBox', 'bg-purple');
INSERT INTO tags (id, group_code, name, badge_color)
VALUES (34, 'platform', 'PS4', 'bg-purple');

TRUNCATE TABLE achievement_rule;
INSERT INTO achievement_rule (id, name, description, trophy_image)
VALUES (1, 'Bug Swatter', 'Killed 25 Thargoids', null);
INSERT INTO achievement_rule (id, name, description, trophy_image)
VALUES (2, 'Killed a Bug', 'First Thargoid Kill', null);
INSERT INTO achievement_rule (id, name, description, trophy_image)
VALUES (3, 'Billionaire', 'Earned First Billion Credits', null);
INSERT INTO achievement_rule (id, name, description, trophy_image)
VALUES (4, 'Uncle Scrooge', 'Earned 3 Billion Credits', null);
INSERT INTO achievement_rule (id, name, description, trophy_image)
VALUES (5, 'First Murder', 'Committed a First Murder', null);
INSERT INTO achievement_rule (id, name, description, trophy_image)
VALUES (6, 'Exterminator Apprentince', 'Killed >50 Thargoids', null);
INSERT INTO achievement_rule (id, name, description, trophy_image)
VALUES (7, 'Exterminator Master', 'Killed >100 Thargoids', null);
INSERT INTO achievement_rule (id, name, description, trophy_image)
VALUES (8, 'Exterminator Pro', 'Killed > 200 Thargoids', null);

TRUNCATE TABLE achievement_condition;
INSERT INTO achievement_condition (id, achievement_rule_id, variable, operator, condition_value, data_type)
VALUES (1, 1, 'thargoid_count_bounty', '>=', '25', 'I');
INSERT INTO achievement_condition (id, achievement_rule_id, variable, operator, condition_value, data_type)
VALUES (2, 2, 'thargoid_count_bounty', '>=', '1', 'I');
INSERT INTO achievement_condition (id, achievement_rule_id, variable, operator, condition_value, data_type)
VALUES (3, 3, 'credits', '>=', '1000000000', 'I');
INSERT INTO achievement_condition (id, achievement_rule_id, variable, operator, condition_value, data_type)
VALUES (5, 4, 'credits', '>=', '3000000000', 'I');
INSERT INTO achievement_condition (id, achievement_rule_id, variable, operator, condition_value, data_type)
VALUES (6, 5, 'crime_count_murder', '>=', '1', 'I');
INSERT INTO achievement_condition (id, achievement_rule_id, variable, operator, condition_value, data_type)
VALUES (7, 6, 'thargoid_count_bounty', '>=', '50', 'I');
INSERT INTO achievement_condition (id, achievement_rule_id, variable, operator, condition_value, data_type)
VALUES (8, 7, 'thargoid_count_bounty', '>=', '100', 'I');
INSERT INTO achievement_condition (id, achievement_rule_id, variable, operator, condition_value, data_type)
VALUES (9, 8, 'thargoid_count_bounty', '>=', '200', 'I');

TRUNCATE TABLE thargoid_variant;
INSERT INTO thargoid_variant (id, name, reward)
VALUES (1, 'Scout', 10000);
INSERT INTO thargoid_variant (id, name, reward)
VALUES (2, 'Cyclop', 2000000);
INSERT INTO thargoid_variant (id, name, reward)
VALUES (3, 'Baslisk', 6000000);
INSERT INTO thargoid_variant (id, name, reward)
VALUES (4, 'Medusa', 10000000);
INSERT INTO thargoid_variant (id, name, reward)
VALUES (5, 'Hydra', 15000000);
INSERT INTO thargoid_variant (id, name, reward)
VALUES (6, 'Orthrus', 0);

SET FOREIGN_KEY_CHECKS = 1;

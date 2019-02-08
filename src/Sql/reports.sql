SELECT
   `earning_history`.`squadron_id` AS `squadron_id`,sum(`earning_history`.`reward`) AS `total_earned`,
   `earning_history`.`earned_on` AS `earned_on`
FROM `earning_history` group by `earning_history`.`squadron_id`,`earning_history`.`earned_on`;

create view v_commander_daily_earning as select user_id, squadron_id, sum(reward) as total_earned, earned_on from earning_history group by user_id, earned_on;

set sql_mode='';

create view v_commander_total_earning as select user_id, squadron_id, sum(reward) as total_earned from earning_history group by user_id;

delimiter $$
create procedure p_commander_earning_rank(in squadronId INT)
begin
select user_id, b.squadron_id, total_earned, 1+(select count(*) from v_commander_total_earning a where a.total_earned > b.total_earned and a.squadron_id=squadronId) as rank from v_commander_total_earning b left join user u on u.id=b.user_id where b.squadron_id=squadronId order by rank;
end $$
delimiter ;

select user_id, squadron_id, total_earned, find_in_set(total_earned, (select group_concat(distinct total_earned order by total_earned DESC) from v_commander_total_earning)) as rank from v_commander_total_earning;

select user_id, squadron_id, total_earned, 1+(select count(*) from v_commander_total_earning a where a.total_earned > b.total_earned and squadron_id=? as rank from v_commander_total_earning b where squadron_id=? order by `rank` asc;

create view v_commander_exploration_total as select t.*,cast(ifnull(format((t.efficiency_achieved / t.saa_scan_completed) * 100,1),0) as decimal(4,1)) as efficiency_rate from (select user_id, a.squadron_id, u.commander_name, sum(a.systems_scanned) as systems_scanned, sum(a.bodies_found) as bodies_found, sum(a.saa_scan_completed) as saa_scan_completed, sum(a.efficiency_achieved) as efficiency_achieved from activity_counter a left outer join user u on a.user_id=u.id group by user_id, squadron_id) t;

create view v_commander_market_total as select t.*, t.units_sold - t.units_bought as net_units from (select user_id, u.squadron_id, commander_name, sum(market_buy) as units_bought, sum(market_sell) as units_sold from activity_counter a left outer join user u on a.user_id = u.id group by user_id) t;

create or replace view v_commander_market_net_units as select t.*, t.units_sold - t.units_bought as net_units from (select user_id, u.squadron_id, sum(market_buy) as units_bought, sum(market_sell) as units_sold from activity_counter a left outer join user u on a.user_id = u.id group by user_id) t;

create or replace view v_commander_market_net_earning as select u.id, ifnull(t1.squadron_id,ifnull(t2.squadron_id,u.squadron_id)) as squadron_id, ifnull(t1.market_buy,0) as market_buy, ifnull(t2.market_sell,0) as market_sell, ifnull(t1.market_buy,0) + ifnull(t2.market_sell,0) as total from (select e.user_id, e.squadron_id, sum(reward) as market_buy from earning_history e where earning_type_id='5' group by e.user_id, e.squadron_id) t1 left join (select e.user_id, e.squadron_id, sum(reward) as market_sell from earning_history e where earning_type_id='6' group by e.user_id, e.squadron_id) t2 on t1.user_id = t2.user_id and t1.squadron_id = t2.squadron_id right outer join user u on t1.user_id = u.id;

select u.commander_name, v1.*, format(v2.market_buy,0) as market_buy, format(v2.market_sell,0) as market_sell, format(v2.total,0) as total, cast(ifnull(format((v2.total/v1.units_sold),0),0) as decimal(10,2)) as cr_per_unit from v_commander_market_net_units v1 left join v_commander_market_net_earning v2 on v1.user_id = v2.id and v1.squadron_id=v2.squadron_id right outer join user u on v1.user_id=u.id;
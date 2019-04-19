SET FOREIGN_KEY_CHECKS = 0;
insert into squadron_tags(squadron_id, tag_id) (select s.id, t.id
                                                from squadron s
                                                       left join platform p on s.platform_id = p.id
                                                       left join tags t on p.name = t.name);
SET FOREIGN_KEY_CHECKS = 1;
BEGIN

DECLARE totalcnt int default 0;
DECLARE i int default 0;

DECLARE done boolean default 0;
DECLARE c_uid_1    int default 0;
DECLARE c_uid_2    int default 0;
DECLARE c_uid_3    int default 0;
DECLARE c_parent_1 int default 0;
DECLARE c_parent_2 int default 0;
DECLARE c_parent_3 int default 0;

DECLARE cur_users CURSOR FOR SELECT `user_id`,`parent_id` FROM `shp_users` WHERE 1;
DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

SELECT COUNT(1) INTO totalcnt FROM `shp_users` WHERE 1;

OPEN cur_users;

mloop: LOOP

  FETCH cur_users INTO c_uid_1, c_parent_1;
  IF done THEN
    LEAVE mloop;
  END IF;
  
  SET i = i + 1;
  IF i%1000=0 OR i=totalcnt THEN
    SELECT i, totalcnt;
  END IF;
  
  IF c_parent_1>0 THEN
  
    IF c_parent_1=c_uid_1 THEN
      UPDATE `shp_users` SET `parent_id`=0,`parent_nick`='',`parent_id2`=0,`parent_id3`=0 WHERE `user_id`=c_uid_1;
      ITERATE mloop;
    END IF;
    
    SELECT `user_id`,`parent_id` INTO c_uid_2,c_parent_2 FROM `shp_users` WHERE `user_id`=c_parent_1;
    IF c_parent_2>0 THEN
      IF c_uid_2=c_parent_2 THEN
        ITERATE mloop;
      END IF;
      
      SELECT `user_id`,`parent_id` INTO c_uid_3,c_parent_3 FROM `shp_users` WHERE `user_id`=c_parent_2;
      IF c_uid_3=c_parent_3 THEN
        SET c_parent_3 = 0;
      END IF;
      
      UPDATE `shp_users` SET `parent_id2`=c_parent_2,`parent_id3`=c_parent_3 WHERE `user_id`=c_uid_1;
    END IF;
    
  END IF;
  
END LOOP mloop;

CLOSE cur_users;
SELECT i;
END
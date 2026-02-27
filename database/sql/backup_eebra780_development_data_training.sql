SET SQL_SAFE_UPDATES = 0;

UPDATE `eebra780_development`.`trainings`
SET `status` = 10
WHERE `status` = 1;

UPDATE `eebra780_development`.`trainings`
SET `status` = 20
WHERE `status` = 2;

UPDATE `eebra780_development`.`trainings`
SET `status` = 30
WHERE `status` = 3;

UPDATE `eebra780_development`.`trainings`
SET `status` = 40
WHERE `status` = 4;

UPDATE `eebra780_development`.`trainings`
SET `status` = 0
WHERE `status` = 10;

UPDATE `eebra780_development`.`trainings`
SET `status` = 1
WHERE `status` = 20;

UPDATE `eebra780_development`.`trainings`
SET `status` = 3
WHERE `status` = 30;

UPDATE `eebra780_development`.`trainings`
SET `status` = 2
WHERE `status` = 40;

SET SQL_SAFE_UPDATES = 1;
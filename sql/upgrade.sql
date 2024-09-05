DELIMITER $$
DROP PROCEDURE if exists AddColumnIfNotExists;
$$

CREATE PROCEDURE AddColumnIfNotExists()
BEGIN
    DECLARE column_exists INT;

    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'aadhar';

    IF column_exists = 0 THEN
        ALTER TABLE users 
        ADD COLUMN  `aadhar` varchar(155) DEFAULT NULL;
    END IF;

    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'business_name';

    IF column_exists = 0 THEN
        ALTER TABLE users 
        ADD COLUMN  `business_name` varchar(155) DEFAULT NULL;
    END IF;

    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'business_address';

    IF column_exists = 0 THEN
        ALTER TABLE users
        ADD COLUMN  `business_address` text DEFAULT NULL;
    END IF;
END$$

DELIMITER $$

call AddColumnIfNotExists();
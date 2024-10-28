CREATE TABLE IF NOT EXISTS `#__acyc_configuration` (
	`name`  VARCHAR(255) NOT NULL,
	`value` TEXT         NOT NULL,
	PRIMARY KEY (`name`)
)
	ENGINE = InnoDB
	/*!40100
	DEFAULT CHARACTER SET utf8
	COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acyc_test` (
	`email`         VARCHAR(255) NOT NULL,
	`date`          DATETIME     NOT NULL,
	`raw_result`    TEXT         NUll,
	`test_result`   VARCHAR(50)  NOT NULL,
	`disposable`    INT          NULL,
	`free`          INT          NULL,
	`accept_all`    INT          NULL,
	`role_email`    INT          NULL,
	`current_step`  INT          NULL,
	`block_reason`  VARCHAR(50)  NULL,
	`batch_id`      INT          NULL,
	`domain_exists` INT          NULL,
	PRIMARY KEY (`email`)
)
	ENGINE = InnoDB
	/*!40100
	DEFAULT CHARACTER SET utf8
	COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acyc_block_history` (
	`email`         VARCHAR(255) NOT NULL,
	`block_date`    DATETIME     NOT NULL,
	`block_reason`  VARCHAR(50)  NOT NULL,
	`block_action`  VARCHAR(50)  NOT NULL,
	PRIMARY KEY (`email`)
)
	ENGINE = InnoDB
	/*!40100
	DEFAULT CHARACTER SET utf8
	COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acyc_delete_history` (
	`email`         VARCHAR(255) NOT NULL,
	`delete_date`    DATETIME     NOT NULL,
	`delete_reason`  VARCHAR(50)  NOT NULL,
	`delete_action`  VARCHAR(50)  NOT NULL,
	PRIMARY KEY (`email`)
)
	ENGINE = InnoDB
	/*!40100
	DEFAULT CHARACTER SET utf8
	COLLATE utf8_general_ci*/;

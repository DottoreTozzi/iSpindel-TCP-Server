CREATE TABLE `Data` (
	`Timestamp` datetime NOT NULL,
	`Name` varchar(64) COLLATE ascii_bin NOT NULL,
	`ID` INT UNSIGNED NOT NULL,
	`Angle` double NOT NULL,
	`Temperature` double NOT NULL,
	`Battery` double NOT NULL,
	`ResetFlag` boolean,
	`Gravity` double NOT NULL DEFAULT 0,
	`UserToken` varchar(64) COLLATE ascii_bin,
	`Interval` int,
	`RSSI` int,
	`Recipe` varchar(64),
	PRIMARY KEY (`Timestamp`, `Name`, `ID`)
	) 
ENGINE=InnoDB DEFAULT CHARSET=ascii 
COLLATE=ascii_bin COMMENT='iSpindle Data';

CREATE TABLE `Calibration` (
    `ID` INT UNSIGNED NOT NULL,
    `const1` double NOT NULL,
    `const2` double NOT NULL,
    `const3` double NOT NULL,
    PRIMARY KEY (`ID`)
) 
ENGINE=InnoDB DEFAULT CHARSET=ascii 
COLLATE=ascii_bin COMMENT='iSpindle Calibration Data';

CREATE TABLE `Config` (
	`ID` int NOT NULL,
	`Interval` int NOT NULL,
	`Token` varchar(64) NOT NULL,
	`Polynomial` varchar(64) NOT NULL,
        `Sent` boolean NOT NULL,
	PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin COMMENT='iSpindle Config Data';


ALTER TABLE `Settings` ADD COLUMN `DeviceName` varchar(64);
ALTER TABLE `Settings` DROP PRIMARY KEY, ADD PRIMARY KEY(`Section`, `Parameter`, `value`, `DeviceName`);
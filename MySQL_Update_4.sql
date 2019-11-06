ALTER TABLE `Settings`
ADD COLUMN `DeviceName` varchar(64),
DROP PRIMARY KEY, ADD PRIMARY KEY(`Section`, `Parameter`, `value`, `DeviceName`);

INSERT IGNORE INTO `Settings` (`Section`, `Parameter`, `value`, `Description_DE`, `Description_EN`, `DeviceName`) VALUES
('INFLUXDB', 'INFLUXDBADDR', 'localhost', 'IP-Adresse/Name des InfluxDB-Servers', 'IP address/hostname of the InfluxDB Server', '_DEFAULT'),
('INFLUXDB', 'INFLUXDBPORT', '8086', 'Port des InfluxDB-Servers', 'Port of InfluxDB Server', '_DEFAULT'),
('INFLUXDB', 'INFLUXDBNAME', 'spindeldaten', 'Name der Datenbank innerhalb von InfluxDB', 'Name of the database inside InfluxDB', '_DEFAULT'),
('INFLUXDB', 'INFLUXDBUSERNAME', 'username', 'Userame für InfluxDB', 'user name for InfluxDB', '_DEFAULT'),
('INFLUXDB', 'INFLUXDBPASSWD', 'secret', 'Passwort de InfluxDB-Users', 'apssword of the InfluxDB user', '_DEFAULT'),
('INFLUXDB', 'ENABLE_INFLUXDB', '0', 'Weiterleitung an InfluxDB', 'Forward to InfluxDB', '_DEFAULT');
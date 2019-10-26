INSERT IGNORE INTO `Settings` (`Section`, `Parameter`, `value`, `Description_DE`, `Description_EN`) VALUES
('BREWFATHER', 'BREWFATHERADDR', 'log.brewfather.net', 'IP Adresse des Brewfather Servers', 'IP Address of the Brewfather Server'),
('BREWFATHER', 'BREWFATHERPORT', '80', 'Port des Brewfather Servers', 'Port of Brewfather Server'),
('BREWFATHER', 'BREWFATHERSUFFIX', '[SG]', 'iSpindel polynom in ... ([SG] = relative Dichte, [PL] = plato)  ', 'iSpindle polynom set for ... ([SG] = specific gravity, [PL] = plato)'),
('BREWFATHER', 'BREWFATHER_TOKEN', 'mytoken', 'Token für Brewfather Server', 'Token for Brewfather Server'),
('BREWSPY', 'BREWSPYADDR', 'brew-spy.com', 'IP Adresse des BrewSpy Servers', 'IP Address of the BrewSpy Server'),
('BREWSPY', 'BREWSPYPORT', '80', 'Port des BrewSpy Servers', 'Port of BrewSpy Server'),
('BREWSPY', 'BREWSPY_TOKEN', 'mytoken', 'Token für BrewSpy Server', 'Token for BrewSpy Server'),
('BREWFATHER', 'ENABLE_BREWFATHER', '0', 'Weiterleitung an Brewfather', 'Forward to Brewfather'),
('BREWSPY', 'ENABLE_BREWSPY', '0', 'Weiterleitung an BrewSpy', 'Forward to BrewSpy'),
('BREWFATHER', 'FAT_USE_ISPINDLE_TOKEN', '0', 'Verwendung des ISpindle Tokens zur Weiterleitung', 'Use token from iSpindle for data forwarding'),
('BREWSPY', 'SPY_USE_ISPINDLE_TOKEN', '0', 'Verwendung des ISpindle Tokens zur Weiterleitung', 'Use token from iSpindle for data forwarding');

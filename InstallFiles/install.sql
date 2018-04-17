-- cPanelWHMFramework --
-- PLEASE DO NOT REMOVE THIS SQL COMMANDS --
-- REQUIRED FOR FRAMEWORK INTERNAL PROCESSES --

CREATE TABLE IF NOT EXISTS `Halon_mxRecords` (
  `domain` varchar(255) NOT NULL,
  `mx_records` text NULL,  
  PRIMARY KEY (domain)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `Halon_configuration` (
  `name` varchar(255) NOT NULL,
  `value` text NULL,  
  PRIMARY KEY (name)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
-- cPanelWHMFramework --

-- PLEASE INSERT BELOW YOUR CUSTOM SQL COMMANDS --

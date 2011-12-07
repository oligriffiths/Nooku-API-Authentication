CREATE TABLE `jos_api_apis` (
  `api_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `key` varchar(32) NOT NULL DEFAULT '',
  `secret` varchar(32) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `browse` tinyint(1) DEFAULT '0',
  `read` tinyint(1) DEFAULT '0',
  `edit` tinyint(1) DEFAULT '0',
  `add` tinyint(1) DEFAULT '0',
  `delete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`api_id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/** 1.7 Plugin **/
INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`)
VALUES
	(0, 'System - Nooku API', 'plugin', 'koowa_api', 'system', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 2, 0);
	
	
/*** 1.5 Plugin **/
INSERT INTO `jos_plugins` (`id`, `name`, `element`, `folder`, `access`, `ordering`, `published`, `iscore`, `client_id`, `checked_out`, `checked_out_time`, `params`)
VALUES
	(0, 'System - Nooku API', 'nooku_api', 'system', 0, 0, 1, 0, 0, 0, '0000-00-00 00:00:00', '');



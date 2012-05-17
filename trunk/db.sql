CREATE TABLE `jos_tokens` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `key` varchar(32) NOT NULL DEFAULT '',
  `secret` varchar(32) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `ip_whitelist` text,
  `ip_blacklist` text,
  `last_request` datetime DEFAULT NULL,
  `requests_in_last_hour` int(11) DEFAULT '0',
  `requests_total` int(11) DEFAULT '0',
  `requests_max` int(11) DEFAULT '0',
  `browse` tinyint(1) DEFAULT '0',
  `read` tinyint(1) DEFAULT '0',
  `edit` tinyint(1) DEFAULT '0',
  `add` tinyint(1) DEFAULT '0',
  `delete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/** 2.5 Install **/
INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`)
VALUES
	(0, 'System - Nooku API', 'plugin', 'koowa_tokens', 'system', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 2, 0);
	
	
/*** 1.5 Install **/
INSERT INTO `jos_components` (`id`, `name`, `link`, `menuid`, `parent`, `admin_menu_link`, `admin_menu_alt`, `option`, `ordering`, `admin_menu_img`, `iscore`, `params`, `enabled`)
VALUES
	(0, 'API Tokens', 'option=com_tokens', 0, 0, 'option=com_tokens', 'tokens', 'com_tokens', 0, '', 0, '', 1);
INSERT INTO `jos_plugins` (`id`, `name`, `element`, `folder`, `access`, `ordering`, `published`, `iscore`, `client_id`, `checked_out`, `checked_out_time`, `params`)
VALUES
	(0, 'System - Nooku API', 'koowa_tokens', 'system', 0, 0, 1, 0, 0, 0, '0000-00-00 00:00:00', '');

<?php
/**
 * @version		$Id: select.php 2725 2010-10-28 01:54:08Z johanjanssens $
 * @category	Koowa
 * @package		Koowa_Template
 * @subpackage	Helper
 * @copyright	Copyright (C) 2007 - 2010 Johan Janssens. All rights reserved.
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link     	http://www.nooku.org
 */

/**
 * JHTML Helper
 *
 * @author		Oli Griffiths <organic-development.com>
 * @category	com_api
 * @package		Template
 * @subpackage	Helper
 */
class ComApiTemplateHelperHtml extends KTemplateHelperDefault
{
	
	public function link($config = array()){
		$config = new KConfig($config);
		$config->append(array(
			'url' 	=> null,
			'text'   	=> ''			
		))->append(array(
			'attribs'	=> array('title' => $config->text)
		));
		
		return JHTML::link($config->url, $config->text, KConfig::unbox($config->attribs));
	}
	
}
<?php
/**
 * @package		Skillset
 * @subpackage	Skillset
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2014 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later
 * @version		1.0.0
 */

// no direct access
defined('_JEXEC') or die;

// include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';


$class_sfx = htmlspecialchars($params->get('class_sfx'));
$display = $params->get('display');

if($params->get('css-load')) {
	JHtml::stylesheet(Juri::base() . 'modules/mod_skillset/css/skillset.css');
}

if($params->get('jquery-load')) {
	JLoader::import( 'joomla.version' );
	$version = new JVersion();
	if (version_compare( $version->RELEASE, '2.5', '<=')) {
	    if(JFactory::getApplication()->get('jquery') !== true) {
	        // load jQuery here
	        JFactory::getApplication()->set('jquery', true);
	    }
	} else {
	    JHtml::_('jquery.framework');
	}
}

if ($display == "count") {
	require(JModuleHelper::getLayoutPath('mod_skillset', $params->get('layout', 'count')));
} elseif ($display == "circular") {
	require(JModuleHelper::getLayoutPath('mod_skillset', $params->get('layout', 'circular')));
} else {
	require(JModuleHelper::getLayoutPath('mod_skillset', $params->get('layout', 'default')));
}
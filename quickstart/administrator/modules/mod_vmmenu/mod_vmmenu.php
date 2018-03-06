<?php
/**
 *
 * @version $Id$
 * @package VirtueMart
 * @author Valérie Isaksen
 * @subpackage mod_vmmenu
 * @copyright Copyright (C) 2014 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

// no direct access
defined('_JEXEC') or die;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists( 'VmConfig' )) {
	$path = JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php';
	if(file_exists($path)){
		require($path);
	} else {
		$app = JFactory::getApplication();
		$app->enqueueMessage('VirtueMart Administration module is still installed, please install VirtueMart again, or uninstall the module via the joomla extension manager');
		return false;
	}
}

$user		= JFactory::getUser();
if ($user->guest) return;

// Include the module helper classes.
if (!class_exists('ModVMMenuHelper')) {
	require dirname(__FILE__).'/helper.php';
}

// Get the authorised components and sub-menus.
$vmComponentItems = ModVMMenuHelper::getVMComponent(true);

// Initialise variables.
$lang		= JFactory::getLanguage();

$hideMainmenu	= vRequest::getInt('hidemainmenu')  ;

// Render the module layout
require JModuleHelper::getLayoutPath('mod_vmmenu', $params->get('layout', 'default'));

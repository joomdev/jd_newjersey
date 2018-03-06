<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 9633 2017-09-07 08:16:00Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
AdminUIHelper::startAdminArea($this);

echo '<div id="cpanel">';

$tabs = array (	'tools' 	=> 	'COM_VIRTUEMART_UPDATE_TOOLS_TAB',
'spwizard' 	=> 	'COM_VM_WIZARD_TAB',
'migrator' 	=> 	'COM_VIRTUEMART_MIGRATION_TAB');

if(vRequest::getBool('show_spwizard',false)){
	unset($tabs['spwizard']);
	$tabs = array_merge(array('spwizard' 	=> 	'COM_VM_WIZARD_TAB'), $tabs);
}
AdminUIHelper::buildTabs ( $this,  $tabs);

AdminUIHelper::endAdminArea();

echo '</div>';

$j = 'function confirmation(message, destnUrl) {
	var answer = confirm(message);
	if (answer) {
		window.location = destnUrl;
	}
}';

vmJsApi::addJScript('vm.confirm',$j);
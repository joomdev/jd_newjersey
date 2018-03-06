<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: virtuemart.php 9666 2017-11-15 11:02:04Z Milbo $
* @package VirtueMart
* @subpackage core
* @author Max Milbers
* @copyright Copyright (C) 2009-14 by the authors of the VirtueMart Team listed at /administrator/com_virtuemart/copyright.php - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

/* Require the config */

if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

VmConfig::loadConfig();

vmRam('Start');
//vmTime('joomla start until Vm is called','joomlaStart');
vmSetStartTime('vmStart');

vmLanguage::loadJLang('com_virtuemart', true);

$_controller = vRequest::getCmd('view', vRequest::getCmd('controller', 'virtuemart')) ;
$task = vRequest::getCmd('task','') ;

if(VmConfig::get('shop_is_offline',0) and $task!='feed' and $_controller!='virtuemart'){	//yes, quickndirty
	$_controller = 'virtuemart';
	require (VMPATH_SITE.DS.'controllers'.DS.'virtuemart.php');
	vRequest::setVar('view', 'virtuemart');
	$task='';
	$basePath = VMPATH_SITE;
} else {

	// Front-end helpers
	if(!class_exists('VmImage')) require(VMPATH_ADMIN.DS.'helpers'.DS.'image.php'); //dont remove that file it is actually in every view except the state view
	if(!class_exists('shopFunctionsF'))require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php'); //dont remove that file it is actually in every view


	$trigger = 'onVmSiteController';
// 	$task = vRequest::getCmd('task',vRequest::getCmd('layout',$_controller) );		$this makes trouble!


	$session = JFactory::getSession();
	$manage = vRequest::getCmd('manage',$session->get('manage', false,'vm'));
	if(!$manage) $session->set('manage', 0,'vm');

	$feViews = array('askquestion','cart','invoice','pdf','pluginresponse','productdetails','recommend','vendor','vmplg');
	$app = JFactory::getApplication();
	if($manage and $task!='feed' and !in_array($_controller,$feViews)){

		if	( shopFunctionsF::isFEmanager() ) {
			$session->set('manage', 1,'vm');
			vRequest::setVar('manage','1');
			vRequest::setVar('tmpl','component') ;

			vmLanguage::loadJLang('com_virtuemart');
			$jlang = JFactory::getLanguage();
			$tag = $jlang->getTag();
			$jlang->load('', JPATH_ADMINISTRATOR,$tag,true);
			vmLanguage::loadJLang('com_virtuemart');
			$basePath = VMPATH_ADMIN;
			$trigger = 'onVmAdminController';


			vmJsApi::jQuery(false);
			vmJsApi::loadBECSS();

			$router = $app->getRouter();
			$router->setMode(0);

		} else {
			$session->set('manage', 0,'vm');
			vRequest::setVar('manage',0);
			$basePath = VMPATH_SITE;
			$app->redirect('index.php?option=com_virtuemart', vmText::_('COM_VIRTUEMART_RESTRICTED_ACCESS') );
		}

	} elseif($_controller) {
			if($_controller!='productdetails'){
				//$session->set('manage', 0,'vm');
				//vRequest::setVar('manage','0');
			}
			vmJsApi::jQuery();
			vmJsApi::jSite();
			vmJsApi::cssSite();
			$basePath = VMPATH_SITE;
	}
}

// controller alias
if ($_controller=='pluginresponse') {
	$_controller='vmplg';
}
/* Create the controller name */
$_class = 'VirtuemartController'.ucfirst($_controller);

if (file_exists($basePath.DS.'controllers'.DS.$_controller.'.php')) {
	if (!class_exists($_class)) {
		require ($basePath.DS.'controllers'.DS.$_controller.'.php');
	}
}
else {
	// try plugins
	JPluginHelper::importPlugin('vmextended');
	$dispatcher = JDispatcher::getInstance();
	$rets = $dispatcher->trigger($trigger, array($_controller));

	foreach($rets as $ret){
		if($ret) return true;
	}
}


if (class_exists($_class)) {
    $controller = new $_class();

    $controller->execute($task);

    //vmTime($_class.' Finished task '.$task,'Start');
    vmRam('End');
    vmRamPeak('Peak');
	vmTime('"'.$_class.'" Finished task ','vmStart');
    /* Redirect if set by the controller */
    $controller->redirect();
} else {
    vmDebug('VirtueMart controller not found: '. $_class);
    if (VmConfig::get('handle_404',1)) {
    	$mainframe = JFactory::getApplication();
    	$mainframe->redirect(JRoute::_ ('index.php?option=com_virtuemart&view=virtuemart', FALSE));
    } else {
    	JError::raise(E_ERROR,'404','Not found');
    }
}

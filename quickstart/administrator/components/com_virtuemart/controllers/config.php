<?php
/**
*
* Config controller
*
* @package	VirtueMart
* @subpackage Config
* @auhtor Max Milbers
* @author RickG
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2014 VirtueMart Team and authors. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: config.php 9627 2017-08-22 16:56:14Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmController'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Configuration Controller
 *
 * @package    VirtueMart
 * @subpackage Config
 */
class VirtuemartControllerConfig extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		vmLanguage::loadJLang('com_virtuemart_config');
		vmLanguage::loadJLang('com_virtuemart.sys');
		parent::__construct();

	}


	/**
	 * Handle the save task
	 */
	function save($data = 0){

		vRequest::vmCheckToken();
		$model = VmModel::getModel('config');

		$data = vRequest::getPost();

		if(strpos($data['offline_message'],'|')!==false){
			$data['offline_message'] = str_replace('|','',$data['offline_message']);
		}

		$msg = '';
		if ($model->store($data)) {
			$msg = vmText::_('COM_VIRTUEMART_CONFIG_SAVED');
			// Load the newly saved values into the session.
			VmConfig::loadConfig();
		}

		$redir = 'index.php?option=com_virtuemart';
		if(vRequest::getCmd('task') == 'apply'){
			$redir = $this->redirectPath;
		}

		$this->setRedirect($redir.'&vmms=1&nosafepathcheck=1', $msg);


	}


	/**
	 * Overwrite the remove task
	 * Removing config is forbidden.
	 * @author Max Milbers
	 */
	function remove(){

		$msg = vmText::_('COM_VIRTUEMART_ERROR_CONFIGS_COULD_NOT_BE_DELETED');

		$this->setRedirect( $this->redirectPath , $msg);
	}
}

//pure php no tag

<?php
/**
*
* Userfields controller
*
* @package	VirtueMart
* @subpackage Userfields
* @author Oscar van Eijk
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: userfields.php 9478 2017-03-16 09:33:17Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmController'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Controller class for the Order status
 *
 * @package    VirtueMart
 * @subpackage Userfields
 * @author     Oscar van Eijk
 */
class VirtuemartControllerUserfields extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access public
	 * @author
	 */
	function __construct(){
		parent::__construct('virtuemart_userfield_id');

	}

	function viewJson() {

		// Create the view object.
		$view = $this->getView('userfields', 'json');

		// Now display the view.
		$view->display(null);
	}

	function save($data = 0) {

		if($data===0) $data = vRequest::getPost();

		if(vmAccess::manager('raw')){
			$data['description'] = vRequest::get('description','');
			if(isset($data['params'])){
				$data['params'] = vRequest::get('params','');
			}
		} else {
			$data['description'] = vRequest::getHtml('description','');
			if(isset($data['params'])){
				$data['params'] = vRequest::getHtml('params','');
			}
		}
		$data['name'] = vRequest::getCmd('name');
		// onSaveCustom plugin;
		parent::save($data);
	}



}

//No Closing tag

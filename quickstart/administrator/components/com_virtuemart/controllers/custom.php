<?php
/**
*
* custom controller
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: custom.php 3039 2011-04-14 22:37:04Z Electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmController'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Product Controller
 *
 * @package    VirtueMart
 * @author Max Milbers
 */
class VirtuemartControllerCustom extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct('virtuemart_custom_id');

	}


	function viewJson() {

		// Create the view object.
		$view = $this->getView('custom', 'json');

		// Now display the view.
		$view->display(null);
	}

	function save($data = 0) {

		if($data===0)$data = vRequest::getPost();
		$data['custom_desc'] = vRequest::getHtml('custom_desc');
		$data['custom_value'] = vRequest::getHtml('custom_value');
		$data['layout_pos'] = vRequest::getCmd('layout_pos');
		if(isset($data['params'])){
			$data['params'] = vRequest::getHtml('params','');
		}
		// onSaveCustom plugin;
		parent::save($data);
	}

	/**
	* Clone a product
	*
	* @author Max Milbers
	*/
	public function createClone() {

		$app = Jfactory::getApplication();
		$model = VmModel::getModel('custom');
		$msgtype = '';
		$cids = vRequest::getInt($this->_cidName, vRequest::getInt('virtuemart_custom_id'));

		foreach ($cids as $custom_id) {
			if ($model->createClone($custom_id)) $msg = vmText::_('COM_VIRTUEMART_CUSTOM_CLONED_SUCCESSFULLY');
			else {
				$msg = vmText::_('COM_VIRTUEMART_CUSTOM_NOT_CLONED_SUCCESSFULLY').' : '.$custom_id;
				$msgtype = 'error';
			}
		}
		$app->redirect('index.php?option=com_virtuemart&view=custom', $msg, $msgtype);
	}
}
// pure php no closing tag

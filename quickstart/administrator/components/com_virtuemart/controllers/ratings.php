<?php
/**
*
* Review controller
*
* @package	VirtueMart
* @subpackage
* @author Max Milberes
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: ratings.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if (!class_exists ('VmController')){
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmcontroller.php');
}


/**
 * Review Controller
 *
 * @package    VirtueMart
 */
class VirtuemartControllerRatings extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * we must overwrite it here, because the task publish can be meant for two different list layouts.
	 */
	function publish($cidname=0,$table=0,$redirect = 0){

		vRequest::vmCheckToken();
		$layout = vRequest::getString('layout','default');

		if($layout=='list_reviews'){

			$virtuemart_product_id = vRequest::getInt('virtuemart_product_id');
			if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
				$virtuemart_product_id = (int)$virtuemart_product_id[0];
			} else {
				$virtuemart_product_id = (int)$virtuemart_product_id;
			}
			$redPath = '';
			if (!empty($virtuemart_product_id)) {
				$redPath = '&task=listreviews&virtuemart_product_id=' . $virtuemart_product_id;
			}

			parent::publish('virtuemart_rating_review_id','rating_reviews',$this->redirectPath.$redPath);
		} else {
			parent::publish();
		}

	}

	function unpublish($cidname=0,$table=0,$redirect = 0){

		vRequest::vmCheckToken();
		$layout = vRequest::getString('layout','default');

		if($layout=='list_reviews'){

			$virtuemart_product_id = vRequest::getInt('virtuemart_product_id');
			if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
				$virtuemart_product_id = (int)$virtuemart_product_id[0];
			} else {
				$virtuemart_product_id = (int)$virtuemart_product_id;
			}
			$redPath = '';
			if (!empty($virtuemart_product_id)) {
				$redPath = '&task=listreviews&virtuemart_product_id=' . $virtuemart_product_id;
			}

			parent::unpublish('virtuemart_rating_review_id','rating_reviews',$this->redirectPath.$redPath);
		} else {
			parent::unpublish();
		}

	}

	/**
	 * Save task for review
	 *
	 * @author Max Milbers
	 */
	function saveReview(){

		$this->storeReview(FALSE);
	}

	/**
	 * Save task for review
	 *
	 * @author Max Milbers
	 */
	function applyReview(){

		$this->storeReview(TRUE);
	}


	function storeReview($apply){

		vRequest::vmCheckToken();

		if (empty($data)){
			$data = vRequest::getPost();
		}

		$model = VmModel::getModel($this->_cname);
		$id = $model->saveRating($data);

		$msg = 'failed';
		if (!empty($id)) {
			$msg = vmText::sprintf ('COM_VIRTUEMART_STRING_SAVED', $this->mainLangKey);
		}

		$redir = $this->redirectPath;
		if($apply){
			$redir = 'index.php?option=com_virtuemart&view=ratings&task=edit_review&virtuemart_rating_review_id='.$id;
		} else {
			$virtuemart_product_id = vRequest::getInt('virtuemart_product_id');
			if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
				$virtuemart_product_id = (int)$virtuemart_product_id[0];
			} else {
				$virtuemart_product_id = (int)$virtuemart_product_id;
			}
			$redir = 'index.php?option=com_virtuemart&view=ratings&task=listreviews&virtuemart_product_id='.$virtuemart_product_id;
		}

		$this->setRedirect($redir, $msg);
	}
	/**
	 * Save task for review
	 *
	 * @author Max Milbers
	 */
	function cancelEditReview(){

		$virtuemart_product_id = vRequest::getInt('virtuemart_product_id');
		if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
			$virtuemart_product_id = (int)$virtuemart_product_id[0];
		} else {
			$virtuemart_product_id = (int)$virtuemart_product_id;
		}
		$msg = vmText::sprintf('COM_VIRTUEMART_STRING_CANCELLED',$this->mainLangKey); //'COM_VIRTUEMART_OPERATION_CANCELED'
		$this->setRedirect('index.php?option=com_virtuemart&view=ratings&task=listreviews&virtuemart_product_id='.$virtuemart_product_id, $msg);
	}

}
// pure php no closing tag

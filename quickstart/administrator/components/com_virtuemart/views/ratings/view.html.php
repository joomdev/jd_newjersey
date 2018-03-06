<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage	ratings
* @author
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 9467 2017-03-08 22:45:00Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for ratings (and customer reviews)
 *
 */
class VirtuemartViewRatings extends VmViewAdmin {
	public $max_rating;

	function display($tpl = null) {

		$mainframe = Jfactory::getApplication();
		$option = vRequest::getCmd('option');

		//Load helpers


		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		/* Get the review IDs to retrieve (input variable may be cid, cid[] or virtuemart_rating_review_id */
		$cids = vRequest::getInt('cid', vRequest::getVar('virtuemart_rating_review_id',0));
		if ($cids && !is_array($cids)) $cids = array($cids);

		// Figure out maximum rating scale (default is 5 stars)
		$this->max_rating = VmConfig::get('vm_maximum_rating_scale',5);

		$model = VmModel::getModel();
		$this->SetViewTitle('REVIEW_RATE' );


		/* Get the task */
		$task = vRequest::getCmd('task');
		$new = false;
		vmdebug('my task',$task);
		switch ($task) {
			case 'edit':
				/* Get the data
				$rating = $model->getRating($cids);
				$this->addStandardEditViewCommands();

				break;*/
			case 'listreviews':

				$this->setLayout('list_reviews');
				$this->addStandardDefaultViewLists($model);
				$virtuemart_product_id = vRequest::getInt('virtuemart_product_id');
				if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
					$virtuemart_product_id = (int)$virtuemart_product_id[0];
				} else {
					$virtuemart_product_id = (int)$virtuemart_product_id;
				}
				$this->reviewslist = $model->getReviews($virtuemart_product_id, vmAccess::getVendorId());

				$lists = array();
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

				$this->pagination = $model->getPagination();

				$this->addStandardDefaultViewCommands(true,true);
				break;

			case 'add':
				$new = true;
				$cids = vRequest::getInt('virtuemart_product_id',0);
			case 'edit_review':
				$this->setLayout('edit_review');
				JToolBarHelper::divider();

				// Get the data
				$this->rating = $model->getReview($cids,$new);
				//vmdebug('$this->rating',$this->rating);
				if(!empty($this->rating)){
					$this->SetViewTitle('REVIEW_RATE',$this->rating->product_name." (". $this->rating->customer.")" );

					JToolBarHelper::custom('saveReview', 'save', 'save',  vmText::_('COM_VIRTUEMART_SAVE'), false);
					JToolBarHelper::custom('applyReview', 'apply', 'apply',  vmText::_('COM_VIRTUEMART_APPLY'), false);

				} else {
					$this->SetViewTitle('REVIEW_RATE','ERROR' );
				}

				JToolBarHelper::custom('cancelEditReview', 'cancel', 'cancel',  vmText::_('COM_VIRTUEMART_CANCEL'), false);

				break;
			default:

				$this->addStandardDefaultViewCommands(false, true);
				$this->addStandardDefaultViewLists($model);

				$this->ratingslist = $model->getRatings();
				$this->pagination = $model->getPagination();
				vmdebug('Got default');
				break;
		}
		parent::display($tpl);
	}

}
// pure php no closing tag

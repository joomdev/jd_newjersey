<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 9420 2017-01-12 09:35:36Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');


/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewVirtuemart extends VmViewAdmin {

	function display($tpl = null) {

		if (!class_exists('VmImage'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');
		vmLanguage::loadJLang('com_virtuemart_orders',TRUE);


		if(JFactory::getApplication()->isSite()){
			$bar = JToolBar::getInstance('toolbar');
			$bar->appendButton('Link', 'back', 'COM_VIRTUEMART_LEAVE', 'index.php?option=com_virtuemart&manage=0');
		}

		$layout = $this->getLayout();

		if($this->manager('report')){
			vmSetStartTime('report');

			$model = VmModel::getModel('virtuemart');

			$nbrCustomers = $model->getTotalCustomers();
			$this->nbrCustomers=$nbrCustomers;

			$nbrActiveProducts = $model->getTotalActiveProducts();
			$this->nbrActiveProducts= $nbrActiveProducts;
			$nbrInActiveProducts = $model->getTotalInActiveProducts();
			$this->nbrInActiveProducts= $nbrInActiveProducts;
			$nbrFeaturedProducts = $model->getTotalFeaturedProducts();
			$this->nbrFeaturedProducts= $nbrFeaturedProducts;

			$ordersByStatus = $model->getTotalOrdersByStatus();
			$this->ordersByStatus= $ordersByStatus;

			$recentOrders = $model->getRecentOrders();
			if(!class_exists('CurrencyDisplay'))require(VMPATH_ADMIN.DS.'helpers'.DS.'currencydisplay.php');

			/* Apply currency This must be done per order since it's vendor specific */
			$_currencies = array(); // Save the currency data during this loop for performance reasons
			foreach ($recentOrders as $virtuemart_order_id => $order) {

				//This is really interesting for multi-X, but I avoid to support it now already, lets stay it in the code
				if (!array_key_exists('v'.$order->virtuemart_vendor_id, $_currencies)) {
					$_currencies['v'.$order->virtuemart_vendor_id] = CurrencyDisplay::getInstance('',$order->virtuemart_vendor_id);
				}
				$order->order_total = $_currencies['v'.$order->virtuemart_vendor_id]->priceDisplay($order->order_total);
			}
			$this->recentOrders= $recentOrders;
			$recentCustomers = $model->getRecentCustomers();
			$this->recentCustomers=$recentCustomers;

			$reportModel		= VmModel::getModel('report');
			vRequest::setvar('task','');
			$myCurrencyDisplay = CurrencyDisplay::getInstance();
			$revenueBasic = $reportModel->getRevenue(60,true);
			$this->report = '';
			if(!empty($revenueBasic['report'])){
				$this->report = $revenueBasic['report'];

				vmJsApi::addJScript( "jsapi","//google.com/jsapi",false,false, false, '' );
				vmJsApi::addJScript('vm.stats_chart',$revenueBasic['js'],false,true);
				vmTime('Created report','report');
			}

		}

		//if($layout=='default'){
			$j = 'jQuery("#feed").ready(function(){
				var datas = "";
				vmSiteurl = "'. JURI::root( ) .'"
				jQuery.ajax({
						type: "GET",
						async: true,
						cache: false,
						dataType: "json",
						url: vmSiteurl + "index.php?option=com_virtuemart&view=virtuemart&task=feed&tmpl=component",
						data: datas,
						dataType: "html"
					})
					.done(function( data ) {
						jQuery("#feed").append(data);
					});
				})';
			vmJsApi::addJScript('getFeed',$j, false, true);
		//}

		self::showACLPref($this);

		parent::display($tpl);
	}


}

//pure php no tag
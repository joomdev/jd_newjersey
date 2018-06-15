<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author Oscar van Eijk
 * @author Max Milbers
 * @author Patrick Kohl
 * @author Valerie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: orders.php 9802 2018-03-20 15:22:11Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel')) require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model for VirtueMart Orders
 * WHY $this->db is never used in the model ?
 * @package VirtueMart
 */
class VirtueMartModelOrders extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('orders');
		$this->addvalidOrderingFieldName(array('order_name','order_email','payment_method','shipment_method','virtuemart_order_id' ) );
		$this->populateState();
	}

	function populateState () {
		$type = 'search';
		$task = vRequest::getCmd('task','');
		$view = vRequest::getCmd('view','orders');
		$k= 'com_virtuemart.'.$view.'.'.$task.$type;

		$ts = vRequest::getString($type, false);
		$app = JFactory::getApplication();

		if($ts===false){
			$this->{$type} = $app->getUserState($k, '');
		} else {
			$app->setUserState( $k,$ts);
			$this->{$type} = $ts;
		}
		$this->__state_set = true;
	}

	/**
	 * This function gets the orderId, for anonymous users
	 * @author Max Milbers
	 */
	public function getOrderIdByOrderPass($orderNumber,$orderPass){

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `order_pass`="'.$db->escape($orderPass).'" AND `order_number`="'.$db->escape($orderNumber).'"';
		$db->setQuery($q);
		$orderId = $db->loadResult();
		if(empty($orderId)) vmdebug('getOrderIdByOrderPass no Order found $orderNumber = '.$orderNumber.' $orderPass = '.$orderPass.' $q = '.$q);
		return $orderId;

	}
	/**
	 * This function gets the orderId, for payment response
	 * author Valerie Isaksen
	 */
	public static function getOrderIdByOrderNumber($orderNumber){

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `order_number`="'.$db->escape($orderNumber).'"';
		$db->setQuery($q);
		$orderId = $db->loadResult();
		return $orderId;

	}
	/**
	 * This function seems completly broken, JRequests are not allowed in the model, sql not escaped
	 * This function gets the secured order Number, to send with paiement
	 *
	 */
	public function getOrderNumber($virtuemart_order_id){

		$db = JFactory::getDBO();
		$q = 'SELECT `order_number` FROM `#__virtuemart_orders` WHERE virtuemart_order_id="'.(int)$virtuemart_order_id.'"  ';
		$db->setQuery($q);
		$OrderNumber = $db->loadResult();
		return $OrderNumber;

	}

	/**
	 * Was also broken, actually used?
	 *
	 * get next/previous order id
	 *
	 */

	public function getOrderId($order_id, $direction ='DESC') {

		if ($direction == 'ASC') {
			$arrow ='>';
		} else {
			$arrow ='<';
		}

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `virtuemart_order_id`'.$arrow.(int)$order_id;
		$q.= ' ORDER BY `virtuemart_order_id` '.$direction ;
		$db->setQuery($q);

		if ($oderId = $db->loadResult()) {
			return $oderId ;
		}
		return 0 ;
	}

	/**
	 * This is a proxy function to return an order safely, we may set the getOrder function to private
	 * Maybe the right place would be the controller, cause there are JRequests in it. But for a fast solution,
	 * still better than to have it 3-4 times in the view.html.php of the views.
	 * @author Max Milbers
	 *
	 * @return array
	 */
	public function getMyOrderDetails($orderID = 0, $orderNumber = false, $orderPass = false, $userlang=false){

		if(VmConfig::get('ordertracking','guests') == 'none' and !vmAccess::manager('orders')){
			return false;
		}

		$virtuemart_order_id = vRequest::getInt('virtuemart_order_id',$orderID) ;
		$orderNumber = vRequest::getString('order_number',$orderNumber);

		$sess = JFactory::getSession();
		if(empty($orderNumber)) $h = $virtuemart_order_id; else $h = $orderNumber;
		$tries = $sess->get('getOrderDetails.'.$h,0);
		if($tries>6){
			vmDebug ('Too many tries, Invalid order_number/password '.vmText::_('COM_VIRTUEMART_RESTRICTED_ACCESS'));
			vmError ('Too many tries, Invalid order_number/password guest '.$orderNumber.' '.$orderPass , 'COM_VIRTUEMART_RESTRICTED_ACCESS');
			return false;
		} else {
			$tries++;
			$sess->set('getOrderDetails.'.$h,$tries);
		}

		$_currentUser = JFactory::getUser();
		$cuid = $_currentUser->get('id');

		//Extra check, when a user is logged in, else we use the guest method
		if(!empty($cuid)){
			if (!$virtuemart_order_id and $orderNumber) {
				$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($orderNumber);
			}
			if(!empty($virtuemart_order_id)){
				$orderDetails = $this->getOrder($virtuemart_order_id);
				if($orderDetails['details']['BT']->virtuemart_user_id == $cuid or vmAccess::manager('orders')) {
					$sess->set('getOrderDetails.'.$h,0);
					return $orderDetails;
				}
			}
		} else if(VmConfig::get('ordertracking','guests') == 'registered' and empty($cuid)){
			return true;
		}

		if( (VmConfig::get('ordertracking','guests') == 'guestlink' or VmConfig::get('ordertracking','guests') == 'guests') and !empty( $orderNumber )){
			$orderPass = vRequest::getString( 'order_pass', $orderPass );

			if( empty( $orderPass )) {
				return true;
			} else {

				$orderId = $this->getOrderIdByOrderPass( $orderNumber, $orderPass );
				if($orderId) {

					if(VmConfig::get('ordertracking','guests') == 'guestlink' or vmAccess::manager('orders')){
						$sess->set('getOrderDetails.'.$h,0);
						return $this->getOrder( $orderId );
					} //Guest case
					else {
						$o = $this->getOrder( $orderId );
						if(empty( $o['details']['BT']->virtuemart_user_id ) ) {
							$sess->set('getOrderDetails.'.$h,0);
							return $o;
						} else {
							return true;
						}
					}
				}
			}
		}

		return false;
	}


	/**
	 * Load a single order, Attention, this function is not protected! Do the right manangment before, to be certain
     * we suggest to use getMyOrderDetails
	 */
	public function getOrder($virtuemart_order_id){

		//sanitize id
		$virtuemart_order_id = (int)$virtuemart_order_id;
		$db = JFactory::getDBO();
		$order = array();

		// Get the order details
		$q = "SELECT  o.*, o.created_on as order_created, u.*, s.order_status_name
			FROM #__virtuemart_orders o
			LEFT JOIN #__virtuemart_orderstates s
			ON s.order_status_code = o.order_status
			LEFT JOIN #__virtuemart_order_userinfos u
			ON u.virtuemart_order_id = o.virtuemart_order_id
			WHERE o.virtuemart_order_id=".$virtuemart_order_id;
		$db->setQuery($q);
		$order['details'] = $db->loadObjectList('address_type');
		if($order['details'] and isset($order['details']['BT'])){
			$concat = array();
			if(!empty($order['details']['BT']->company))  $concat[]= $order['details']['BT']->company;
			if(!empty($order['details']['BT']->first_name))  $concat[]= $order['details']['BT']->first_name;
			if(!empty($order['details']['BT']->middle_name))  $concat[]= $order['details']['BT']->middle_name;
			if(!empty($order['details']['BT']->last_name))  $concat[]= $order['details']['BT']->last_name;
			$order['details']['BT']->order_name = '';
			foreach($concat as $c){
				$order['details']['BT']->order_name .= trim($c).' ';
			}
			$order['details']['BT']->order_name = trim(htmlspecialchars(strip_tags(htmlspecialchars_decode($order['details']['BT']->order_name))));

			if(isset($order['details']['ST'])){
				$order['details']['has_ST'] = true;
			} else {
				$order['details']['has_ST'] = false;
				$order['details']['ST'] =&$order['details']['BT'];
			}
		}

		// Get the order history
		$q = "SELECT *
			FROM #__virtuemart_order_histories
			WHERE virtuemart_order_id=".$virtuemart_order_id."
			ORDER BY virtuemart_order_history_id ASC";
		$db->setQuery($q);
		$order['history'] = $db->loadObjectList();

		// Get the order items
	$q = 'SELECT virtuemart_order_item_id, product_quantity, order_item_name, order_item_sku, i.virtuemart_product_id, product_item_price, product_final_price, product_basePriceWithTax, product_discountedPriceWithoutTax, product_priceWithoutTax, product_subtotal_with_tax, product_subtotal_discount, product_tax, product_attribute, order_status,
			intnotes, virtuemart_category_id
			FROM #__virtuemart_order_items i
				LEFT JOIN #__virtuemart_products p
				ON p.virtuemart_product_id = i.virtuemart_product_id
				LEFT JOIN #__virtuemart_product_categories c
				ON p.virtuemart_product_id = c.virtuemart_product_id
			WHERE `virtuemart_order_id`="'.$virtuemart_order_id.'" group by `virtuemart_order_item_id`';

		$orderBy = VmConfig::get('order_item_ordering','virtuemart_order_item_id');
        if (!empty ( $orderBy)) {
        	$orderingDir = VmConfig::get('order_item_ordering_dir','ASC');
            $q .= ' ORDER BY `'.$orderBy.'` ' . $orderingDir;
        }
//group by `virtuemart_order_id`'; Why ever we added this, it makes trouble, only one order item is shown then.
// without group by we get the product 3 times, when it is in 3 categories and similar, so we need a group by
//lets try group by `virtuemart_order_item_id`
		$db->setQuery($q);
		$order['items'] = $db->loadObjectList();

		$customfieldModel = VmModel::getModel('customfields');
		$pModel = VmModel::getModel('product');
		foreach($order['items'] as $p=>$item){

			$ids = array();

			$product = $pModel->getProduct($item->virtuemart_product_id);
			if($product){
				$pvar = get_object_vars($product);

				foreach ( $pvar as $k => $v) {
					if (!isset($item->$k) and strpos ($k, '_') !== 0 and property_exists($product, $k)) {
						$item->$k = $v;
					}
				}
			}

			if(!empty($item->product_attribute)){
				//Format now {"9":7,"20":{"126":{"comment":"test1"},"127":{"comment":"t2"},"128":{"comment":"topic 3"},"129":{"comment":"4 44 4 4 44 "}}}
				//$old = '{"46":" <span class=\"costumTitle\">Cap Size<\/span><span class=\"costumValue\" >S<\/span>","109":{"textinput":{"comment":"test"}}}';
				//$myCustomsOld = @json_decode($old,true);

				$myCustoms = @json_decode($item->product_attribute,true);
				$myCustoms = (array) $myCustoms;

				$item->customfields = array();
				foreach($myCustoms as $custom){
					if(!is_array($custom)){
						$custom = array( $custom =>false);
					}
					foreach($custom as $id=>$field){
						$item->customfields[] = $customfieldModel-> getCustomEmbeddedProductCustomField($id);
						$ids[] = $id;
					}
				}
			}

			if(!empty($product->customfields)){
				if(!isset($item->customfields)) $item->customfields = array();
				foreach($product->customfields as $customfield){
					if(!in_array($customfield->virtuemart_customfield_id,$ids) and $customfield->field_type=='E' and ($customfield->is_input or $customfield->is_cart_attribute)){
						$item->customfields[] = $customfield;
					}
				}
			}
			$order['items'][$p] = $item;
		}

// Get the order items
		$q = "SELECT  *
			FROM #__virtuemart_order_calc_rules AS z
			WHERE  virtuemart_order_id=".$virtuemart_order_id;
		$db->setQuery($q);
		$order['calc_rules'] = $db->loadObjectList();
		return $order;
	}

	/**
	 * Select the products to list on the product list page
	 * @param $uid integer Optional user ID to get the orders of a single user
	 * @param $_ignorePagination boolean If true, ignore the Joomla pagination (for embedded use, default false)
	 */
	public function getOrdersList($uid = 0, $noLimit = false) {
// 		vmdebug('getOrdersList');
		$tUserInfos = $this->getTable('userinfos');
		$this->_noLimit = $noLimit;

		$concat = array();
		if(property_exists($tUserInfos,'company'))  $concat[]= 'u.company';
		if(property_exists($tUserInfos,'first_name'))  $concat[]= 'u.first_name';
		if(property_exists($tUserInfos,'middle_name'))  $concat[]= 'u.middle_name';
		if(property_exists($tUserInfos,'last_name'))  $concat[]= 'u.last_name';
		if(!empty($concat)){
			$concatStr = "CONCAT_WS(' ',".implode(',',$concat).")";
		} else {
			$concatStr = 'o.order_number';
		}

// quorvia added phone, zip, city and shipping details and ST data
		$select = " o.*, ".$concatStr." AS order_name "
            .',u.email as order_email,
            pm.payment_name AS payment_method,
            sm.shipment_name AS shipment_method,
            u.company AS company,
            u.city AS city,
            u.zip AS zip,
            u.phone_1 AS phone,
            st.address_type AS st_type,
            st.company AS st_company,
            st.city AS st_city,
            st.zip AS st_zip,
            u.customer_note AS customer_note';
		$from = $this->getOrdersListQuery();

		$where = array();

		if(empty($uid)){
			if(VmConfig::get('multix','none')!='none'){
				if(vmAccess::manager('managevendors')){
					$virtuemart_vendor_id = vRequest::getInt('virtuemart_vendor_id',vmAccess::isSuperVendor());
				} else if( vmAccess::manager('orders')){
					$virtuemart_vendor_id = vmAccess::isSuperVendor();
				} else {
					$virtuemart_vendor_id = false;
				}
				if($virtuemart_vendor_id){
					$where[]= ' o.virtuemart_vendor_id = ' . (int)$virtuemart_vendor_id.' ';
				}
			}
			if(!vmAccess::manager('orders')){
				//A normal user is only allowed to see its own orders, we map $uid to the user id
				$user = JFactory::getUser();
				$uid = (int)$user->id;
				if(!empty($uid)){
					$where[]= ' u.virtuemart_user_id = ' . (int)$uid.' ';
				}
			}
		} else {
			$where[]= ' u.virtuemart_user_id = ' . (int)$uid.' ';
		}

		if ($this->search){
			$db = JFactory::getDBO();
			$this->search = '"%' . $db->escape( $this->search, true ) . '%"' ;
			$this->search = str_replace(' ','%',$this->search);

			$searchFields = array();
			$searchFields[] = 'u.first_name';
			//$searchFields[] = 'u.middle_name';
			$searchFields[] = 'u.last_name';
			$searchFields[] = 'o.order_number';
			$searchFields[] = 'u.company';
			$searchFields[] = 'u.email';
			$searchFields[] = 'u.phone_1';
			$searchFields[] = 'u.address_1';
			$searchFields[] = 'u.city';
			$searchFields[] = 'u.zip';
//quorvia addedd  ST data searches
			$searchFields[] = 'st.last_name';
			$searchFields[] = 'st.company';
			$searchFields[] = 'st.city';
			$searchFields[] = 'st.zip';
			$where[] = implode (' LIKE '.$this->search.' OR ', $searchFields) . ' LIKE '.$this->search.' ';
			//$where[] = ' ( u.first_name LIKE '.$search.' OR u.middle_name LIKE '.$search.' OR u.last_name LIKE '.$search.' OR `order_number` LIKE '.$search.')';
		}

		$order_status_code = vRequest::getString('order_status_code', false);
		if ($order_status_code and $order_status_code!=-1){
			$where[] = ' o.order_status = "'.$order_status_code.'" ';
		}

		if (count ($where) > 0) {
			$whereString = ' WHERE (' . implode (' AND ', $where) . ') ';
		}
		else {
			$whereString = '';
		}

		if ( vRequest::getCmd('view') == 'orders') {
			$ordering = $this->_getOrdering();
		} else {
			$ordering = ' order by o.modified_on DESC';
		}

		$this->_data = $this->exeSortSearchListQuery(0,$select,$from,$whereString,'',$ordering);

		if($this->_data){
			foreach($this->_data as $k=>$d){
				$this->_data[$k]->order_name = htmlspecialchars(strip_tags(htmlspecialchars_decode($d->order_name)));
			}
		}

		return $this->_data ;
	}

	/**
	 * List of tables to include for the product query
	 */
	private function getOrdersListQuery()	{
		return ' FROM #__virtuemart_orders as o
				LEFT JOIN #__virtuemart_order_userinfos as u
				ON u.virtuemart_order_id = o.virtuemart_order_id AND u.address_type="BT"
				LEFT JOIN #__virtuemart_order_userinfos as st
				ON st.virtuemart_order_id = o.virtuemart_order_id AND st.address_type="ST"
				LEFT JOIN #__virtuemart_paymentmethods_'.VmConfig::$vmlang.' as pm
				ON o.virtuemart_paymentmethod_id = pm.virtuemart_paymentmethod_id
				LEFT JOIN #__virtuemart_shipmentmethods_'.VmConfig::$vmlang.' as sm
				ON o.virtuemart_shipmentmethod_id = sm.virtuemart_shipmentmethod_id';
	}


	/**
	 * Update an order item status
	 * $vatTax was $orderUpdate boolean, keeps now the vattaxes
	 * @author Max Milbers
	 * @author Ondřej Spilka - used for item edit also
	 * @author Maik Künnemann
	 */
	public function updateSingleItem($virtuemart_order_item_id, &$orderdata, $orderUpdate = false, &$vatTax = false, $itemTaxes = array())
	{
		$virtuemart_order_item_id = (int)$virtuemart_order_item_id;
		//vmdebug('updateSingleItem',$virtuemart_order_item_id,$orderdata);
		$table = $this->getTable('order_items');
		$oldQuantity = 0;
		if(!empty($virtuemart_order_item_id)){
			$table->load($virtuemart_order_item_id);
			$oldOrderStatus = $table->order_status;
			$oldQuantity = $table->product_quantity;
		}

		if(empty($oldOrderStatus)){
			$oldOrderStatus = $orderdata->current_order_status;
			if($orderUpdate and empty($oldOrderStatus)){
				$oldOrderStatus = 'P';
			}
		}

		$dataT = $table->getProperties();//get_object_vars($table);

		$orderdatacopy = $orderdata;
		$data = array_merge($dataT,(array)$orderdatacopy);

		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		$this->_currencyDisplay = CurrencyDisplay::getInstance();
		$rounding = $this->_currencyDisplay->_priceConfig['salesPrice'][1];

		$date = JFactory::getDate();
		$today = $date->toSQL();
		//$vatTaxes = array();
		if ( $orderUpdate and !empty($data['virtuemart_order_item_id'])) {
			
			$taxCalcValue = false;
			$daTax = 'notset';

			foreach($itemTaxes as $tax) {
				if($tax->calc_kind=='VatTax'){
					$vat = $tax;
					$taxCalcValue = $vat->calc_value;
				}
				if($tax->calc_kind=='DATax'){
					$daTax = true;
				}
				if($tax->calc_kind=='DBTax'){
					$daTax = false;
				}
			}

			//Fallbacks
			if(!$taxCalcValue){
				//Could be a new item, missing the tax rules, we try to get one of another product.
				//get tax calc_value of product VatTax
				$db = JFactory::getDbo();
				$sql = 'SELECT * FROM `#__virtuemart_order_calc_rules` WHERE `virtuemart_order_id` = "'.$orderdata->virtuemart_order_id.'" AND `calc_kind` = "VatTax" ';
				$db->setQuery($sql);
				if ($vat = $db->loadObject()) {
					$taxCalcValue = $vat->calc_value;
					$vat->virtuemart_order_calc_rule_id = 0; 	//We set this here, so that we know the tax is missing and must be inserted
					vmdebug('updateSingleItem $taxCalcValue loaded by fallback '.$vat->virtuemart_calc_id);
				} else {
					$vat = false;
				}

			}

			//When the product has no discount
			if($daTax == 'notset'){
				$daTax = VmConfig::get('taxafterdiscount',$daTax);
				if($daTax == 'notset'){
					static $fallbackCached = null;
					if(isset($fallback)){
						$daTax = $fallbackCached;
					} else {
						$db = JFactory::getDbo();
						$sql = $sql = 'SELECT COUNT(*) FROM `#__virtuemart_calcs` WHERE `calc_kind` = "DATax"  ';
						$db->setQuery($sql);
						$r = $db->loadResult();
						if($r>0){
							$daTax = true;
						} else {
							$daTax = false;
						}
						$fallbackCached = $daTax;
					}
				}
			}

			$withTax = false;
			if(!empty($data['product_tax']) and $data['product_tax']!=='0.0' and $data['product_tax']!=='0.00000'){
				$withTax = true;
			} else if(empty($data['product_tax']) ){
				$withTax = true;
			}

			$overwriteDiscount = false;
			if(empty($data['product_subtotal_discount']) and $data['product_subtotal_discount'] === '' ){
				$overwriteDiscount = true;
				vmdebug('Set $overwriteDiscount '.$data['product_subtotal_discount']);
			}

			if(!empty($data['calculate_product_tax'])) {
				$data = self::calculateRow($data, $taxCalcValue, $rounding, $daTax, $withTax, $overwriteDiscount);
			}


			if(!empty($vat)){
				$t = $data['product_tax'] * $data['product_quantity'];

				if(isset($vatTax[$vat->virtuemart_calc_id])) {
					$vatTax[$vat->virtuemart_calc_id] += $t;
				} else {
					$vatTax[$vat->virtuemart_calc_id] = $t;
				}

				$sql = false;

				if(!empty($vat->virtuemart_order_calc_rule_id)){
					$sql = 'UPDATE `#__virtuemart_order_calc_rules` SET `calc_result`="'.$t.'",`calc_amount`="'.$data['product_tax'].'", `modified_on` = "'.$today.'" WHERE `virtuemart_order_calc_rule_id`="'.$vat->virtuemart_order_calc_rule_id.'"';
					$db->setQuery($sql);
					vmdebug('updateSingleItem $virtuemart_order_calc_rule_id',$sql);
					if ($db->execute() === false) {
						vmError($db->getError());
					}
				} else {
					$orderCalcRules = $this->getTable('order_calc_rules');
					$orderCalcRules->bind($vat);

					$orderCalcRules->virtuemart_order_calc_rule_id = 0;
					$orderCalcRules->calc_result = $t;
					$orderCalcRules->calc_amount = $data['product_tax'];
					$orderCalcRules->modified_on = $today;
					$orderCalcRules->virtuemart_order_item_id = $virtuemart_order_item_id;
					$orderCalcRules->check();
					$orderCalcRules->store();
				}
			}
		}

		if(!empty($table->virtuemart_vendor_id)){
			$data['virtuemart_vendor_id'] = $table->virtuemart_vendor_id;
		}

		$getProductById = false;
		if(!empty($data['virtuemart_product_id']) and $table->virtuemart_product_id!=$data['virtuemart_product_id']){
			$getProductById = true;
		}

		$table->bindChecknStore($data);

		if(empty($virtuemart_order_item_id) and !empty($table->virtuemart_order_item_id)){
			$virtuemart_order_item_id = $table->virtuemart_order_item_id;
		}

		//update product identification
		if ( $orderUpdate  and !empty($virtuemart_order_item_id)) {
			$set = false;
			if($getProductById){	// empty($data['order_item_sku']
				$set = 'oi.order_item_sku=p.product_sku, oi.order_item_name=l.product_name';
				$where = 'oi.virtuemart_product_id=p.virtuemart_product_id and
							oi.virtuemart_product_id=l.virtuemart_product_id and
							oi.virtuemart_order_item_id="'.(int)$virtuemart_order_item_id.'"';
			} else if (empty($table->virtuemart_product_id) and !empty($data['order_item_sku'])){

				$set = 'oi.virtuemart_product_id=p.virtuemart_product_id, oi.order_item_name=l.product_name';
				$where = 'p.virtuemart_product_id=l.virtuemart_product_id and
				 			p.product_sku="'.$data['order_item_sku'].'" and
				 			oi.virtuemart_order_item_id="'.(int)$virtuemart_order_item_id.'"';
			}

			if(!empty($set)){
				$prolang = '#__virtuemart_products_' . VmConfig::$vmlang;
				$oi = " #__virtuemart_order_items";
				$protbl = "#__virtuemart_products";
				$update = $oi.' as oi, '.$protbl.' as p, '.$prolang . ' as l';

				$db = JFactory::getDBO();
				$q = 'UPDATE '.$update.' SET '.$set.' WHERE '.$where;
				$db->setQuery($q);
				if ($db->execute() === false) {
					vmError('updateSingleItem '.$sql,'Error updating order');
				}
			}
		}

		//store history
		if($orderUpdate){
			$table->emptyCache();
			$table->load($virtuemart_order_item_id);
			if($dataT['oi_hash']!=$table->oi_hash){
				if(empty($dataT['virtuemart_order_item_id'])){
					$dataT['action'] = 'new';
					if(!empty($virtuemart_order_item_id)){
						$dataT['virtuemart_order_item_id'] = $virtuemart_order_item_id;
					}

					$props = $table->getProperties();
					foreach($props as $k=>$v){
						if(empty($dataT[$k])){
							$dataT[$k] = $v;
						}
					}
				}
				$tableHist = $this->getTable('order_item_histories');

				$tableHist->bindChecknStore($dataT);
			}
		}



		if(!empty($oldQuantity) and $oldQuantity!=$table->product_quantity){
			$this->handleStockAfterStatusChangedPerProduct($oldOrderStatus, $oldOrderStatus, $table, $oldQuantity);
		}
		$this->handleStockAfterStatusChangedPerProduct($orderdata->order_status, $oldOrderStatus, $table,$table->product_quantity);

		return $table;
	}

	function calculateRow($data, $taxCalcValue, $rounding, $daTax = true, $withTax = true, $overrideDiscount = false){

		$quantity = $data['product_quantity'];
		if(empty($data['product_subtotal_discount'])){
			$data['product_subtotal_discount'] = 0.0;
		} else {
			$itemDiscount = $data['product_subtotal_discount'];
			if($itemDiscount<0.0){
				$itemDiscount = $itemDiscount * (-1);
			}
			$itemDiscount = $itemDiscount/$quantity;
		}

		$taxValue = $taxCalcValue;
		if(!$withTax){
			$data['product_tax'] = 0.0;
			$taxValue = 0.0;
		}

		$roundIntern = 5;
		if(VirtueMartModelOrders::isNotEmptyDec($data,'product_basePriceWithTax') and VirtueMartModelOrders::isEmptyDec($data, 'product_item_price') and VirtueMartModelOrders::isEmptyDec($data,'product_final_price')){

			$data['product_item_price'] = $data['product_basePriceWithTax'] * (1 - $taxCalcValue / ($taxCalcValue + 100));
			$data['product_item_price'] = round($data['product_item_price'], $roundIntern);
		}

		if(VirtueMartModelOrders::isNotEmptyDec($data,'product_item_price')){

			$data['product_basePriceWithTax'] = round( $data['product_item_price'] * (1 + $taxValue/100.0), $rounding );

			if($daTax){
				if($overrideDiscount and VirtueMartModelOrders::isEmptyDec($data,'product_subtotal_discount') and VirtueMartModelOrders::isNotEmptyDec($data,'product_final_price')){
					$itemDiscount = $data['product_basePriceWithTax'] - $data['product_final_price'];
				} else {
					$data['product_final_price'] = $data['product_basePriceWithTax'] - $itemDiscount;
				}
			} else {
				if($overrideDiscount and VirtueMartModelOrders::isEmptyDec($data,'product_subtotal_discount') and VirtueMartModelOrders::isNotEmptyDec($data,'product_final_price')){
					$itemDiscount = round($data['product_item_price'] - $data['product_final_price'] + $data['product_final_price'] * $taxValue/(100 + $taxValue), $rounding);
				} else {
					$data['product_final_price'] = round( ($data['product_item_price'] - $itemDiscount) * ((100 + $taxValue)/100.0) , $rounding);
				}
			}
		} else if (VirtueMartModelOrders::isNotEmptyDec($data,'product_final_price')){
			if($daTax){
				$data['product_item_price'] = round( ($data['product_final_price'] + $itemDiscount) * (1 -  $taxValue / ($taxValue + 100)), $roundIntern ) ;
				$data['product_basePriceWithTax'] = round($data['product_item_price'] * (1 + $taxCalcValue/100.0), $rounding );
			} else {
				$data['product_item_price'] = round( $data['product_final_price'] * (1 -  $taxValue / ($taxValue + 100)) + $itemDiscount, $roundIntern );
				$data['product_basePriceWithTax'] = round( $data['product_item_price'] * (1 +  $taxValue/100.0), $rounding );
			}
		} else {
			vmdebug('Missing case');
		}

		if($withTax){
			$data['product_tax'] = round($data['product_final_price'], $rounding) * $taxCalcValue / ($taxCalcValue + 100);
		} else {
			$data['product_basePriceWithTax'] = round( $data['product_item_price'] * (1 + $taxCalcValue/100.0), $rounding );
		}
		$data['product_tax'] = round($data['product_tax'], $roundIntern);


		$data['product_discountedPriceWithoutTax'] = $data['product_item_price'] - ($itemDiscount);
		//$data['product_discountedPriceWithoutTax'] = $data['product_final_price'] - $data['product_tax'];

		//if($daTax){
			$data['product_priceWithoutTax'] = $data['product_final_price'] - $data['product_tax'];
		/*} else {
			$data['product_priceWithoutTax'] = $data['product_final_price'] - $data['product_tax'];
		}*/


//$data['product_subtotal_discount'] = (round($orderdata->product_final_price, $rounding) - round($data['product_basePriceWithTax'], $rounding)) * $orderdata->product_quantity;
		$data['product_subtotal_with_tax'] = round($data['product_final_price'], $rounding) * $data['product_quantity'];

		if($data['product_subtotal_discount']<0.0){
			$itemDiscount = $itemDiscount * (-1);
		}
		$data['product_subtotal_discount'] = $quantity * $itemDiscount;
vmdebug('my prices',$data);
		return $data;
	}

	public static function isEmptyDec($d,$n){
		return (boolean) (empty($d[$n]) or $d[$n]==0);
	}

	public static function isNotEmptyDec($d,$n){
		return (boolean) (isset($d[$n]) and $d[$n]!=0);
	}

	public function updateBill($virtuemart_order_id, $vattax){

		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		$this->_currencyDisplay = CurrencyDisplay::getInstance();
		$rounding = $this->_currencyDisplay->_priceConfig['salesPrice'][1];
		//OSP update cartRules/shipment/payment
		//it would seem strange this is via item edit
		//but in general, shipment and payment would be tractated as another items of the order
		//in datas they are not, bu okay we have it here and functional
		//moreover we can compute all aggregate values here via one aggregate SQL
		if (!empty( $virtuemart_order_id)) {

			$db = JFactory::getDBO();
			$ordid = $virtuemart_order_id;
			//cartRules
			$calc_rules = vRequest::getVar('calc_rules',false);
			$calculate_billTaxAmount = vRequest::getInt('calculate_billTaxAmount',false);
			//$calc_rules_amount = 0;
			$calc_rules_discount_amount = 0;
			$calc_rules_tax_amount = 0;
			$calc_rules_vattax_amount = 0;

			if(!empty($calc_rules))
			{
				foreach($calc_rules as $calc_kind => $calc_rule) {

					foreach($calc_rule as $virtuemart_calc_id => $calc_amount) {

						if($calculate_billTaxAmount){
							if(!isset($vattax[$virtuemart_calc_id])) continue;
							$calc_amount = $vattax[$virtuemart_calc_id];
						}

						if ($calc_kind == 'DBTaxRulesBill' || $calc_kind == 'DATaxRulesBill') {
							$calc_rules_discount_amount += $calc_amount;
						}
						else if ($calc_kind == 'taxRulesBill') {
							$calc_rules_tax_amount += $calc_amount;
						}
						else if ($calc_kind == 'VatTax') {
							$calc_rules_vattax_amount += $calc_amount;
						}
					}
				}
			}

			$date = JFactory::getDate();
			$today = $date->toSQL();

			//shipment
			$os = vRequest::getString('order_shipment');
			$ost = vRequest::getString('order_shipment_tax');

			if ( $os!="" )
			{
				$sql = 'UPDATE `#__virtuemart_orders` SET `order_shipment`="'.$os.'",`order_shipment_tax`="'.$ost.'", `modified_on` = "'.$today.'" WHERE  `virtuemart_order_id`="'.$ordid.'"';
				$db->setQuery($sql);
				if ($db->execute() === false) {
					vmError('updateSingleItem Error updating order_shipment '.$sql);
				}
			}

			//payment
			$op = vRequest::getString('order_payment');
			$opt = vRequest::getString('order_payment_tax');
			if ( $op!="" )
			{
				$sql = 'UPDATE `#__virtuemart_orders` SET `order_payment`="'.$op.'",`order_payment_tax`="'.$opt.'", `modified_on` = "'.$today.'" WHERE  `virtuemart_order_id`="'.$ordid.'"';
				$db->setQuery($sql);
				if ($db->execute() === false) {
					vmError('updateSingleItem Error updating order payment'.$sql);
				}
			}

			$sql = 'UPDATE `#__virtuemart_orders` SET '.
			'`order_discountAmount`=(SELECT sum(product_subtotal_discount) FROM #__virtuemart_order_items where `virtuemart_order_id`='.$ordid.'),
					`order_billDiscountAmount`=`order_discountAmount`+'.$calc_rules_discount_amount.',
					`order_salesPrice`=(SELECT sum(product_final_price*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`='.$ordid.'),
					`order_tax`=(SELECT sum( product_tax*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`='.$ordid.'),
					`order_subtotal`=(SELECT sum(ROUND(product_item_price, '. $rounding .')*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`='.$ordid.'),';
			vmdebug('$calc_rules_amount',$calc_rules_vattax_amount, $calc_rules_tax_amount, $calc_rules_discount_amount);
			if(vRequest::getString('calculate_billTaxAmount')) {
				$sql .= '`order_billTaxAmount`= `order_shipment_tax`+`order_payment_tax`+ '.$calc_rules_tax_amount.' + '.$calc_rules_vattax_amount;
			} else {
				$sql .= '`order_billTaxAmount`="'.vRequest::getString('order_billTaxAmount').'"';
			}
			//$sql .= ',`order_total`=(SELECT sum(product_final_price*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`='.$ordid.')+`order_shipment`+`order_shipment_tax`+`order_payment`+`order_payment_tax`+'.$calc_rules_amount.',';
			$sql .= ',`order_total`=(SELECT sum(product_final_price*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`='.$ordid.') + `order_shipment` +`order_payment` + `order_shipment_tax`+`order_payment_tax` - '.$calc_rules_discount_amount;

			$sql .= ', `modified_on` = "'.$today.'"';
			$sql .= ' WHERE  `virtuemart_order_id`='.$ordid;

			$db->setQuery($sql);
			if ($db->execute() === false) {
				vmError('updateSingleItem '.$db->getError().' and '.$sql);
			}

		}

	}

	/**
	 * Strange name is just temporarly
	 *
	 * @param unknown_type $order_id
	 * @param unknown_type $order_status
         * @author Max Milbers
	 */
	var $useDefaultEmailOrderStatus = true;
	public function updateOrderStatus($orders=0, $order_id =0,$order_status=0){

		//General change of orderstatus
		$total = 1 ;
		if(empty($orders)){
			$orders = array();
			$orderslist = vRequest::getVar('orders',  array());
			$total = 0 ;
			// Get the list of orders in post to update
			foreach ($orderslist as $key => $order) {
				if ( $orderslist[$key]['order_status'] !== $orderslist[$key]['current_order_status'] ) {
					$orders[$key] =  $orderslist[$key];
					$total++;
				}
			}
		}

		if(!is_array($orders)){
			$orders = array($orders);
		}

		/* Process the orders to update */
		$updated = 0;
		$error = 0;
		if ($orders) {
			// $notify = vRequest::getVar('customer_notified', array()); // ???
			// $comments = vRequest::getVar('comments', array()); // ???
			foreach ($orders as $virtuemart_order_id => $order) {
				if  ($order_id >0) $virtuemart_order_id= $order_id;
				$this->useDefaultEmailOrderStatus = false;
				if($this->updateStatusForOneOrder($virtuemart_order_id,$order,true)){
					$updated ++;
				} else {
					$error++;
				}
			}
		}
		$result = array( 'updated' => $updated , 'error' =>$error , 'total' => $total ) ;
		return $result ;

	}

	/**
	 * Attention, if you use this function within your trigger take care of the last parameter,
	 * you should define it, this parameter maybe set to false in future releases
	 *
	 * IMPORTANT: The $inputOrder can contain extra data by plugins
	 *
	 * @param $virtuemart_order_id
	 * @param $inputOrder
	 * @param bool $useTriggers
	 * @return bool
	 */
	function updateStatusForOneOrder($virtuemart_order_id,$inputOrder,$useTriggers=true){

// 		vmdebug('updateStatusForOneOrder', $inputOrder);

		/* Update the order */
		$data = $this->getTable('orders');
		$data->load($virtuemart_order_id);
		$old_order_status = $data->order_status;
		if(empty($inputOrder['virtuemart_order_id'])){
			unset($inputOrder['virtuemart_order_id']);
		}

		$data->bind($inputOrder);

		$cp_rm = VmConfig::get('cp_rm',array('C'));
		if(!is_array($cp_rm)) $cp_rm = array($cp_rm);

		if ( in_array((string) $data->order_status,$cp_rm) ){
			if (!empty($data->coupon_code)) {
				if (!class_exists('CouponHelper'))
					require(VMPATH_SITE . DS . 'helpers' . DS . 'coupon.php');
				CouponHelper::RemoveCoupon($data->coupon_code);
			}
		}
		//First we must call the payment, the payment manipulates the result of the order_status
		if($useTriggers){

			if(!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS.DS.'vmpsplugin.php');

			JPluginHelper::importPlugin('vmcalculation');
			JPluginHelper::importPlugin('vmcustom');

			JPluginHelper::importPlugin('vmshipment');
			$_dispatcher = JDispatcher::getInstance();											//Should we add this? $inputOrder
			$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderShipment',array(&$data,$old_order_status));

			// Payment decides what to do when order status is updated
			JPluginHelper::importPlugin('vmpayment');
			$_dispatcher = JDispatcher::getInstance();											//Should we add this? $inputOrder
			$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderPayment',array(&$data,$old_order_status));
			foreach ($_returnValues as $_returnValue) {
				if ($_returnValue === true) {
					break; // Plugin was successfull
				} elseif ($_returnValue === false) {
					return false; // Plugin failed
				}
				// Ignore null status and look for the next returnValue
			}

			/**
			* If an order gets cancelled, fire a plugin event, perhaps
			* some authorization needs to be voided
			*/
			if ($data->order_status == "X") {

				$_dispatcher = JDispatcher::getInstance();
				//Should be renamed to plgVmOnCancelOrder
				$_dispatcher->trigger('plgVmOnCancelPayment',array(&$data,$old_order_status));
			}
		}

		if(empty($data->delivery_date)){
			$del_date_type = VmConfig::get('del_date_type','m');
			if(strpos($del_date_type,'os')!==FALSE){	//for example osS
				$os = substr($del_date_type,2);
				if($data->order_status == $os){
					$date = JFactory::getDate();
					$data->delivery_date = $date->toSQL();
				}
			} else {
				vmLanguage::loadJLang('com_virtuemart_orders', true);
				$data->delivery_date = vmText::_('COM_VIRTUEMART_DELDATE_INV');
			}
		}

		if ($data->store()) {

			$task= vRequest::getCmd('task',0);
			$view= vRequest::getCmd('view',0);

			//The item_id of the request is already given as inputOrder by the calling function (controller). inputOrder could be manipulated by the
			//controller and so we must not use the request data here.
			$upd_items = vRequest::getVar('item_id',false);
			if($upd_items) {

				//get tax calc_value of product VatTax
				$db = JFactory::getDBO();
				$sql = 'SELECT * FROM `#__virtuemart_order_calc_rules` WHERE `virtuemart_order_id` = "'.$virtuemart_order_id.'" ORDER BY virtuemart_order_item_id';
				$db->setQuery( $sql );
				$orderCalcs = $db->loadObjectList();

				$vatTaxes = array();

				foreach( $inputOrder as $item_id => $order_item_data ) {

					if(!empty($item_id) and $item_id=='customer_notified') continue;	//Attention, we need the check against empty, else it continues for "0"

					$order_item_data['current_order_status'] = $order_item_data['order_status'];
					if(!isset( $order_item_data['comments'] )) $order_item_data['comments'] = '';
					$order_item_data = (object)$order_item_data;
					$order_item_data->virtuemart_order_id = $virtuemart_order_id;

					//$this->updateSingleItem($order_item->virtuemart_order_item_id, $data->order_status, $order['comments'] , $virtuemart_order_id, $data->order_pass);
					if(empty( $item_id )) {
						$inputOrder['comments'] .= ' '.vmText::sprintf( 'COM_VIRTUEMART_ORDER_PRODUCT_ADDED', $order_item_data->order_item_name );
					}
					$taxes = array();
					foreach( $orderCalcs as $calc ) {

						if($calc->virtuemart_order_item_id == $item_id and ($calc->calc_kind == 'VatTax' or $calc->calc_kind == 'DATax')) {
							$taxes[] = $calc;
						}
					}

					$this->updateSingleItem( $item_id, $order_item_data, true, $vatTaxes, $taxes );
				}

				$this->updateBill($virtuemart_order_id, $vatTaxes);
			} else {

				$update_lines = 1;
				if ($task==='updatestatus' and $view==='orders') {
					$lines = vRequest::getVar('orders');
					$update_lines = $lines[$virtuemart_order_id]['update_lines'];
				}

				if($update_lines==1){

					$q = 'SELECT virtuemart_order_item_id
												FROM #__virtuemart_order_items
												WHERE virtuemart_order_id="'.$virtuemart_order_id.'"';
					$db = JFactory::getDBO();
					$db->setQuery($q);
					$order_items = $db->loadObjectList();
					if ($order_items) {
						foreach ($order_items as $item_id=>$order_item) {
							$this->updateSingleItem($order_item->virtuemart_order_item_id, $data);
						}
					}
				}
			}

			//Must be below the handling of the order items, else we must add an except as for "customer_notified"
			$inputOrder['comments'] = trim($inputOrder['comments']);
			$invM = VmModel::getModel('invoice');
			//TODO use here needNewInvoiceNumber

			if($old_order_status!=$data->order_status and VirtueMartModelInvoice::needInvoiceByOrderstatus($inputOrder['order_status'])){
				$inputOrder['invoice_number'] = $invM->createReferencedInvoiceNumber($data->virtuemart_order_id, $inputOrder);
			}

			//We need a new invoice, therefore rename the old one.
			/*$inv_os = VmConfig::get('inv_os',array('C'));
			if(!is_array($inv_os)) $inv_os = array($inv_os);
			if($old_order_status!=$data->order_status and in_array($data->order_status,$inv_os)){
				//$this->renameInvoice($data->virtuemart_order_id);
				vmdebug('my input order here',$inputOrder);
				$inputOrder['invoice_number'] = $this->createReferencedInvoice($data->virtuemart_order_id);
			}*/

			/* Update the order history */
			$this->_updateOrderHist($virtuemart_order_id, $data->order_status, $inputOrder['customer_notified'], $inputOrder['comments']);


			// When the plugins did not already notified the user, do it here (the normal way)
			//Attention the ! prevents at the moment that an email is sent. But it should used that way.
// 			if (!$inputOrder['customer_notified']) {
			$this->notifyCustomer( $data->virtuemart_order_id , $inputOrder );
// 			}

			JPluginHelper::importPlugin('vmcoupon');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmCouponUpdateOrderStatus', array($data, $old_order_status));
			if(!empty($returnValues)){
				foreach ($returnValues as $returnValue) {
					if ($returnValue !== null  ) {
						return $returnValue;
					}
				}
			}


			return true;
		} else {
			return false;
		}
	}


	/**
	 * Get the information from the cart and create an order from it
	 *
	 * @author Oscar van Eijk
	 * @param object $_cart The cart data
	 * @return mixed The new ordernumber, false on errors
	 */
	public function createOrderFromCart($cart)
	{

		if ($cart === null) {
			vmError('createOrderFromCart() called without a cart - that\'s a programming bug','Can\'t create order, sorry.');
			return false;
		}

		$usr = JFactory::getUser();
		//$prices = $cart->getCartPrices();
		if (($orderID = $this->_createOrder($cart, $usr)) == 0) {
			vmError('Couldn\'t create order','Couldn\'t create order');
			return false;
		}
		if (!$this->_createOrderLines($orderID, $cart)) {
			vmError('Couldn\'t create order items','Couldn\'t create order items');
			return false;
		}
		if (!$this-> _createOrderCalcRules($orderID, $cart) ) {
			vmError('Couldn\'t create order items','Couldn\'t create order items');
			return false;
		}
		$this->_updateOrderHist($orderID);
		if (!$this->_writeUserInfo($orderID, $usr, $cart)) {
			vmError('Couldn\'t create order userinfo','Couldn\'t create order userinfo');
			return false;
		}

		return $orderID;
	}

	/**
	 * Write the order header record
	 *
	 * @author Oscar van Eijk
	 * @param object $_cart The cart data
	 * @param object $_usr User object
	 * @param array $_prices Price data
	 * @return integer The new ordernumber
	 */
	private function _createOrder($_cart, $_usr)
	{
		//		TODO We need tablefields for the new values:
		//		Shipment:
		//		$_prices['shipmentValue']		w/out tax
		//		$_prices['shipmentTax']			Tax
		//		$_prices['salesPriceShipment']	Total
		//
		//		Payment:
		//		$_prices['paymentValue']		w/out tax
		//		$_prices['paymentTax']			Tax
		//		$_prices['paymentDiscount']		Discount
		//		$_prices['salesPricePayment']	Total

		$_orderData = new stdClass();

		$_orderData->virtuemart_order_id = null;
		$oldOrderNumber = '';
		if(!empty($_cart->virtuemart_order_id)){
			$_orderData->virtuemart_order_id = $this->reUsePendingOrder($_cart);
			if($_orderData->virtuemart_order_id){
				$order = $this->getOrder($_orderData->virtuemart_order_id);
				$oldOrderNumber = $order['details']['BT']->order_number;
			}
		}

		$_orderData->virtuemart_user_id = $_usr->get('id');
		$_orderData->virtuemart_vendor_id = $_cart->vendorId;
		$_orderData->customer_number = $_cart->customer_number;
		$_prices = $_cart->cartPrices;
		//Note as long we do not have an extra table only storing addresses, the virtuemart_userinfo_id is not needed.
		//The virtuemart_userinfo_id is just the id of a stored address and is only necessary in the user maintance view or for choosing addresses.
		//the saved order should be an snapshot with plain data written in it.
		//		$_orderData->virtuemart_userinfo_id = 'TODO'; // $_cart['BT']['virtuemart_userinfo_id']; // TODO; Add it in the cart... but where is this used? Obsolete?
		$_orderData->order_total = $_prices['billTotal'];
		$_orderData->order_salesPrice = $_prices['salesPrice'];
		$_orderData->order_billTaxAmount = $_prices['billTaxAmount'];
		$_orderData->order_billDiscountAmount = $_prices['billDiscountAmount'];
		$_orderData->order_discountAmount = $_prices['discountAmount'];
		$_orderData->order_subtotal = $_prices['priceWithoutTax'];
		$_orderData->order_tax = $_prices['taxAmount'];
		$_orderData->order_shipment = $_prices['shipmentValue'];
		$_orderData->order_shipment_tax = $_prices['shipmentTax'];
		$_orderData->order_payment = $_prices['paymentValue'];
		$_orderData->order_payment_tax = $_prices['paymentTax'];

		if (!empty($_cart->cartData['VatTax'])) {
			$taxes = array();
			foreach($_cart->cartData['VatTax'] as $k=>$VatTax) {
				$taxes[$k]['virtuemart_calc_id'] = $k;
				$taxes[$k]['calc_name'] = $VatTax['calc_name'];
				$taxes[$k]['calc_value'] = $VatTax['calc_value'];
				$taxes[$k]['result'] = $VatTax['result'];
			}
			$_orderData->order_billTax = vmJsApi::safe_json_encode($taxes);
		}

		if (!empty($_cart->couponCode)) {
			$_orderData->coupon_code = $_cart->couponCode;
			$_orderData->coupon_discount = $_prices['salesPriceCoupon'];
		}
		$_orderData->order_discount = $_prices['discountAmount'];  // discount order_items


		$_orderData->order_status = 'P';
		$_orderData->order_currency = $this->getVendorCurrencyId($_orderData->virtuemart_vendor_id);
		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . '/helpers/currencydisplay.php');
		}

		if (isset($_cart->pricesCurrency)) {
			$_orderData->user_currency_id = $_cart->pricesCurrency ;
			$currency = CurrencyDisplay::getInstance($_orderData->user_currency_id);
			if($_orderData->user_currency_id != $_orderData->order_currency){
				$_orderData->user_currency_rate =   $currency->convertCurrencyTo($_orderData->user_currency_id ,1.0,false);
			} else {
				$_orderData->user_currency_rate=1.0;
			}
		}

		$shoppergroups = $_cart->user->get('shopper_groups');
		if (!empty($shoppergroups)) {
			$_orderData->user_shoppergroups = implode(',', $shoppergroups);
		}

		if (isset($_cart->paymentCurrency)) {
			$_orderData->payment_currency_id = $_cart->paymentCurrency ;//$this->getCurrencyIsoCode($_cart->pricesCurrency);
			$currency = CurrencyDisplay::getInstance($_orderData->payment_currency_id);
			if($_orderData->payment_currency_id != $_orderData->order_currency){
				$_orderData->payment_currency_rate =   $currency->convertCurrencyTo($_orderData->payment_currency_id ,1.0,false);
			} else {
				$_orderData->payment_currency_rate=1.0;
			}
		}

		$_orderData->virtuemart_paymentmethod_id = $_cart->virtuemart_paymentmethod_id;
		$_orderData->virtuemart_shipmentmethod_id = $_cart->virtuemart_shipmentmethod_id;

		//Some payment plugins need a new order_number for any try
		$_orderData->order_number = '';
		$_orderData->order_pass = '';

		$_orderData->order_language = $_cart->order_language;
		$_orderData->ip_address = ShopFunctions::getClientIP();;

		$maskIP = VmConfig::get('maskIP','last');
		if($maskIP=='last'){
			$rpos = strrpos($_orderData->ip_address,'.');
			$_orderData->ip_address = substr($_orderData->ip_address,0,($rpos+1)).'xx';
		}


		//lets merge here the userdata from the cart to the order so that it can be used
		if(!empty($_cart->BT)){
			foreach($_cart->BT as $k=>$v){
				$_orderData->$k = $v;
			}
		}

		JPluginHelper::importPlugin('vmshopper');
		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$plg_datas = $dispatcher->trigger('plgVmOnUserOrder',array(&$_orderData));


		$i = 0;
		while($oldOrderNumber==$_orderData->order_number) {
			if($i>5) {
				$msg = 'Could not generate new unique ordernumber';
				vmError($msg.', an ordernumber should contain at least one random char', $msg);
				break;
			}
			$_orderData->order_number = $this->genStdOrderNumber( $_orderData->virtuemart_vendor_id );
			if(!empty($oldOrderNumber))vmdebug('Generated new ordernumber ',$oldOrderNumber,$_orderData->order_number);
			$i++;
		}
		if(empty($_orderData->order_pass)){
			$_orderData->order_pass = $this->genStdOrderPass();
		}
		if(empty($_orderData->order_create_invoice_pass)){
			$_orderData->order_create_invoice_pass = $this->genStdCreateInvoicePass();
		}

		$orderTable =  $this->getTable('orders');
		$orderTable -> bindChecknStore($_orderData);

		if (!empty($_cart->couponCode)) {
			//set the virtuemart_order_id in the Request for 3rd party coupon components (by Seyi and Max)
			vRequest::setVar ( 'virtuemart_order_id', $orderTable->virtuemart_order_id );
			// If a gift coupon was used, remove it now
			CouponHelper::setInUseCoupon($_cart->couponCode, true);
		}
		// the order number is saved into the session to make sure that the correct cart is emptied with the payment notification
		$_cart->order_number = $orderTable->order_number;
		$_cart->order_pass = $_orderData->order_pass;
		$_cart->virtuemart_order_id = $orderTable->virtuemart_order_id;
		$_cart->setCartIntoSession ();

		return $orderTable->virtuemart_order_id;
	}

	function reUsePendingOrder($_cart,$customer_number = false){
		$order = false;
		$db = JFactory::getDbo();
		$q = 'SELECT * FROM `#__virtuemart_orders` ';
		if(!empty($_cart->virtuemart_order_id)){
			$db->setQuery($q . ' WHERE `virtuemart_order_id`= "'.$_cart->virtuemart_order_id.'" AND `order_status` = "P" ');
			$order = $db->loadAssoc();
			if(!$order){
				vmdebug('This should not happen, there is a cart with order_number, but not order stored '.$_cart->virtuemart_order_id);
			}
		}

		if($customer_number and VmConfig::get('reuseorders',true) and !$order){
			$jnow = JFactory::getDate();
			$jnow->sub(new DateInterval('PT1H'));
			$minushour = $jnow->toSQL();
			$q .= ' WHERE `customer_number`= "'.$customer_number.'" ';
			$q .= '	AND `order_status` = "P"
				AND `created_on` > "'.$minushour.'" ';
			$db->setQuery($q);
			$order = $db->loadAssoc();
		}

		if($order) {
			//Dirty hack
			$this->removeOrderItems( $order['virtuemart_order_id'], false );

			$psTypes = array('shipment','payment');
			foreach($psTypes as $_psType){
				if(!empty($order['virtuemart_'.$_psType.'method_id'])){
					$q = 'SELECT `'.$_psType.'_element` FROM `#__virtuemart_'.$_psType.'methods` ';
					$q .= 'WHERE `virtuemart_'.$_psType.'method_id` = "'.(int)$order['virtuemart_'.$_psType.'method_id'].'" ';
					$db->setQuery($q);
					$plg_name = $db->loadResult();
					if(empty($plg_name)) continue;
					$_tablename = '#__virtuemart_' . $_psType . '_plg_' . $plg_name;

					$q = 'DELETE FROM '.$_tablename.' WHERE virtuemart_order_id="'.$order['virtuemart_order_id'].'"';
					$db->setQuery($q);
					$db->execute();
				}
			}

			return $order['virtuemart_order_id'];
		} else {
			return false;
		}
	}

	private function getVendorCurrencyId($vendorId){
		$q = 'SELECT `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`="'.$vendorId.'" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$vendorCurrency =  $db->loadResult();
		return $vendorCurrency;
	}


	/**
	 * Write the BillTo record, and if set, the ShipTo record
	 *
	 * @author Oscar van Eijk
	 * @param integer $_id Order ID
	 * @param object $_usr User object
	 * @param object $_cart Cart object
	 * @return boolean True on success
	 */
	private function _writeUserInfo($_id, &$_usr, $_cart)
	{
		$_userInfoData = array();

		if(!class_exists('VirtueMartModelUserfields')) require(VMPATH_ADMIN.DS.'models'.DS.'userfields.php');

		$_userFieldsModel = VmModel::getModel('userfields');
		$_userFieldsBT = $_userFieldsModel->getUserFields('account'
		, array('delimiters'=>true, 'captcha'=>true)
		, array('username', 'password', 'password2', 'user_is_vendor')
		);

		$userFieldsCart = $_userFieldsModel->getUserFields(
			'cart'
			, array('captcha' => true, 'delimiters' => true) // Ignore these types
			, array('user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type') // Skips
		);
		$_userFieldsBT = array_merge($_userFieldsBT,$userFieldsCart);


		foreach ($_userFieldsBT as $_fld) {
			$_name = $_fld->name;
			if(!empty( $_cart->BT[$_name])){
				if (is_array( $_cart->BT[$_name])) {
					$_userInfoData[$_name] =  implode("|*|",$_cart->BT[$_name]);
				} else {
					$_userInfoData[$_name] = $_cart->BT[$_name];
				}
			}
		}

		$_userInfoData['virtuemart_order_id'] = $_id;
		$_userInfoData['virtuemart_user_id'] = $_usr->get('id');
		$_userInfoData['address_type'] = 'BT';

		$db = JFactory::getDBO();
		$q = ' SELECT `virtuemart_order_userinfo_id` FROM `#__virtuemart_order_userinfos` ';
		$q .= ' WHERE `virtuemart_order_id` = "'.$_id.'" AND `address_type` = "BT" ';
		$db->setQuery($q);
		if($vmOId = $db->loadResult()){
			$_userInfoData['virtuemart_order_userinfo_id'] = $vmOId;
		}

		$order_userinfosTable = $this->getTable('order_userinfos');
		if (!$order_userinfosTable->bindChecknStore($_userInfoData)){
			return false;
		}

		if ($_cart->ST and empty($_cart->STsameAsBT)) {
			$_userInfoData = array();
			$_userFieldsST = $_userFieldsModel->getUserFields('shipment'
			, array('delimiters'=>true, 'captcha'=>true)
			, array('username', 'password', 'password2', 'user_is_vendor')
			);
			foreach ($_userFieldsST as $_fld) {
				$_name = $_fld->name;
				if(!empty( $_cart->ST[$_name])){
					$_userInfoData[$_name] = $_cart->ST[$_name];
				}
			}

			$_userInfoData['virtuemart_order_id'] = $_id;
			$_userInfoData['virtuemart_user_id'] = $_usr->get('id');
			$_userInfoData['address_type'] = 'ST';

			$q = ' SELECT `virtuemart_order_userinfo_id` FROM `#__virtuemart_order_userinfos` ';
			$q .= ' WHERE `virtuemart_order_id` = "'.$_id.'" AND `address_type` = "ST" ';
			$db->setQuery($q);
			if($vmOId = $db->loadResult()){
				$_userInfoData['virtuemart_order_userinfo_id'] = $vmOId;
			}

			$order_userinfosTable = $this->getTable('order_userinfos');
			if (!$order_userinfosTable->bindChecknStore($_userInfoData)){
				return false;
			}
		}
		return true;
	}


	function handleStockAfterStatusChangedPerProduct($newState, $oldState, $tableOrderItems, $quantity) {

		vmdebug( 'handleStockAfterStatusChangedPerProduct '.$oldState.' '.$newState.' '. $quantity, $tableOrderItems->product_quantity);
		if($newState == $oldState and $quantity == $tableOrderItems->product_quantity) return;
		// $StatutWhiteList = array('P','C','X','R','S','N');
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM `#__virtuemart_orderstates` ');
		$StatutWhiteList = $db->loadAssocList('order_status_code');
		// new product is statut N
		$StatutWhiteList['N'] = Array ( 'order_status_id' => 0 , 'order_status_code' => 'N' , 'order_stock_handle' => 'A');
		if(!array_key_exists($oldState,$StatutWhiteList) or !array_key_exists($newState,$StatutWhiteList)) {
			vmError('The workflow for '.$newState.' or  '.$oldState.' is unknown, take a look on model/orders function handleStockAfterStatusChanged','Can\'t process workflow, contact the shopowner. Status is '.$newState);
			return ;
		}

		// P 	Pending
		// C 	Confirmed
		// X 	Cancelled
		// R 	Refunded
		// S 	Shipped
		// N 	New or coming from cart
		//  TO have no product setted as ordered when added to cart simply delete 'P' FROM array Reserved
		// don't set same values in the 2 arrays !!!
		// stockOut is in normal case shipped product
		//order_stock_handle
		// 'A' : stock Available
		// 'O' : stock Out
		// 'R' : stock reserved
		// the status decreasing real stock ?
		// $stockOut = array('S');
		if ($StatutWhiteList[$newState]['order_stock_handle'] == 'O') $isOut = 1;
		else $isOut = 0;
		if ($StatutWhiteList[$oldState]['order_stock_handle'] == 'O') $wasOut = 1;
		else $wasOut = 0;

		// Stock change ?
		if ($isOut && !$wasOut)     $product_in_stock = '-';
		else if ($wasOut && !$isOut ) $product_in_stock = '+';
		else $product_in_stock = '=';

		// the status increasing reserved stock(virtual Stock = product_in_stock - product_ordered)
		// $Reserved =  array('P','C');
		if ($StatutWhiteList[$newState]['order_stock_handle'] == 'R') $isReserved = 1;
		else $isReserved = 0;
		if ($StatutWhiteList[$oldState]['order_stock_handle'] == 'R') $wasReserved = 1;
		else $wasReserved = 0;

		if ($isReserved && !$wasReserved )     $product_ordered = '+';
		else if (!$isReserved && $wasReserved ) $product_ordered = '-';
		else $product_ordered = '=';

		$diff = $tableOrderItems->product_quantity - $quantity;

		if(!empty($diff) and $product_in_stock == '=' and $product_ordered == '='){

			if($isReserved){
				if($diff>0)	$product_ordered = '+'; else $product_ordered = '-';
			}
			if($isOut){
				if($diff>0)	$product_in_stock = '+'; else $product_ordered = '-';
			}
			$quantity = abs($diff);
		}

		//Here trigger plgVmGetProductStockToUpdateByCustom
		$productModel = VmModel::getModel('product');

		if (!empty($tableOrderItems->product_attribute)) {
			if(!class_exists( 'VirtueMartModelCustomfields' )) require(VMPATH_ADMIN.DS.'models'.DS.'customfields.php');
			$virtuemart_product_id = $tableOrderItems->virtuemart_product_id;
			$product_attributes = json_decode( $tableOrderItems->product_attribute, true );
			foreach( $product_attributes as $virtuemart_customfield_id => $param ) {
				if($param) {
					if(is_array( $param )) {
						reset( $param );
						$customfield_id = key( $param );
					} else {
						$customfield_id = $param;
					}

					if($customfield_id) {
						if($productCustom = VirtueMartModelCustomfields::getCustomEmbeddedProductCustomField( $customfield_id )) {
							if($productCustom->field_type == "E") {
								if(!class_exists( 'vmCustomPlugin' )) require(VMPATH_PLUGINLIBS.DS.'vmcustomplugin.php');
								JPluginHelper::importPlugin( 'vmcustom' );
								$dispatcher = JDispatcher::getInstance();
								$dispatcher->trigger( 'plgVmGetProductStockToUpdateByCustom', array(&$tableOrderItems, $param, $productCustom) );
							}
						}
					}
				}
			}
		}

		// we can have more then one product in case of pack
		// in case of child, ID must be the child ID
		// TO DO use $prod->amount change for packs(eg. 1 computer and 2 HDD)
		if (is_array($tableOrderItems))	{
			foreach ($tableOrderItems as $prod ) {
				$productModel->updateStockInDB($prod, $quantity,$product_in_stock,$product_ordered);
			}
		} else {
			$productModel->updateStockInDB($tableOrderItems, $quantity,$product_in_stock,$product_ordered);
		}


	}

	/**
	 * Create the ordered item records
	 *
	 * @author Oscar van Eijk
	 * @author Kohl Patrick
	 * @param integer $_id integer Order ID
	 * @param object $_cart array The cart data
	 * @return boolean True on success
	 */
	private function _createOrderLines($virtuemart_order_id, $cart)
	{
		$_orderItems = $this->getTable('order_items');

		foreach ($cart->products  as $priceKey=>$product) {

			if(!empty($product->customProductData)){
				$_orderItems->product_attribute = vmJsApi::safe_json_encode($product->customProductData);
			} else {
				$_orderItems->product_attribute = '';
			}


			$_orderItems->virtuemart_order_item_id = null;
			$_orderItems->virtuemart_order_id = $virtuemart_order_id;

			$_orderItems->virtuemart_vendor_id = $product->virtuemart_vendor_id;
			$_orderItems->virtuemart_product_id = $product->virtuemart_product_id;
			$_orderItems->order_item_sku = $product->product_sku;
			$_orderItems->order_item_name = $product->product_name;
			$_orderItems->product_quantity = $product->quantity;
			$_orderItems->product_item_price = $product->allPrices[$product->selectedPrice]['basePriceVariant'];
			$_orderItems->product_basePriceWithTax = $product->allPrices[$product->selectedPrice]['basePriceWithTax'];

			//$_orderItems->product_tax = $_cart->pricesUnformatted[$priceKey]['subtotal_tax_amount'];
			$_orderItems->product_tax = $product->allPrices[$product->selectedPrice]['taxAmount'];
			$_orderItems->product_final_price = $product->allPrices[$product->selectedPrice]['salesPrice'];
			$_orderItems->product_subtotal_discount = $product->allPrices[$product->selectedPrice]['subtotal_discount'];
			$_orderItems->product_subtotal_with_tax =  $product->allPrices[$product->selectedPrice]['subtotal_with_tax'];
			$_orderItems->product_priceWithoutTax = $product->allPrices[$product->selectedPrice]['priceWithoutTax'];
			$_orderItems->product_discountedPriceWithoutTax = $product->allPrices[$product->selectedPrice]['discountedPriceWithoutTax'];
			$_orderItems->order_status = 'P';
			if (!$_orderItems->check()) {
				return false;
			}

			// Save the record to the database
			if (!$_orderItems->store()) {
				return false;
			}
			$product->virtuemart_order_item_id = $_orderItems->virtuemart_order_item_id;

			$this->handleStockAfterStatusChangedPerProduct( $_orderItems->order_status,'N',$_orderItems,$_orderItems->product_quantity);

		}

		return true;

	}
/**
	 * Create the ordered item records
	 *
	 * @author Valerie Isaksen
	 * @param integer $_id integer Order ID
	 * @param object $_cart array The cart data
	 * @return boolean True on success
	 */
	private function _createOrderCalcRules($order_id, $_cart)
	{

		$productKeys = array_keys($_cart->products);

		$calculation_kinds = array('DBTax','Tax','VatTax','DATax','Marge');

		foreach($productKeys as $key){
			foreach($calculation_kinds as $calculation_kind) {

				if(!isset($_cart->cartPrices[$key][$calculation_kind])) continue;
				$productRules = $_cart->cartPrices[$key][$calculation_kind];

				foreach($productRules as $rule){
					if(empty($rule[4])){
						continue;
					}
					$orderCalcRules = $this->getTable('order_calc_rules');
					$orderCalcRules->virtuemart_order_calc_rule_id= null;
					$orderCalcRules->virtuemart_calc_id= $rule[7];
					$orderCalcRules->virtuemart_order_item_id = $_cart->products[$key]->virtuemart_order_item_id;
					$orderCalcRules->calc_rule_name = $rule[0];
					$orderCalcRules->calc_amount =  0;
					$orderCalcRules->calc_result =  0;
					if ($calculation_kind == 'VatTax') {
						$orderCalcRules->calc_amount =  $_cart->cartPrices[$key]['taxAmount'];
						$orderCalcRules->calc_result =  $_cart->cartData['VatTax'][$rule[7]]['result'];
					}
					$orderCalcRules->calc_value = $rule[1];
					$orderCalcRules->calc_mathop = $rule[2];
					$orderCalcRules->calc_kind = $calculation_kind;
					$orderCalcRules->calc_currency = $rule[4];
					$orderCalcRules->calc_params = $rule[5];
					$orderCalcRules->virtuemart_vendor_id = $rule[6];
					$orderCalcRules->virtuemart_order_id = $order_id;

					if (!$orderCalcRules->check()) {
						vmdebug('_createOrderCalcRules check product rule ',$this);
						return false;
					}

					// Save the record to the database
					if (!$orderCalcRules->store()) {
						vmdebug('_createOrderCalcRules store product rule ',$this);
						return false;
					}
				}

			}
		}


		$Bill_calculation_kinds=array('DBTaxRulesBill', 'taxRulesBill', 'DATaxRulesBill');

		foreach($Bill_calculation_kinds as $calculation_kind) {

		    foreach($_cart->cartData[$calculation_kind] as $rule){
			    $orderCalcRules = $this->getTable('order_calc_rules');
			     $orderCalcRules->virtuemart_order_calc_rule_id = null;
				 $orderCalcRules->virtuemart_calc_id= $rule['virtuemart_calc_id'];
			     $orderCalcRules->calc_rule_name= $rule['calc_name'];
			     $orderCalcRules->calc_amount =  $_cart->cartPrices[$rule['virtuemart_calc_id'].'Diff'];
				 if ($calculation_kind == 'taxRulesBill' and !empty($_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['result'])) {
					$orderCalcRules->calc_result =  $_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['result'];
				 }
			     $orderCalcRules->calc_kind=$calculation_kind;
			     $orderCalcRules->calc_currency=$rule['calc_currency'];
			     $orderCalcRules->calc_value=$rule['calc_value'];
			     $orderCalcRules->calc_mathop=$rule['calc_value_mathop'];
			     $orderCalcRules->virtuemart_order_id=$order_id;
			     $orderCalcRules->calc_params=$rule['calc_params'];
				 $orderCalcRules->virtuemart_vendor_id = $rule['virtuemart_vendor_id'];
			     if (!$orderCalcRules->check()) {
				    return false;
			    }

			    // Save the record to the database
			    if (!$orderCalcRules->store()) {
				    return false;
			    }
		    }
		}

		if(!empty($_cart->virtuemart_paymentmethod_id)){

			if(empty($_cart->cartPrices['payment_calc_id'])){

			} else {
				$orderCalcRules = $this->getTable('order_calc_rules');
				$calcModel = VmModel::getModel('calc');
				$calcModel->setId($_cart->cartPrices['payment_calc_id']);
				$calc = $calcModel->getCalc();
				if(empty($calc->calc_currency) or empty($calc->calc_value)) {

				} else {
					$orderCalcRules->virtuemart_order_calc_rule_id = null;
					$orderCalcRules->virtuemart_calc_id = $calc->virtuemart_calc_id;
					$orderCalcRules->calc_kind = 'payment';
					$orderCalcRules->calc_rule_name = $calc->calc_name;
					$orderCalcRules->calc_amount = $_cart->cartPrices['paymentTax'];
					$orderCalcRules->calc_value = $calc->calc_value;
					$orderCalcRules->calc_mathop = $calc->calc_value_mathop;
					$orderCalcRules->calc_currency = $calc->calc_currency;
					$orderCalcRules->calc_params = $calc->calc_params;
					$orderCalcRules->virtuemart_vendor_id = $calc->virtuemart_vendor_id;
					$orderCalcRules->virtuemart_order_id = $order_id;
					if (!$orderCalcRules->check()) {

					} else {
						// Save the record to the database
						if (!$orderCalcRules->store()) {
							return false;
						}
					}
				}
			}
		}

		if(!empty($_cart->virtuemart_shipmentmethod_id)){

			if(empty($_cart->cartPrices['shipment_calc_id'])){

			} else {
				$orderCalcRules = $this->getTable('order_calc_rules');
				$calcModel = VmModel::getModel('calc');
				$calcModel->setId($_cart->cartPrices['shipment_calc_id']);
				$calc = $calcModel->getCalc();
				if(empty($calc->calc_currency) or empty($calc->calc_value)) {

				} else {
					$orderCalcRules->virtuemart_order_calc_rule_id = null;
					$orderCalcRules->virtuemart_calc_id = $calc->virtuemart_calc_id;
					$orderCalcRules->calc_kind = 'shipment';
					$orderCalcRules->calc_rule_name = $calc->calc_name;
					$orderCalcRules->calc_amount = $_cart->cartPrices['shipmentTax'];
					$orderCalcRules->calc_value = $calc->calc_value;
					$orderCalcRules->calc_mathop = $calc->calc_value_mathop;
					$orderCalcRules->calc_currency = $calc->calc_currency;
					$orderCalcRules->calc_params = $calc->calc_params;
					$orderCalcRules->virtuemart_vendor_id = $calc->virtuemart_vendor_id;
					$orderCalcRules->virtuemart_order_id = $order_id;
					if (!$orderCalcRules->check()) {
						return false;
					} else {
						// Save the record to the database
						if (!$orderCalcRules->store()) {
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Update the order history
	 *
	 * @author Oscar van Eijk
	 * @param $_id Order ID
	 * @param $_status New order status (default: P)
	 * @param $_notified 1 (default) if the customer was notified, 0 otherwise
	 * @param $_comment (Customer) comment, default empty
	 */
	public function _updateOrderHist($_id, $_status = 'P', $_notified = 0, $_comment = '')
	{
		$_orderHist = $this->getTable('order_histories');
		$oldOrderStatus = false;
		if(!empty($_id)){
			$db = JFactory::getDbo();
			$q = 'SELECT `order_status_code` FROM #__virtuemart_order_histories WHERE virtuemart_order_id="'.$_id.'" ORDER BY `created_on` DESC LIMIT 1';
			$db->setQuery($q);
			$oldOrderStatus = $db->loadResult();
		}

		if($oldOrderStatus==$_status) {
			$_orderHist->load($_id,'virtuemart_order_id');
		} else {
			$_orderHist->virtuemart_order_id = $_id;
			$_orderHist->order_status_code = $_status;
			//$_orderHist->date_added = date('Y-m-d G:i:s', time());
			$_orderHist->customer_notified = $_notified;
		}
		$_orderHist->comments = nl2br($_comment);
		$_orderHist->store();

	}

	/**
	 * Update the order item history
	 *
	 * @author Oscar van Eijk,kohl patrick
	 * @param $_id Order ID
	 * @param $_status New order status (default: P)
	 * @param $_notified 1 (default) if the customer was notified, 0 otherwise
	 * @param $_comment (Customer) comment, default empty
	 */
	private function _updateOrderItemHist($_id, $status = 'P', $notified = 1, $comment = '')
	{
		$_orderHist = $this->getTable('order_item_histories');
		$_orderHist->virtuemart_order_item_id = $_id;
		$_orderHist->order_status_code = $status;
		$_orderHist->customer_notified = $notified;
		$_orderHist->comments = $comment;
		$_orderHist->store();
	}

	/**
	 * Creates a standard order password
	 */
	 static public function genStdOrderPass(){
		if(!class_exists('vmCrypt'))
			require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcrypt.php');
		 $chrs = "ABCDEFGHJKLMNPQRSTUVWXYZ";
		 $chrs.= "abcdefghijkmnopqrstuvwxyz";
		 $chrs.= "123456789";
		return 'p_'.vmCrypt::getToken(VmConfig::get('randOrderPw',8),$chrs);
	 }

	static public function genStdCreateInvoicePass(){
		if(!class_exists('vmCrypt'))
			require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcrypt.php');
		return vmCrypt::getToken(8);
	}

	/**
	 * Generate a unique ordernumber using getHumanToken, which is a random token
	 * with only upper case chars and without 0 and O to prevent missreadings
	 * @author Max Milbers
	 * @param integer $virtuemart_vendor_id For the correct count
	 * @return string A unique ordernumber
	 */
	static public function genStdOrderNumber($virtuemart_vendor_id=1, $length = 4){

		$db = JFactory::getDBO();

		$q = 'SELECT COUNT(1) FROM #__virtuemart_orders WHERE `virtuemart_vendor_id`="'.$virtuemart_vendor_id.'"';
		$db->setQuery($q);

		//We can use that here, because the order_number is free to set, the invoice_number must often follow special rules
		$c = $db->loadResult();
		$c = $c + (int)VM_ORDER_OFFSET;

		if(!class_exists('vmCrypt'))
			require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcrypt.php');
		$str = vmCrypt::getHumanToken(VmConfig::get('randOrderNr',$length)).'0'.$c;

		return $str;
	}

	/**
	 * Generate a unique ordernumber. This is done in a similar way as VM1.1.x, although
	 * the reason for this is unclear to me :-S
	 * @deprecated
	 * @param integer $uid The user ID. Defaults to 0 for guests
	 * @return string A unique ordernumber
	 */
	static public function generateOrderNumber($uid = 0,$length=5, $virtuemart_vendor_id=1) {
		return self::genStdOrderNumber($virtuemart_vendor_id, $length);
	}


	/**
	 * Notifies the customer that the Order Status has been changed
	 *
	 * @author Christopher Roussel, Valérie Isaksen, Max Milbers
	 *
	 */
	public function notifyCustomer($virtuemart_order_id, $newOrderData = 0 ) {

		if (isset($newOrderData['customer_notified']) && $newOrderData['customer_notified']==0) {
		    return true;
		}
		if(!class_exists('shopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');

		//Important, the data of the order update mails, payments and invoice should
		//always be in the database, so using getOrder is the right method

		$vendorModel = VmModel::getModel('vendor');

		//Lets set the language to the Shop default
		$prevLang = VmConfig::$vmlangTag;
		shopFunctionsF::loadOrderLanguages(VmConfig::$jDefLangTag);
		$order = $this->getOrder($virtuemart_order_id);


		$vars['orderDetails']=$order;

		$payment_name = $shipment_name='';
		if (!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');

		JPluginHelper::importPlugin('vmshipment');
		JPluginHelper::importPlugin('vmpayment');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmOnShowOrderFEShipment',array(  $order['details']['BT']->virtuemart_order_id, $order['details']['BT']->virtuemart_shipmentmethod_id, &$shipment_name));
		$returnValues = $dispatcher->trigger('plgVmOnShowOrderFEPayment',array(  $order['details']['BT']->virtuemart_order_id, $order['details']['BT']->virtuemart_paymentmethod_id, &$payment_name));
		$order['shipmentName']=$shipment_name;
		$order['paymentName']=$payment_name;
		if($newOrderData!=0){	//We do not really need that
			$vars['newOrderData'] = (array)$newOrderData;
		}

		//$vars['includeComments'] = vRequest::getVar('customer_notified', array());
		//I think this is misleading, I think it should always ask for example $vars['newOrderData']['doVendor'] directly
		//Using this function garantue us that it is always there. If the vendor should be informed should be done by the plugins
		//We may add later something to the method, defining this better
		$vars['url'] = 'url';
		if(!isset($newOrderData['doVendor'])) $vars['doVendor'] = false; else $vars['doVendor'] = $newOrderData['doVendor'];

		$invoice = $this->createInvoiceByOrder($order);
		if($invoice){
			$vars['mediaToSend'][] = $invoice;
		}

		$virtuemart_vendor_id = $order['details']['BT']->virtuemart_vendor_id;

		$vendorEmail = array();
		$vendorEmail[] = $vars['vendorEmail'] = $vendorModel->getVendorEmail($virtuemart_vendor_id);
		$addVendorEmails = VmConfig::get('addVendorEmail','');
		if (!empty($addVendorEmails)) $vendorEmail = array_merge($vendorEmail,explode(';',$addVendorEmails));

		$vendor = $vendorModel->getVendor($virtuemart_vendor_id);
		$vars['vendor'] = $vendor;

		//Mail for vendor
		$orderstatusForVendorEmail = VmConfig::get('email_os_v',array('U','C','R','X'));
		if(!is_array($orderstatusForVendorEmail)) $orderstatusForVendorEmail = array($orderstatusForVendorEmail);

		if ( in_array((string)$order['details']['BT']->order_status,$orderstatusForVendorEmail)){
			//shopFunctionsF::loadOrderLanguages(VmConfig::$jDefLangTag);
			//VmConfig::setLanguageByTag(VmConfig::$jDefLangTag);
			$view = shopFunctionsF::prepareViewForMail('invoice', $vars);
			$res = shopFunctionsF::sendVmMail( $view, $vendorEmail, TRUE );
		}

		// Send the email
		//$res = shopFunctionsF::renderMail('invoice', $order['details']['BT']->email, $vars, null,$vars['doVendor'],$this->useDefaultEmailOrderStatus);
		$sendMail = false;
		if(!$this->useDefaultEmailOrderStatus and isset($vars['newOrderData']['customer_notified']) and $vars['newOrderData']['customer_notified']==1){
			$sendMail = true;
		} else {
			$orderstatusForShopperEmail = VmConfig::get('email_os_s',array('U','C','S','R','X'));
			if(!is_array($orderstatusForShopperEmail)) $orderstatusForShopperEmail = array($orderstatusForShopperEmail);
			if ( in_array((string) $vars['orderDetails']['details']['BT']->order_status,$orderstatusForShopperEmail) ){
				$sendMail = true;
				vmdebug('renderMail by default orderstati');
			}
		}

		shopFunctionsF::loadOrderLanguages(VmConfig::$vmlangTag);
		$res = true;
		if($sendMail){
			if(!empty($vars['orderDetails']['details']) and !empty($vars['orderDetails']['details']['BT']->order_language)) {
				$orderLang = $vars['orderDetails']['details']['BT']->order_language;
				shopFunctionsF::loadOrderLanguages($orderLang);
				$vendor = $vendorModel->getVendor($virtuemart_vendor_id);
				$vars['vendor'] = $vendor;
				$vars['orderDetails'] = $this->getOrder($virtuemart_order_id);
			}

			$shopperEmail = array();
			$shopperEmailFields = VmConfig::get('email_sf_s',array('email'));
			foreach ($shopperEmailFields as $field) {
				if (!empty($order['details']['BT']->$field)) $shopperEmail[] = $order['details']['BT']->$field;
			}
			if (count($shopperEmail) < 1) $shopperEmail[] = $order['details']['BT']->email;

			$view = shopFunctionsF::prepareViewForMail('invoice', $vars);
			$res = shopFunctionsF::sendVmMail( $view, $shopperEmail, false );

		}

		if(is_object($res) or !$res){
			$string = 'COM_VIRTUEMART_NOTIFY_CUSTOMER_ERR_SEND';
			vmdebug('notifyCustomer function shopFunctionsF::renderMail throws JException');
			$res = 0;
		} //We need this, to prevent that a false alert is thrown.
		else if ($res and $res!=-1) {
			$string = 'COM_VIRTUEMART_NOTIFY_CUSTOMER_SEND_MSG';
		}

		if($res!=-1){
			vmLanguage::setLanguageByTag(vmLanguage::$jSelLangTag);
			vmInfo( vmText::_($string,false).' '.$order['details']['BT']->first_name.' '.$order['details']['BT']->last_name. ', '.$order['details']['BT']->email);
		}

		//quicknDirty to prevent that an email is sent twice
		$app = JFactory::getApplication();
		if($app->isSite()){
			if (!class_exists('VirtueMartCart'))
				require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
			$cart = VirtueMartCart::getCart();
			$cart->customer_notified = true;
		}
		VmLanguage::setLanguageByTag($prevLang);
		return true;
	}

	public function createInvoiceByOrder($order){

		if(!isset($order['details']['BT'])) return false;

		$inv = false;
		// florian : added if pdf invoice are enabled
		$invoiceNumberDate = array();
		if ($this->createInvoiceNumber($order['details']['BT'], $invoiceNumberDate )) {

			$pdfInvoice = (int)VmConfig::get('pdf_invoice', 0); // backwards compatible
			$force_create_invoice=vRequest::getInt('create_invoice', -1);
			//TODO we need an array of orderstatus
			if ( VirtueMartModelInvoice::needInvoiceByOrderstatus($order['details']['BT']->order_status)  or $pdfInvoice==1  or $force_create_invoice==1 ){
				if (!shopFunctions::InvoiceNumberReserved($invoiceNumberDate[0])) {
					if(!class_exists('VirtueMartControllerInvoice')) require( VMPATH_SITE.DS.'controllers'.DS.'invoice.php' );
					$controller = new VirtueMartControllerInvoice( array(
					'model_path' => VMPATH_SITE.DS.'models',
					'view_path' => VMPATH_SITE.DS.'views'
					));
					$lTag = VmConfig::$vmlangTag;

					$inv = $controller->getInvoicePDF($order);
					vmLanguage::setLanguageByTag($lTag);
				}
			}

		}
		return $inv;
	}

	/**
	 * Retrieve the details for an order line item.
	 *
	 * @author RickG
	 * @param string $orderId Order id number
	 * @param string $orderLineId Order line item number
	 * @return object Object containing the order item details.
	 */
	function getOrderLineDetails($orderId, $orderLineId) {
		$table = $this->getTable('order_items');
		if ($table->load((int)$orderLineId)) {
			return $table;
		}
		else {
			$table->reset();
			$table->virtuemart_order_id = $orderId;
			return $table;
		}
	}


	/**
	 * Save an order line item.
	 *
	 * @author RickG
	 * @return boolean True of remove was successful, false otherwise
	 */
	function saveOrderLineItem($data) {

		if(!vmAccess::manager('orders.edit')) {
			return false;
		}

		$table = $this->getTable('order_items');

		//Done in the table already
		if (!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
		JPluginHelper::importPlugin('vmshipment');
		$_dispatcher = JDispatcher::getInstance();
		$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderLineShipment',array( $data));
		foreach ($_returnValues as $_retVal) {
			if ($_retVal === false) {
				// Stop as soon as the first active plugin returned a failure status
				return;
			}
		}
		if (!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
		JPluginHelper::importPlugin('vmpayment');
		$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderLinePayment',array( $data));
		foreach ($_returnValues as $_retVal) {
			if ($_retVal === false) {
				// Stop as soon as the first active plugin returned a failure status
				return;
			}
		}
		$table->bindChecknStore($data);
		return true;

	}


	/**
	 * @author Max Milbers
	 *
	 * remove product from order item table
	 * @param $virtuemart_order_id Order to clear
	 * @param $auth
	 * @return boolean True of remove was successful, false otherwise
	 *
	 */
	function removeOrderItems ($virtuemart_order_id, $auth = true){

		if($auth and !vmAccess::manager('orders.edit')) {
			return false;
		}
		$q ='DELETE from `#__virtuemart_order_items` WHERE `virtuemart_order_id` = ' .(int) $virtuemart_order_id;
		$db = JFactory::getDBO();
		$db->setQuery($q);

		$ok = true;
		if ($db->execute() === false) {
			vmError($db->getError());
			$ok = true;
		}

		$q ='DELETE from `#__virtuemart_order_calc_rules` WHERE `virtuemart_order_id` = ' .(int) $virtuemart_order_id;
		$db = JFactory::getDBO();
		$db->setQuery($q);

		if ($db->execute() === false) {
			vmError($db->getError());
			$ok = true;
		}

		return $ok;
	}

	/**
	 * Remove an order line item.
	 *
	 * @author RickG
	 * @param string $orderLineId Order line item number
	 * @return boolean True of remove was successful, false otherwise
	 */
	function removeOrderLineItem($orderLineId) {

		if(!vmAccess::manager('orders.edit')) {
			return false;
		}

		$item = $this->getTable('order_items');
		if (!$item->load($orderLineId)) {
			return false;
		}

		//Why should the stock change, when the order is deleted?
		//answer : when creating an order (P) the stock is reserved
		$this->handleStockAfterStatusChangedPerProduct('X', $item->order_status, $item,$item->product_quantity);

		//take data for history
		$data = $item->getProperties();

		if (!VmConfig::get('ordersAddOnly',false) and $item->delete($orderLineId)) {
			$data['action'] = 'deleted';
			$tableHist = $this->getTable('order_item_histories');

			$tableHist->bindChecknStore($data);

			$q = "DELETE FROM `#__virtuemart_order_calc_rules`
			WHERE `virtuemart_order_item_id`=".$orderLineId;
			$this->_db->setQuery($q);
			$this->_db->execute();//*/
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Delete all record ids selected
	 *
	 * @author Max Milbers
	 * @author Patrick Kohl
	 * @return boolean True is the delete was successful, false otherwise.
	 */
	public function remove($ids) {

		if(!vmAccess::manager('orders.delete')) {
			return false;
		}

		$invM = VmModel::getModel('invoice');
		$table = $this->getTable($this->_maintablename);
		$removedOrderMsgs=array();
		foreach($ids as $id) {

			$order = $this->getOrder($id);

			$invoice= $invM->hasInvoice($id);
			if ($invoice) {
				$removedOrderMsgs [$order['details']['BT']->order_number]= 'COM_VIRTUEMART_ORDER_NOT_ALLOWED_TO_DELETE';
				continue;
			}

			if(!empty($order['items'])){
				foreach($order['items'] as $it){
					$this->removeOrderLineItem($it->virtuemart_order_item_id);
				}
			}

			$this->removeOrderItems($id);

			$q = "DELETE FROM `#__virtuemart_order_histories`
			WHERE `virtuemart_order_id`=".$id;
			$this->_db->setQuery($q);
			$this->_db->execute();

			$q = "DELETE FROM `#__virtuemart_order_calc_rules`
			WHERE `virtuemart_order_id`=".$id;
			$this->_db->setQuery($q);
			$this->_db->execute();

			// rename invoice number by adding the date, and update the invoice table
			$this->renameInvoice($id );

			if (!$table->delete((int)$id)) {
				$removedOrderMsgs [$order['details']['BT']->order_number] = 'COM_VIRTUEMART_ORDER_PB_WHILE_DELETING';
			}
			$removedOrderMsgs [$order['details']['BT']->order_number] =true;
		}
		return $removedOrderMsgs;

	}


	/** Update order head record
	*
	* @author Ondřej Spilka
	* @author Maik Künnemann
	* @return boolean True is the update was successful, otherwise false.
	*/ 
	public function UpdateOrderHead($virtuemart_order_id, $_orderData) {

		if(!vmAccess::manager('orders.edit')){
			return false;
		}
		$orderTable = $this->getTable('orders');
		$orderTable->load($virtuemart_order_id);

		if (!$orderTable->bindChecknStore($_orderData, true)){
			return false;
		}

		$_userInfoData = array();

		if(!class_exists('VirtueMartModelUserfields')) require(VMPATH_ADMIN.DS.'models'.DS.'userfields.php');

		$_userFieldsModel = VmModel::getModel('userfields');

		//bill to
		$_userFieldsCart = $_userFieldsModel->getUserFields('account'
			, array('delimiters'=>true, 'captcha'=>true)
			, array('username', 'password', 'password2', 'user_is_vendor')
			);

		$_userFieldsBT = $_userFieldsModel->getUserFields('cart'
			, array('delimiters'=>true, 'captcha'=>true)
			, array('username', 'password', 'password2', 'user_is_vendor')
		);

		$_userFieldsBT = array_merge((array)$_userFieldsBT,(array)$_userFieldsCart);

		foreach ($_userFieldsBT as $_fld) {
			$_name = $_fld->name;
			if(isset( $_orderData["BT_{$_name}"])){

				$_userInfoData[$_name] = $_orderData["BT_{$_name}"];
			}
		}

		$_userInfoData['virtuemart_order_id'] = $virtuemart_order_id;
		$_userInfoData['address_type'] = 'BT';

		$order_userinfosTable = $this->getTable('order_userinfos');
			$order_userinfosTable->load($virtuemart_order_id, 'virtuemart_order_id'," AND address_type='BT'");
		if (!$order_userinfosTable->bindChecknStore($_userInfoData, true)){
			return false;
		}

		//ship to
		$_userFieldsST = $_userFieldsModel->getUserFields('account'
			, array('delimiters'=>true, 'captcha'=>true)
			, array('username', 'password', 'password2', 'user_is_vendor')
			);

		$_userInfoData = array();
		foreach ($_userFieldsST as $_fld) {
			$_name = $_fld->name;
			if(isset( $_orderData["ST_{$_name}"])){

				$_userInfoData[$_name] = $_orderData["ST_{$_name}"];
			}
		}

		$_userInfoData['virtuemart_order_id'] = $virtuemart_order_id;
		$_userInfoData['address_type'] = 'ST';

		$order_userinfosTable = $this->getTable('order_userinfos');
			$order_userinfosTable->load($virtuemart_order_id, 'virtuemart_order_id'," AND address_type='ST'");
		if (!$order_userinfosTable->bindChecknStore($_userInfoData, true)){
			return false;
		}

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$dispatcher = JDispatcher::getInstance();

		if (!class_exists ('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		// Update Payment Method
		if($_orderData['old_virtuemart_paymentmethod_id'] != $_orderData['virtuemart_paymentmethod_id']) {

			$db = JFactory::getDBO();
			$db->setQuery( 'SELECT `payment_element` FROM `#__virtuemart_paymentmethods` , `#__virtuemart_orders`
					WHERE `#__virtuemart_paymentmethods`.`virtuemart_paymentmethod_id` = `#__virtuemart_orders`.`virtuemart_paymentmethod_id` AND `virtuemart_order_id` = ' . $virtuemart_order_id );
			$paymentTable = '#__virtuemart_payment_plg_'. $db->loadResult();

			$db->setQuery("DELETE from `". $paymentTable ."` WHERE `virtuemart_order_id` = " . $virtuemart_order_id);
			if ($db->execute() === false) {
				vmError($db->getError());
				return false;
			} else {
				JPluginHelper::importPlugin('vmpayment');
			}

		}

		// Update Shipment Method

		if($_orderData['old_virtuemart_shipmentmethod_id'] != $_orderData['virtuemart_shipmentmethod_id']) {

			$db->setQuery( 'SELECT `shipment_element` FROM `#__virtuemart_shipmentmethods` , `#__virtuemart_orders`
					WHERE `#__virtuemart_shipmentmethods`.`virtuemart_shipmentmethod_id` = `#__virtuemart_orders`.`virtuemart_shipmentmethod_id` AND `virtuemart_order_id` = ' . $virtuemart_order_id );
			$shipmentTable = '#__virtuemart_shipment_plg_'. $db->loadResult();

			$db->setQuery("DELETE from `". $shipmentTable ."` WHERE `virtuemart_order_id` = " . $virtuemart_order_id);
			if ($db->execute() === false) {
				vmError($db->getError());
				return false;
			} else {
				JPluginHelper::importPlugin('vmshipment');
			}

		}

		if (!class_exists('VirtueMartCart'))
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		$cart = VirtueMartCart::getCart();
		$cart->virtuemart_paymentmethod_id = $_orderData['virtuemart_paymentmethod_id'];
		$cart->virtuemart_shipmentmethod_id = $_orderData['virtuemart_shipmentmethod_id'];

		$order['order_status'] = $order['details']['BT']->order_status;
		$order['customer_notified'] = 0;
		$order['comments'] = '';

		$returnValues = $dispatcher->trigger('plgVmConfirmedOrder', array($cart, $order));

		return true;
	}


	/** Create empty order head record from admin only
	*
	* @author Ondřej Spilka
	* @return ID of the newly created order
	*/ 
	public function CreateOrderHead()
	{

		$usrid = 0;
		$_orderData = new stdClass();

		$_orderData->virtuemart_order_id = null;
		$_orderData->virtuemart_user_id = 0;
		$_orderData->virtuemart_vendor_id = 1; //TODO

		$_orderData->order_total = 0;
		$_orderData->order_salesPrice = 0;
		$_orderData->order_billTaxAmount = 0;
		$_orderData->order_billDiscountAmount = 0;
		$_orderData->order_discountAmount = 0;
		$_orderData->order_subtotal = 0;
		$_orderData->order_tax = 0;
		$_orderData->order_shipment = 0;
		$_orderData->order_shipment_tax = 0;
		$_orderData->order_payment = 0;
		$_orderData->order_payment_tax = 0;

		$_orderData->order_discount = 0;
		$_orderData->order_status = 'P';
		$_orderData->order_currency = $this->getVendorCurrencyId($_orderData->virtuemart_vendor_id);

		$_orderData->virtuemart_paymentmethod_id = vRequest::getInt('virtuemart_paymentmethod_id');
		$_orderData->virtuemart_shipmentmethod_id = vRequest::getInt('virtuemart_shipmentmethod_id');

		//$_orderData->customer_note = '';
		$_orderData->ip_address = $_SERVER['REMOTE_ADDR'];

		$_orderData->order_number ='';
		JPluginHelper::importPlugin('vmshopper');
		$dispatcher = JDispatcher::getInstance();
		$_orderData->order_number = $this->genStdOrderNumber($_orderData->virtuemart_vendor_id);
		$_orderData->order_pass = $this->genStdOrderPass();
		$_orderData->order_create_invoice_pass = $this->genStdCreateInvoicePass();

		$orderTable =  $this->getTable('orders');
		$orderTable -> bindChecknStore($_orderData);

		$db = JFactory::getDBO();
		$_orderID = $db->insertid();

		$_usr  = JFactory::getUser();
		if (!$this->_writeUserInfo($_orderID, $_usr, array())) {
			vmError('Problem writing user info to order');
		}

		$orderModel = VmModel::getModel('orders');
		$order= $orderModel->getOrder($_orderID);

		$dispatcher = JDispatcher::getInstance();

		JPluginHelper::importPlugin('vmcustom');
		JPluginHelper::importPlugin('vmshipment');
		JPluginHelper::importPlugin('vmpayment');
		if (!class_exists('VirtueMartCart'))
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		$cart = VirtueMartCart::getCart();
		$returnValues = $dispatcher->trigger('plgVmConfirmedOrder', array($cart, $order));

		return $_orderID;
	}


	/**
	* returns true if an invoice number has been created
	* returns false if an invoice number has not been created  due to some configuration parameters
	*/
	function createInvoiceNumber($orderDetails, &$invoiceNumber){

		$invM = VmModel::getModel('invoice');
		return $invM->createNewInvoiceNumber($orderDetails, $invoiceNumber);
	}

	static function getInvoiceNumber($virtuemart_order_id) {
		VmModel::getModel('invoice');
		return VirtueMartModelInvoice::getInvoiceEntry($virtuemart_order_id, true , '`invoice_number`' );
	}

	/** Rename Invoice  (when an order is deleted)
	 *
	 * @author Valérie Isaksen
	 * @author Max Milbers
	 * @param $order_id Id of the order
	 * @return boolean true if deleted successful, false if there was a problem
	 */
	function renameInvoice($order_id) {
		$invM = VmModel::getModel('invoice');
		return $invM->renameInvoice($order_id);
	}

	/** Delete Invoice when an item is updated
	 *
	 * @author Valérie Isaksen
	 * @param $order_id Id of the order
	 * @return boolean true if deleted successful, false if there was a problem
	 */
	function deleteInvoice($order_id ) {
		$invM = VmModel::getModel('invoice');
		return $invM->createReferencedInvoiceNumber($order_id);
	}

}

// No closing tag

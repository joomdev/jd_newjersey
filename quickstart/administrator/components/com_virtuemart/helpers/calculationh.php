<?php

/**
 * Calculation helper class
 *
 * This class provides the functions for the calculations
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2010 - 2015 VirtueMart Team and the authors. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class calculationHelper {


	protected $_db;
	protected $_shopperGroupId;
	var $_cats;
	protected $_now;
	protected $_nullDate;

	protected $_debug;
	protected $_manufacturerId;
	public $_deliveryCountry;
	public $_deliveryState;
	public $_currencyDisplay;
	var $_cart = null;

	var $productPrices;

	/* @deprecated */
	public $_amount = 1;
	public $_amountCart = 0.0;

	public $productVendorId;
	public $productCurrency;
	public $product_tax_id = 0;
	public $product_discount_id = 0;
	public $product_marge_id = 0;
	public $vendorCurrency = 0;
	public $inCart = FALSE;
	private $checkAutomaticSelected = false;
	protected $exchangeRateVendor = 0;
	protected $exchangeRateShopper = 0;
	protected $_internalDigits = 9;
	protected $_revert = false;
	static $_instance;


	/** Constructor,... sets the actual date and current currency
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @author Geraint
	 */
	private function __construct() {
		$this->_db = JFactory::getDBO();
		$this->_app = JFactory::getApplication();
		//$this->_cart =& VirtuemartCart::getCart();
		//We store in UTC and use here of course also UTC
		$jnow = JFactory::getDate();
		$this->_now = $jnow->toSQL();
		$this->_nullDate = $this->_db->getNullDate();

		$this->productVendorId = 1;

		if (!class_exists('CurrencyDisplay')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		$this->_currencyDisplay = CurrencyDisplay::getInstance();
		$this->_debug = false;

		if(!empty($this->_currencyDisplay->_vendorCurrency)){
			$this->vendorCurrency = $this->_currencyDisplay->_vendorCurrency;
			$this->vendorCurrency_code_3 = $this->_currencyDisplay->_vendorCurrency_code_3;
			$this->vendorCurrency_numeric = $this->_currencyDisplay->_vendorCurrency_numeric;
		}

		$this->setShopperGroupIds();

		$this->setCountryState();
		$this->setVendorId($this->productVendorId);

		$this->rules['Marge'] = array();
		$this->rules['Tax'] 	= array();
		$this->rules['VatTax'] 	= array();
		$this->rules['DBTax'] = array();
		$this->rules['DATax'] = array();

		//round only with internal digits
		$this->_roundindig = VmConfig::get('roundindig',FALSE);
	}

	static public function getInstance() {
		if (!is_object(self::$_instance)) {
			self::$_instance = new calculationHelper();
			//vmdebug('Created new Calculator Instance');
		} else {
			//We store in UTC and use here of course also UTC
			$jnow = JFactory::getDate();
			self::$_instance->_now = $jnow->toSQL();
		}
		return self::$_instance;
	}

	public function setVendorCurrency($id) {
		$this->vendorCurrency = $id;
	}

	//static $allrules= array();
	var $allrules= array();
	public function setVendorId($id){

		if(empty($this->_calcModel)){
			$this->_calcModel = VmModel::getModel('calc');
		}
		$this->productVendorId = (int)$id;
		if(empty($this->allrules[$this->productVendorId])){
			$epoints = array("'Marge'","'Tax'","'VatTax'","'DBTax'","'DATax'");
			$this->allrules[$this->productVendorId]['Marge'] = array();
			$this->allrules[$this->productVendorId]['Tax'] 	= array();
			$this->allrules[$this->productVendorId]['VatTax'] 	= array();
			$this->allrules[$this->productVendorId]['DBTax'] = array();
			$this->allrules[$this->productVendorId]['DATax'] = array();

			$select = 'SELECT c.*';
			$q = ' FROM #__virtuemart_calcs as c ';
			$shopperGrpJoin = '';
			if(!empty($this->_shopperGroupId) and count($this->_shopperGroupId)>0){
				//$select .= ', cs.virtuemart_shoppergroup_id';
				$q .= ' LEFT JOIN #__virtuemart_calc_shoppergroups as cs ON cs.virtuemart_calc_id=c.virtuemart_calc_id ';
				$shopperGrpJoin = "\n AND (";
				foreach($this->_shopperGroupId as $gr){
					$shopperGrpJoin .= ' virtuemart_shoppergroup_id = '.(int)$gr. ' OR';
				}
				$shopperGrpJoin .=' (virtuemart_shoppergroup_id) IS NULL) ';
			}

			$countryGrpJoin = '';
			if(!empty($this->_deliveryCountry)){
				//$select .= ', cc.virtuemart_country_id';
				$q .= ' LEFT JOIN #__virtuemart_calc_countries as cc ON cc.virtuemart_calc_id=c.virtuemart_calc_id ';
				$countryGrpJoin = "\n".' AND ( virtuemart_country_id = "'.(int)$this->_deliveryCountry.'" OR (virtuemart_country_id) IS NULL) ';
			}

			$stateGrpJoin = '';
			if(!empty($this->_deliveryState)){
				//$select .= ', cst.virtuemart_state_id';
				$q .= ' LEFT JOIN #__virtuemart_calc_states as cst ON cst.virtuemart_calc_id=c.virtuemart_calc_id ';
				$stateGrpJoin = "\n".' AND ( virtuemart_state_id = "'.(int)$this->_deliveryState.'" OR (virtuemart_state_id) IS NULL) ';
			}

			$q .= ' WHERE `calc_kind` IN (' . implode(",",$epoints). ' )
                AND `published`="1"
                AND (`virtuemart_vendor_id`="' . $id . '" OR `shared`="1" )
				AND ( publish_up = "' . $this->_db->escape($this->_nullDate) . '" OR publish_up <= "' . $this->_db->escape($this->_now) . '" )
				AND ( publish_down = "' . $this->_db->escape($this->_nullDate) . '" OR publish_down >= "' . $this->_db->escape($this->_now) . '" )';

			$q .= $shopperGrpJoin . $countryGrpJoin . $stateGrpJoin;

			$this->_db->setQuery($select . $q);

			$allrules = $this->_db->loadAssocList();

			if(!$allrules) return;

			//By Maik, key of array is directly virtuemart_calc_id
			foreach ($allrules as $rule){
				$this->allrules[$this->productVendorId][$rule['calc_kind']][$rule['virtuemart_calc_id']] = $rule;
			}
			if ($this->_debug) vmdebug('Calculation rules',$select . $q, $allrules);
		}

	}

	public function getCartPrices() {
		return $this->_cart->cartPrices;
	}

	public function setCartPrices($cartPrices) {
		$this->_cart->cartPrices = $cartPrices;
	}

	public function setCartPricesMerge($cartPrices){

		foreach($cartPrices as $k=>$item){
			if(isset($this->_cart->cartPrices[$k]) and is_array($this->_cart->cartPrices[$k])){
				$this->_cart->cartPrices[$k] = array_merge($this->_cart->cartPrices[$k],$item);

			} else {
				$this->_cart->cartPrices[$k] = $item;
			}
		}
	}

	public function getCartData() {
		return $this->_cart->cartData;
	}

	protected function setShopperGroupIds($shopperGroupIds=0, $vendorId=1) {

		if (!empty($shopperGroupIds)) {
			$this->_shopperGroupId = $shopperGroupIds;
		} else {
			$user = JFactory::getUser();
			$this->_shopperGroupId = array();
			if (!empty($user->id)) {
				$this->_db->setQuery('SELECT `usgr`.`virtuemart_shoppergroup_id` FROM #__virtuemart_vmuser_shoppergroups as `usgr`
 										JOIN `#__virtuemart_shoppergroups` as `sg` ON (`usgr`.`virtuemart_shoppergroup_id`=`sg`.`virtuemart_shoppergroup_id`)
 										WHERE `usgr`.`virtuemart_user_id`="' . $user->id . '" AND `sg`.`virtuemart_vendor_id`="' . (int) $vendorId . '" ');
				$this->_shopperGroupId = $this->_db->loadColumn();
				if (empty($this->_shopperGroupId)) {

					$this->_db->setQuery('SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_shoppergroups
								WHERE `default`="'.($user->guest+1).'" AND `virtuemart_vendor_id`="' . (int) $vendorId . '"');
					$this->_shopperGroupId = $this->_db->loadColumn();
				}
			}
			if(!$this->_shopperGroupId) $this->_shopperGroupId = array();
			$shoppergroupmodel = VmModel::getModel('ShopperGroup');
			$site = JFactory::getApplication ()->isSite ();
			$shoppergroupmodel->appendShopperGroups($this->_shopperGroupId,$user,$site,$vendorId);
		}
	}

	protected function setCountryState() {

		if ($this->_app->isAdmin()) {
			$userModel = VmModel::getModel('user');
			$userDetails = $userModel->getUser();
			$virtuemart_userinfo_id_BT = $userModel->getBTuserinfo_id($userDetails->JUser->get('id'));
			if ($virtuemart_userinfo_id_BT) {
				$userFieldsArray = $userModel->getUserInfoInUserFields(NULL,'BT',$virtuemart_userinfo_id_BT,false);
				$userFieldsBT = $userFieldsArray[$virtuemart_userinfo_id_BT];
				if ($userFieldsBT) {
					if (isset($userFieldsBT['fields']['virtuemart_country_id']) and isset($userFieldsBT['fields']['virtuemart_country_id']['virtuemart_country_id'])) {
						$this->_deliveryCountry = $userFieldsBT['fields']['virtuemart_country_id']['virtuemart_country_id'];
					}
					if (isset($userFieldsBT['fields']['virtuemart_state_id']) and isset($userFieldsBT['fields']['virtuemart_state_id']['virtuemart_state_id'])) {
						$this->_deliveryState = $userFieldsBT['fields']['virtuemart_state_id']['virtuemart_state_id'];
					}
				}
			} else {
				if(!class_exists('VirtueMartModelVendor')) require(VMPATH_ADMIN.DS.'models'.DS.'vendor.php');
				$vendorModel = VmModel::getModel ('vendor');
				$vendorAddress = $vendorModel->getVendorAdressBT (1);
				if (isset( $vendorAddress->virtuemart_country_id)){
					$this->_deliveryCountry = $vendorAddress->virtuemart_country_id;
				}
				if (isset( $vendorAddress->virtuemart_state_id)) {
					$this->_deliveryState = $vendorAddress->virtuemart_state_id;
				}
			}
			return;
		}


		if(empty($this->_cart)){
			if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
			$this->_cart = VirtueMartCart::getCart();

		}

		if($this->_cart->BT===0){
			$this->_cart->prepareAddressFieldsInCart();
		}

		$stBased = VmConfig::get('taxSTbased',TRUE);
		if ($stBased) {
			$this->_deliveryCountry = (int)$this->_cart->getST('virtuemart_country_id');
		} else if (!empty($this->_cart->BT['virtuemart_country_id'])) {
			$this->_deliveryCountry = (int)$this->_cart->BT['virtuemart_country_id'];
		}

		if ($stBased) {
			$this->_deliveryState = (int)$this->_cart->getST('virtuemart_state_id');
		} else if (!empty($this->_cart->BT['virtuemart_state_id'])) {
			$this->_deliveryState = (int)$this->_cart->BT['virtuemart_state_id'];
		}
	}

	/** function to start the calculation, here it is for the product
	 *
	 * The function first gathers the information of the product (maybe better done with using the model)
	 * After that the function gatherEffectingRulesForProductPrice writes the queries and gets the ids of the rules which affect the product
	 * The function executeCalculation makes the actual calculation according to the rules
	 *
	 * @copyright Copyright (c) 2009 - 2015 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param int $product 	    The product
	 * @param int $catIds 		When the category is already determined, then it makes sense to pass it, if not the function does it for you
	 * @return int $prices		An array of the prices
	 * 							'basePrice'  		basePrice calculated in the shopcurrency
	 * 							'basePriceWithTax'	basePrice with Tax
	 * 							'discountedPrice'	before Tax
	 * 							'priceWithoutTax'	price Without Tax but with calculated discounts AFTER Tax. So it just shows how much the shopper saves, regardless which kind of tax
	 * 							'discountAmount'	the "you save X money"
	 * 							'salesPrice'		The final price, with all kind of discounts and Tax, except stuff that is only in the checkout
	 *
	 */
	public function getProductPrices(&$product, $variant=0.0, $amount=0) {

		$costPrice = 0;

		//temporary quantity
		if (empty($amount)) {
			$amount = 1;
		}
		$this->_amount = $amount;

		//We already have the productobject, no need for extra sql
		if (is_object($product)) {

			if(!empty($product->allPrices[$product->selectedPrice])){
				$prices = $this->productPrices = $product->allPrices[$product->selectedPrice];
				$costPrice = $prices['product_price'];
				$this->productCurrency = $prices['product_currency'];
				$override = $prices['override'];
				$product_override_price = $prices['product_override_price'];
				$product->product_tax_id = $this->product_tax_id = $prices['product_tax_id'];
				$this->product_discount_id = $prices['product_discount_id'];
			}
			$productVendorId = !empty($product->virtuemart_vendor_id)? $product->virtuemart_vendor_id:1;
			$this->setVendorId($productVendorId);

			$this->_cats = isset($product->categories)? $product->categories: array();
			$this->_product = $product;
			$this->_product->amount = $amount;	//temporary quantity
			//$this->productPrices = array();
			if(!isset($this->_product->quantity)) $this->_product->quantity = 1;

			$this->_manufacturerId = !empty($product->virtuemart_manufacturer_id) ? $product->virtuemart_manufacturer_id:0;
		} //Use it as productId
		else {
			vmError('getProductPrices no object given query time','getProductPrices no object given query time');
		}

		if(VmConfig::get('multix','none')!='none' and (empty($this->vendorCurrency) or $this->vendorCurrency!=$this->productVendorId)){
			static $vendorCurrencies = array();
			if(!isset($vendorCurrencies[$this->productVendorId])){
				$this->_db->setQuery('SELECT `vendor_currency` FROM #__virtuemart_vendors  WHERE `virtuemart_vendor_id`="' . $this->productVendorId . '" ');
				$vendorCurrencies[$this->productVendorId] = $this->_db->loadResult();
			}
			$this->vendorCurrency = $vendorCurrencies[$this->productVendorId];
		}



		//For Profit, margin, and so on
		$this->rules['Marge'] = $this->gatherEffectingRulesForProductPrice('Marge', $this->product_marge_id);

		$this->productPrices['costPrice'] = $costPrice;
		$basePriceShopCurrency = $this->roundInternal($this->_currencyDisplay->convertCurrencyTo((int) $this->productCurrency, $costPrice,true));
		//vmdebug('my pure $basePriceShopCurrency',$costPrice,$this->productCurrency,$basePriceShopCurrency);
		$basePriceMargin = $this->roundInternal($this->executeCalculation($this->rules['Marge'], $basePriceShopCurrency));
		$this->basePrice = $basePriceShopCurrency = $this->productPrices['basePrice'] = !empty($basePriceMargin) ? $basePriceMargin : $basePriceShopCurrency;

		if (!empty($variant)) {
			$variant = $this->roundInternal($this->_currencyDisplay->convertCurrencyTo((int) $this->productCurrency, doubleval($variant),true));
			$basePriceShopCurrency = $basePriceShopCurrency + $variant;
			$this->productPrices['basePrice'] = $this->productPrices['basePriceVariant'] = $basePriceShopCurrency;
		}
		if (empty($this->productPrices['basePrice'])) {
			return $this->fillVoidPrices($this->productPrices);
		}
		if (empty($this->productPrices['basePriceVariant'])) {
			$this->productPrices['basePriceVariant'] = $this->productPrices['basePrice'];
		}

		$this->rules['Tax'] = $this->gatherEffectingRulesForProductPrice('Tax', $this->product_tax_id);
		$this->productPrices['basePriceWithTax'] = $this->roundInternal($this->executeCalculation($this->rules['Tax'], $this->productPrices['basePrice'], true),'basePriceWithTax');

		$this->rules['VatTax'] = $this->gatherEffectingRulesForProductPrice('VatTax', $this->product_tax_id);
		if(!empty($this->rules['VatTax'])){
			$price = !empty($this->productPrices['basePriceWithTax']) ? $this->productPrices['basePriceWithTax'] : $this->productPrices['basePrice'];
			$this->productPrices['basePriceWithTax'] = $this->roundInternal($this->executeCalculation($this->rules['VatTax'], $price,true),'basePriceWithTax');
		}

		$this->rules['DBTax'] = $this->gatherEffectingRulesForProductPrice('DBTax', $this->product_discount_id);
		$this->productPrices['discountedPriceWithoutTax'] = $this->roundInternal($this->executeCalculation($this->rules['DBTax'], $this->productPrices['basePrice']),'discountedPriceWithoutTax');

		if ($override==-1) {
			$this->productPrices['discountedPriceWithoutTax'] = $product_override_price;
		}

		$priceBeforeTax = !empty($this->productPrices['discountedPriceWithoutTax']) ? $this->productPrices['discountedPriceWithoutTax'] : $this->productPrices['basePrice'];

		$this->productPrices['priceBeforeTax'] = $priceBeforeTax;
		$this->productPrices['salesPrice'] = $this->roundInternal($this->executeCalculation($this->rules['Tax'], $priceBeforeTax, true),'salesPrice');

		$salesPrice = !empty($this->productPrices['salesPrice']) ? $this->productPrices['salesPrice'] : $priceBeforeTax;

		$this->productPrices['taxAmount'] = $this->roundInternal($salesPrice - $priceBeforeTax);

		if(!empty($this->rules['VatTax'])){
			$this->productPrices['salesPrice'] = $this->roundInternal($this->executeCalculation($this->rules['VatTax'], $salesPrice),'salesPrice');
			$salesPrice = !empty($this->productPrices['salesPrice']) ? $this->productPrices['salesPrice'] : $salesPrice;
		}

		$this->rules['DATax'] = $this->gatherEffectingRulesForProductPrice('DATax', $this->product_discount_id);
		$this->productPrices['salesPriceWithDiscount'] = $this->roundInternal($this->executeCalculation($this->rules['DATax'], $salesPrice),'salesPriceWithDiscount');

		$this->productPrices['salesPrice'] = !empty($this->productPrices['salesPriceWithDiscount']) ? $this->productPrices['salesPriceWithDiscount'] : $salesPrice;

		$this->productPrices['salesPriceTemp'] = $this->productPrices['salesPrice'];
		//Okey, this may not the best place, but atm we handle the override price as salesPrice
		if ($override==1) {
			$this->productPrices['salesPrice'] = $product_override_price;
		}

		if(!empty($product->product_packaging) and $product->product_packaging!='0.0000'){
			$this->productPrices['unitPrice'] = $this->productPrices['salesPrice']/$product->product_packaging;
		} else {
			$this->productPrices['unitPrice'] = 0.0;
		}

		if(!empty($this->rules['VatTax'])){
			$this->_revert = true;
			$this->productPrices['priceWithoutTax'] = $this->productPrices['salesPrice'] - $this->productPrices['taxAmount'];
			$afterTax = $this->roundInternal($this->executeCalculation($this->rules['VatTax'], $this->productPrices['salesPrice']),'salesPrice');

			if(!empty($afterTax)){
				$this->productPrices['taxAmount'] = $this->productPrices['salesPrice'] - $afterTax;
			}
			$this->_revert = false;
		}

		//The whole discount Amount
		$basePriceWithTax = !empty($this->productPrices['basePriceWithTax']) ? $this->productPrices['basePriceWithTax'] : $this->productPrices['basePrice'];

		//changed
		if(empty($this->rules['DBTax'])){
			$this->productPrices['discountAmount'] = $this->roundInternal($basePriceWithTax - $this->productPrices['salesPrice']) * -1;
		} else {
			$this->productPrices['discountAmount'] = $this->roundInternal($this->productPrices['discountedPriceWithoutTax'] - $this->productPrices['basePriceVariant']) * -1;
		}

		//price Without Tax but with calculated discounts AFTER Tax. So it just shows how much the shopper saves, regardless which kind of tax
		$this->productPrices['priceWithoutTax'] = $salesPrice - $this->productPrices['taxAmount'];

		if ($override==1 || $this->productPrices['discountedPriceWithoutTax'] == 0) {
			$this->productPrices['discountedPriceWithoutTax'] = $this->productPrices['salesPrice'] - $this->productPrices['taxAmount'];
		}
		if (!isset($this->productPrices['discountedPriceWithoutTax'])) $this->productPrices['discountedPriceWithoutTax'] = 0.0;

		$this->productPrices['variantModification'] = $variant;

		$this->productPrices['DBTax'] = array();
		foreach($this->rules['DBTax'] as $dbtax){
			$this->productPrices['DBTax'][$dbtax['virtuemart_calc_id']] = array($dbtax['calc_name'],$dbtax['calc_value'],$dbtax['calc_value_mathop'],$dbtax['calc_shopper_published'],$dbtax['calc_currency'],$dbtax['calc_params'], $dbtax['virtuemart_vendor_id'], $dbtax['virtuemart_calc_id']);
		}

		$this->productPrices['Tax'] = array();
		foreach($this->rules['Tax'] as $tax){
			$this->productPrices['Tax'][$tax['virtuemart_calc_id']] =  array($tax['calc_name'],$tax['calc_value'],$tax['calc_value_mathop'],$tax['calc_shopper_published'],$tax['calc_currency'],$tax['calc_params'], $tax['virtuemart_vendor_id'], $tax['virtuemart_calc_id']);
		}

		$this->productPrices['VatTax'] = array();
		foreach($this->rules['VatTax'] as $tax){
			$this->productPrices['VatTax'][$tax['virtuemart_calc_id']] =  array($tax['calc_name'],$tax['calc_value'],$tax['calc_value_mathop'],$tax['calc_shopper_published'],$tax['calc_currency'],$tax['calc_params'], $tax['virtuemart_vendor_id'], $tax['virtuemart_calc_id'],);
		}

		$this->productPrices['DATax'] = array();
		foreach($this->rules['DATax'] as $datax){
			$this->productPrices['DATax'][$datax['virtuemart_calc_id']] =  array($datax['calc_name'],$datax['calc_value'],$datax['calc_value_mathop'],$datax['calc_shopper_published'],$datax['calc_currency'],$datax['calc_params'], $datax['virtuemart_vendor_id'], $datax['virtuemart_calc_id']);
		}

		$this->productPrices['Marge'] = array();
		foreach($this->rules['Marge'] as $marge){
			$this->productPrices['Marge'][$marge['virtuemart_calc_id']] =  array($marge['calc_name'],$marge['calc_value'],$marge['calc_value_mathop'],$marge['calc_shopper_published'],$marge['calc_currency'],$marge['calc_params'], $marge['virtuemart_vendor_id'], $marge['virtuemart_calc_id']);
		}

		if(!empty($this->rules['VatTax'])){
			if(empty($this->_cart->cartData['VatTax'])){
				$this->_cart->cartData['VatTax'] = array();
			}

			foreach($this->rules['VatTax'] as &$rule){
				if(isset($this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']])){
					if(!isset($this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['taxAmount'])) {
						$this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['taxAmount'] = 0.0;
						$this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['subTotal'] = 0.0;
					}
					$this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['taxAmount'] += $this->productPrices['taxAmount'] * $this->_product->amount;
					$this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['subTotal']  += $this->productPrices['salesPrice'] * $this->_product->amount;

				} else {
					$this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']] = $rule;
					if(!isset($this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['taxAmount'])) $this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['taxAmount'] = $this->productPrices['taxAmount'] * $this->_product->amount;
					if(!isset($this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['subTotal'])) $this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['subTotal'] = $this->productPrices['salesPrice'] * $this->_product->amount;
				}
			}
		}

		$tots = array('salesPrice', 'discountedPriceWithoutTax', 'priceWithoutTax', 'discountAmount', 'taxAmount');
		foreach($tots as $name){
			if(isset($this->productPrices[$name])){
				$this->productPrices[$name.'Tt'] = $this->productPrices[$name] * $amount;
			} else {
				$this->productPrices[$name.'Tt'] = 0.0;
			}
		}

		foreach($this->productPrices as $k => &$price){
			if(!is_array($price) and !empty($price) and is_numeric($price)){
				$price = round($price,$this->_internalDigits-4);
			}
		}

		$this->productPrices = array_merge($prices,$this->productPrices);

		return $this->productPrices;
	}

	public function calculateCostprice($productId,$data){

		$this->_revert = true;

		if(empty($data['product_currency'])){
			$this->_db->setQuery('SELECT * FROM #__virtuemart_product_prices  WHERE `virtuemart_product_id`="' . $productId . '" ');
			$row = $this->_db->loadAssoc();
			if ($row) {
				if (!empty($row['product_price'])) {

					$this->productCurrency = $row['product_currency'];
					$this->product_tax_id = $row['product_tax_id'];
					$this->product_discount_id = $row['product_discount_id'];
				} else {
					vmdebug('cost Price empty, if child, everything okey, this is just a dev note');
					return false;
				}
			}
		} else {
			$this->productCurrency = $data['product_currency'];
			$this->product_tax_id = $data['product_tax_id'];
			$this->product_discount_id = $data['product_discount_id'];

		}

		$this->_db->setQuery('SELECT `virtuemart_vendor_id` FROM #__virtuemart_products  WHERE `virtuemart_product_id`="' . $productId . '" ');
		$single = $this->_db->loadResult();
		$this->productVendorId = $single;
		if (empty($this->productVendorId)) {
			$this->productVendorId = 1;
		}

		$this->_db->setQuery('SELECT `virtuemart_category_id` FROM #__virtuemart_product_categories  WHERE `virtuemart_product_id`="' . $productId . '" ');
		$this->_cats = $this->_db->loadColumn();

		if(VmConfig::get('multix','none')!='none' and empty($this->vendorCurrency )){
			if(!isset($vendorCurrencies[$this->productVendorId])){
				$this->_db->setQuery('SELECT `vendor_currency` FROM #__virtuemart_vendors  WHERE `virtuemart_vendor_id`="' . $this->productVendorId . '" ');
				$vendorCurrencies[$this->productVendorId] = $this->_db->loadResult();
			}
			$this->vendorCurrency = $vendorCurrencies[$this->productVendorId];
		}

		$this->rules['Marge'] = $this->gatherEffectingRulesForProductPrice('Marge', $this->product_marge_id);
		$this->rules['Tax'] = $this->gatherEffectingRulesForProductPrice('Tax', $this->product_tax_id);
		$this->rules['VatTax'] = $this->gatherEffectingRulesForProductPrice('VatTax', $this->product_tax_id);
		$this->rules['DBTax'] = $this->gatherEffectingRulesForProductPrice('DBTax', $this->product_discount_id);
		$this->rules['DATax'] = $this->gatherEffectingRulesForProductPrice('DATax', $this->product_discount_id);

		$salesPrice = $data['salesPrice'];

		$withoutVatTax = $this->roundInternal($this->executeCalculation($this->rules['VatTax'], $salesPrice));
		$withoutVatTax = !empty($withoutVatTax) ? $withoutVatTax : $salesPrice;
		vmdebug('calculateCostprice',$salesPrice,$withoutVatTax, $data);

		$withDiscount = $this->roundInternal($this->executeCalculation($this->rules['DATax'], $withoutVatTax));
		$withDiscount = !empty($withDiscount) ? $withDiscount : $withoutVatTax;
		$withTax = $this->roundInternal($this->executeCalculation($this->rules['Tax'], $withDiscount));
		$withTax = !empty($withTax) ? $withTax : $withDiscount;

		$basePriceP = $this->roundInternal($this->executeCalculation($this->rules['DBTax'], $withTax));
		$basePriceP = !empty($basePriceP) ? $basePriceP : $withTax;

		$basePrice = $this->roundInternal($this->executeCalculation($this->rules['Marge'], $basePriceP));
		$basePrice = !empty($basePrice) ? $basePrice : $basePriceP;

		$productCurrency = CurrencyDisplay::getInstance();
		$costprice = $productCurrency->convertCurrencyTo( $this->productCurrency, $basePrice,false);
		$this->_revert = false;

		return $costprice;
	}


	public function setRevert($revert){
		$this->_revert = $revert;
	}

	public function fillVoidPrices(&$prices) {

		if (!isset($prices['basePrice']))
			$prices['basePrice'] = null;
		if (!isset($prices['basePriceVariant']))
			$prices['basePriceVariant'] = null;
		if (!isset($prices['basePriceWithTax']))
			$prices['basePriceWithTax'] = null;
		if (!isset($prices['discountedPriceWithoutTax']))
			$prices['discountedPriceWithoutTax'] = null;
		if (!isset($prices['priceBeforeTax']))
			$prices['priceBeforeTax'] = null;
		if (!isset($prices['taxAmount']))
			$prices['taxAmount'] = null;
		if (!isset($prices['salesPriceWithDiscount']))
			$prices['salesPriceWithDiscount'] = null;
		if (!isset($prices['salesPriceTemp']))
			$prices['salesPriceTemp'] = null;
		if (!isset($prices['salesPrice']))
			$prices['salesPrice'] = null;
		if (!isset($prices['discountAmount']))
			$prices['discountAmount'] = null;
		if (!isset($prices['priceWithoutTax']))
			$prices['priceWithoutTax'] = null;
		if (!isset($prices['variantModification']))
			$prices['variantModification'] = null;
		if (!isset($prices['unitPrice']))
			$prices['unitPrice'] = null;
		return $prices;
	}

	/** function to start the calculation, here it is for the invoice in the checkout
	 * This function is partly implemented !
	 *
	 * The function calls getProductPrices for every product except it is already known (maybe changed and adjusted with product amount value
	 * The single prices gets added in an array and already summed up.
	 *
	 * Then simular to getProductPrices first the effecting rules are determined and calculated.
	 * Ah function to determine the coupon that effects the calculation is already implemented. But not completly in the calculation.
	 *
	 * 		Subtotal + Tax + Discount =	Total
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param int $productIds 	The Ids of the products
	 * @param int $cartVendorId The Owner of the cart, this can be ignored in vm1.5
	 * @return int $prices		An array of the prices
	 * 							'resultWithOutTax'	The summed up baseprice of all products
	 * 							'resultWithTax'  	The final price of all products with their tax, discount and so on
	 * 							'discountBeforeTax'	discounted price without tax which affects only the checkout (the tax of the products is in it)
	 * 							'discountWithTax'	discounted price taxed
	 * 							'discountAfterTax'	final result
	 *
	 */
	//public function getCheckoutPrices(&$cart, $checkAutomaticSelected=true) {
	public function getCheckoutPrices(&$cart) {

		//vmdebug('in function getCheckoutPrices in function getCheckoutPrices');
		$this->_cart =& $cart;
		$this->inCart = TRUE;
		//$pricesPerId = array();

		$resultWithTax = 0.0;
		$resultWithOutTax = 0.0;
		$this->_cart->cartData['VatTax'] = array();
		$this->_cart->cartPrices = array();
		$this->_cart->cartPrices['basePrice'] = 0;
		$this->_cart->cartPrices['basePriceWithTax'] = 0;
		$this->_cart->cartPrices['discountedPriceWithoutTax'] = 0;
		$this->_cart->cartPrices['salesPrice'] = 0;
		$this->_cart->cartPrices['taxAmount'] = 0;
		$this->_cart->cartPrices['salesPriceWithDiscount'] = 0;
		$this->_cart->cartPrices['discountAmount'] = 0;
		$this->_cart->cartPrices['priceWithoutTax'] = 0;
		$this->_cart->cartPrices['subTotalProducts'] = 0;
		$this->_cart->cartPrices['billTotal'] = 0;
		$this->_cart->cartData['duty'] = 1;

		$this->_cart->cartData['payment'] = 0; //could be automatically set to a default set in the globalconfig
		$this->_cart->cartData['paymentName'] = '';
		$cartpaymentTax = 0;

		$this->_amountCart = 0;

		$customfieldModel = VmModel::getModel('customfields');

		foreach ($this->_cart->products as $cprdkey => $productCart) {

			if (empty($this->_cart->products[$cprdkey]->quantity) || empty($this->_cart->products[$cprdkey]->virtuemart_product_id)) {
				if(!is_object($this->_cart->products[$cprdkey])) {
					//vmdebug( 'Error the product for calculation is not an object',$product);
				} else {
					vmError( 'Error the quantity of the product for calculation is 0, please notify the shopowner, the product id ' . $this->_cart->products[$cprdkey]->virtuemart_product_id);
				}
				continue;
			}

			$this->productCurrency = isset($this->_cart->products[$cprdkey]->product_currency)? $this->_cart->products[$cprdkey]->product_currency:0;

			$variantmod = $customfieldModel->calculateModificators($this->_cart->products[$cprdkey]);
			$productPrice = $this->getProductPrices($this->_cart->products[$cprdkey],$variantmod, $this->_cart->products[$cprdkey]->quantity);
			//vmTrace('getProductPrices $productPrice '.$variantmod.' '.$productPrice['basePriceVariant'].' '.$productPrice['salesPrice']);
			//vmdebug('getCheckoutPrices ',$productPrice['salesPrice']);
			$selectedPrice = $this->_cart->products[$cprdkey]->selectedPrice;
			$this->_cart->products[$cprdkey]->allPrices[$selectedPrice] = array_merge($this->_cart->products[$cprdkey]->allPrices[$selectedPrice],$productPrice);

			$this->_cart->cartPrices[$cprdkey] = $productPrice; //$this->_cart->products[$cprdkey]->allPrices[$selectedPrice];

			$this->_amountCart += $this->_cart->products[$cprdkey]->quantity;

			if($this->_currencyDisplay->_priceConfig['basePrice']) $this->_cart->cartPrices['basePrice'] += self::roundInternal($this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['basePrice'],'basePrice') * $this->_cart->products[$cprdkey]->quantity;
			if($this->_currencyDisplay->_priceConfig['basePriceWithTax']) $this->_cart->cartPrices['basePriceWithTax'] += self::roundInternal($this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['basePriceWithTax']) * $this->_cart->products[$cprdkey]->quantity;
			if($this->_currencyDisplay->_priceConfig['discountedPriceWithoutTax']) $this->_cart->cartPrices['discountedPriceWithoutTax'] += self::roundInternal($this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['discountedPriceWithoutTax'],'discountedPriceWithoutTax') * $this->_cart->products[$cprdkey]->quantity;

			if($this->_currencyDisplay->_priceConfig['salesPrice']){
				$this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal_with_tax'] = self::roundInternal($this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['salesPrice'],'salesPrice') * $this->_cart->products[$cprdkey]->quantity;
				$this->_cart->cartPrices['salesPrice'] += $this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal_with_tax'];
				$this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'] = $this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal_with_tax'];
			}

			if($this->_currencyDisplay->_priceConfig['taxAmount']){
				$this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal_tax_amount'] = self::roundInternal($this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['taxAmount'],'taxAmount') * $this->_cart->products[$cprdkey]->quantity;
				$this->_cart->cartPrices['taxAmount'] += $this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal_tax_amount'];
			}

			if($this->_currencyDisplay->_priceConfig['salesPriceWithDiscount']) $this->_cart->cartPrices['salesPriceWithDiscount'] += self::roundInternal($this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['salesPriceWithDiscount'],'salesPriceWithDiscount') * $this->_cart->products[$cprdkey]->quantity;
			if($this->_currencyDisplay->_priceConfig['discountAmount']){
				$this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal_discount'] = self::roundInternal($this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['discountAmount'],'discountAmount') * $this->_cart->products[$cprdkey]->quantity;
				$this->_cart->cartPrices['discountAmount'] += $this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal_discount'];
			}
			if($this->_currencyDisplay->_priceConfig['priceWithoutTax']) {
				$this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal'] = self::roundInternal($this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['priceWithoutTax'],'priceWithoutTax') * $this->_cart->products[$cprdkey]->quantity;
				$this->_cart->cartPrices['priceWithoutTax'] += $this->_cart->products[$cprdkey]->allPrices[$selectedPrice]['subtotal'];
			}
			$this->_cart->products[$cprdkey]->prices = $this->_cart->products[$cprdkey]->allPrices[$selectedPrice];
		}

		$this->_product = null;
		$this->_cart->cartData['DBTaxRulesBill'] = $this->gatherEffectingRulesForBill('DBTaxBill');
		$this->_cart->cartData['taxRulesBill'] = $this->gatherEffectingRulesForBill('TaxBill');
		$this->_cart->cartData['DATaxRulesBill'] = $this->gatherEffectingRulesForBill('DATaxBill');

		$this->_cart->cartPrices['salesPriceDBT'] = array();
		$this->_cart->cartPrices['taxRulesBill'] = array();
		$this->_cart->cartPrices['DATaxRulesBill'] = array();

		foreach ($this->_cart->products as $cprdkey => $product) {
			//for Rules with Categories / Manufacturers
			foreach($this->_cart->cartData['DBTaxRulesBill'] as &$dbrule){
				$applyRule = FALSE;
				if(!empty($dbrule['calc_categories']) || !empty($dbrule['virtuemart_manufacturers'])){
					if(!isset($dbrule['subTotal'])) $dbrule['subTotal'] = 0.0;
					$setCat = !empty($dbrule['calc_categories']) ? array_intersect($dbrule['calc_categories'],$product->categories) : array();
					$setMan = !empty($dbrule['virtuemart_manufacturers']) ? array_intersect($dbrule['virtuemart_manufacturers'],$product->virtuemart_manufacturer_id) : array();
					if(!empty($dbrule['calc_categories']) && !empty($dbrule['virtuemart_manufacturers'])) {
						if(count($setCat)>0 && count($setMan)>0) {
							$applyRule = TRUE;
						}
					} else {
						if(count($setCat)>0 || count($setMan)>0) {
							$applyRule = TRUE;
						}
					}
				} else {
					$applyRule = TRUE;
				}
				if($applyRule){
					if(!isset($dbrule['subTotal'])) $dbrule['subTotal'] = 0.0;
					$dbrule['subTotal'] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
					// subarray with subTotal for each taxID necessary to calculate tax correct if there are more than one VatTaxes
					if(!isset($dbrule['subTotalPerTaxID'])) $dbrule['subTotalPerTaxID'] = array();
					if($product->product_tax_id != 0) {
						if(!isset($dbrule['subTotalPerTaxID'][$product->product_tax_id])) $dbrule['subTotalPerTaxID'][$product->product_tax_id] = 0.0;
						$dbrule['subTotalPerTaxID'][$product->product_tax_id] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
					} else {
						$taxRules = array_merge($this->allrules[$product->virtuemart_vendor_id]['VatTax'],$this->_cart->cartData['taxRulesBill']);
						foreach($taxRules as $virtuemart_calc_id => $rule){
							if(!empty($rule['calc_categories']) || !empty($rule['virtuemart_manufacturers'])) {
								$setCat = !empty($rule['calc_categories']) ? array_intersect($rule['calc_categories'],$product->categories) : array();
								$setMan = !empty($rule['virtuemart_manufacturers']) ? array_intersect($rule['virtuemart_manufacturers'],$product->virtuemart_manufacturer_id) : array();
								if(!empty($rule['calc_categories']) && !empty($rule['virtuemart_manufacturers'])) {
									if(count($setCat)>0 && count($setMan)>0) {
										if(!isset($dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']])) $dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']] = 0.0;
										$dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
									}
								} else {
									if(count($setCat)>0 || count($setMan)>0) {
										if(!isset($dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']])) $dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']] = 0.0;
										$dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
									}
								}
							} else {
								if(!isset($dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']])) $dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']] = 0.0;
								$dbrule['subTotalPerTaxID'][$rule['virtuemart_calc_id']] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
							}
						}
					}
				}
			}

			// subTotal for each taxID necessary, equal if calc_categories exists ore not
			if(!empty($this->_cart->cartData['taxRulesBill'])) {
				foreach($this->_cart->cartData['taxRulesBill'] as $k=>&$trule) {
					if(empty($trule['subTotal'])) $trule['subTotal'] = 0.0;
					if($product->product_tax_id != 0) {
						if($product->product_tax_id == $k) {
							$trule['subTotal']+= $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
						}
					} else if(!empty($trule['calc_categories']) || !empty($trule['virtuemart_manufacturers'])) {
						$setCat = !empty($trule['calc_categories']) ? array_intersect($trule['calc_categories'],$product->categories) : array();
						$setMan = !empty($trule['virtuemart_manufacturers']) ? array_intersect($trule['virtuemart_manufacturers'],$product->virtuemart_manufacturer_id) : array();
						if(!empty($trule['calc_categories']) && !empty($trule['virtuemart_manufacturers'])) {
							if(count($setCat)>0 && count($setMan)>0) {
								$trule['subTotal'] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
							}
						} else {
							if(count($setCat)>0 || count($setMan)>0) {
								$trule['subTotal'] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
							}
						}
					} else {
						$trule['subTotal'] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
					}
					//vmdebug('$this->_cart->cartData["taxRulesBill"]',$this->_cart->cartPrices[$cprdkey]);
				}
			}

			foreach($this->_cart->cartData['DATaxRulesBill'] as &$darule) {
				if(!empty($darule['calc_categories']) || !empty($darule['virtuemart_manufacturers'])) {
					if(!isset($darule['subTotal'])) $darule['subTotal'] = 0.0;
					$setCat = !empty($darule['calc_categories']) ? array_intersect($darule['calc_categories'],$product->categories) : array();
					$setMan = !empty($darule['virtuemart_manufacturers']) ? array_intersect($darule['virtuemart_manufacturers'],$product->virtuemart_manufacturer_id) : array();
					if(!empty($darule['calc_categories']) && !empty($darule['virtuemart_manufacturers'])) {
						if(count($setCat)>0 && count($setMan)>0) {
							$darule['subTotal'] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
						}
					} else {
						if(count($setCat)>0 || count($setMan)>0) {
							$darule['subTotal'] += $this->_cart->cartPrices[$cprdkey]['subtotal_with_tax'];
						}
					}
				}
			}

		}

		// Calculate the discount from all rules before tax to calculate billTotal
		$cartdiscountBeforeTax = $this->roundInternal($this->cartRuleCalculation($this->_cart->cartData['DBTaxRulesBill'], $this->_cart->cartPrices['salesPrice']));

		// We need the discount for each taxID to reduce the total discount before calculate percentage from hole cart discounts
		foreach ($this->_cart->cartData['DBTaxRulesBill'] as &$rule) {
			if (!empty($rule['subTotalPerTaxID'])) {
				foreach ($rule['subTotalPerTaxID'] as $k=>$DBTax) {
					$this->roundInternal($this->cartRuleCalculation($this->_cart->cartData['DBTaxRulesBill'], $this->_cart->cartPrices['salesPrice'], $k, true));
				}
			}
		}

		// combine the discounts before tax for each taxID
		foreach ($this->_cart->cartData['VatTax'] as &$rule) {
			if (!empty($rule['DBTax'])) {
				$sum = 0;
				foreach ($rule['DBTax'] as $key=>$val) {
					$sum += $val;
				}
				$rule['DBTax'] = $sum;
			}
		}

		// calculate the new subTotal with discounts before tax, necessary for billTotal
		$this->_cart->cartPrices['toTax'] = $this->_cart->cartPrices['salesPrice'] + $cartdiscountBeforeTax;

		//Avalara wants to calculate the tax of the shipment. Only disadvantage to set shipping here is that the discounts per bill respectivly the tax per bill
		// is not considered. Todo create a generic system, for example a param for billing rules, excluding/including shipment/payment
		$shipmentCalculated=false;
		if(!empty($this->_cart->cartData['taxRulesBill'])) {
			foreach( $this->_cart->cartData['taxRulesBill'] as $taxRulesBill ) {
				if(!empty($taxRulesBill['calc_value_mathop']) and $taxRulesBill['calc_value_mathop'] == 'avalara'){
					$this->calculateShipmentPrice();
					$shipmentCalculated=true;
				}
			}
		}

		// next step is handling a coupon, if given
		$this->_cart->cartData['vmVat'] = TRUE;
		$this->_cart->cartPrices['salesPriceCoupon'] = 0.0;
		if (!empty($this->_cart->couponCode)) {
			$this->couponHandler($this->_cart->couponCode);
		}

		// now calculate the discount for whole cart and reduce subTotal for each taxRulesBill, to calculate correct tax, also if there are more than one tax rules
		$totalDiscountBeforeTax = $this->_cart->cartPrices['salesPriceCoupon'];
		foreach ($this->_cart->cartData['taxRulesBill'] as $k=>&$rule) {

			if(!empty($rule['subTotal'])) {
				if (isset($this->_cart->cartData['VatTax'][$k]['DBTax'])) {
					$rule['subTotal'] += $this->_cart->cartData['VatTax'][$k]['DBTax'];
				}
				if (!isset($rule['percentage']) && $rule['subTotal'] < $this->_cart->cartPrices['salesPrice']) {
					$rule['percentage'] = $rule['subTotal'] / ($this->_cart->cartPrices['salesPrice'] + $cartdiscountBeforeTax);
				} else if (!isset($rule['percentage'])) {
					$rule['percentage'] = 1;
				}
				$rule['subTotal'] += $totalDiscountBeforeTax * $rule['percentage'];
			} else {
				$rule['subTotal'] = $this->_cart->cartPrices['toTax'];
			}
		}

		// now each taxRule subTotal is reduced with DBTax and we can calculate the cartTax
		$this->_cart->cartPrices['cartTax'] = $this->roundInternal($this->cartRuleCalculation($this->_cart->cartData['taxRulesBill'], $this->_cart->cartPrices['toTax']));

		// toDisc is new subTotal after tax, now it comes discount afterTax and we can calculate the final cart price with tax.
		$toDisc = $this->_cart->cartPrices['toTax'] + $this->_cart->cartPrices['cartTax'];
		$cartdiscountAfterTax = $this->roundInternal($this->cartRuleCalculation($this->_cart->cartData['DATaxRulesBill'], $toDisc));
		$this->_cart->cartPrices['withTax'] = $toDisc + $cartdiscountAfterTax;

		vmSetStartTime('methods');
		if(!$shipmentCalculated){
			$this->calculateShipmentPrice();
		}
		$this->calculatePaymentPrice();
		vmTime('Time consumed for shipment/payment plugins','methods');

		if($this->_currencyDisplay->_priceConfig['salesPrice']) $this->_cart->cartPrices['billSub'] = $this->_cart->cartPrices['basePrice'] + $this->_cart->cartPrices['shipmentValue'] + $this->_cart->cartPrices['paymentValue'];
		if($this->_currencyDisplay->_priceConfig['discountAmount']) $this->_cart->cartPrices['billDiscountAmount'] = $this->_cart->cartPrices['discountAmount'] + $cartdiscountBeforeTax + $cartdiscountAfterTax;// + $this->_cart->cartPrices['shipmentValue'] + $this->_cart->cartPrices['paymentValue'] ;
		if($this->_cart->cartPrices['salesPriceShipment'] < 0) $this->_cart->cartPrices['billDiscountAmount'] += $this->_cart->cartPrices['salesPriceShipment'];
		if($this->_cart->cartPrices['salesPricePayment'] < 0) $this->_cart->cartPrices['billDiscountAmount'] += $this->_cart->cartPrices['salesPricePayment'];
		if($this->_currencyDisplay->_priceConfig['taxAmount']) $this->_cart->cartPrices['billTaxAmount'] = $this->_cart->cartPrices['taxAmount'] + $this->_cart->cartPrices['shipmentTax'] + $this->_cart->cartPrices['paymentTax'] + $this->_cart->cartPrices['cartTax'];

		//The coupon handling is only necessary if a salesPrice is displayed, otherwise we have a kind of catalogue mode
		if($this->_currencyDisplay->_priceConfig['salesPrice']){
			$this->_cart->cartPrices['billTotal'] = $this->_cart->cartPrices['salesPriceShipment'] + $this->_cart->cartPrices['salesPricePayment'] + $this->_cart->cartPrices['withTax'] + $this->_cart->cartPrices['salesPriceCoupon'];

			if(empty($this->_cart->cartPrices['billTotal']) or $this->_cart->cartPrices['billTotal'] < 0){
				$this->_cart->cartPrices['billTotal'] = 0.0;
			}

			if($this->_cart->cartData['vmVat'] and (!empty($cartdiscountBeforeTax) and isset($this->_cart->cartData['VatTax']) and count($this->_cart->cartData['VatTax'])>0) or !empty($this->_cart->couponCode)){

				$totalDiscountToTax =  $this->_cart->cartPrices['salesPriceCoupon'];

				foreach($this->_cart->cartData['VatTax'] as &$vattax){

					if (isset($vattax['subTotal']) && !isset($vattax['percentage'])) {
						if (isset($vattax['DBTax'])) {
							$vattax['subTotal'] += $vattax['DBTax'];
						}
						if ($vattax['subTotal'] < $this->_cart->cartPrices['salesPrice']) {
							$vattax['percentage'] = $vattax['subTotal'] / ($this->_cart->cartPrices['salesPrice'] + $cartdiscountBeforeTax);
						} else {
							$vattax['percentage'] = 1;
						}
					}
					if (isset($vattax['calc_value']) && isset($vattax['percentage'])) {
						if(!isset($vattax['DBTax'])) $vattax['DBTax'] = 0.0;
						$vattax['discountTaxAmount'] = round(($totalDiscountToTax * $vattax['percentage'] + $vattax['DBTax']) / (100 + $vattax['calc_value']) * $vattax['calc_value'],$this->_currencyDisplay->_priceConfig['taxAmount'][1]);
					}
					if (isset($vattax['discountTaxAmount'])) $this->_cart->cartPrices['billTaxAmount'] += $vattax['discountTaxAmount'];
				}
			}

			if($this->_cart->cartPrices['billTaxAmount'] < 0){
				$this->_cart->cartPrices['billTaxAmount'] = 0.0;
			}
		}

		//Calculate VatTax result
		if ($this->_cart->cartPrices['shipment_calc_id']) {
			if(!is_array($this->_cart->cartPrices['shipment_calc_id'])) $this->_cart->cartPrices['shipment_calc_id'] = array($this->_cart->cartPrices['shipment_calc_id']);
			foreach($this->_cart->cartPrices['shipment_calc_id'] as $calcID) {
				if(isset($this->_cart->cartPrices['shipmentTaxPerID'][$calcID])){
					$this->_cart->cartData['VatTax'][$calcID]['shipmentTax'] = $this->_cart->cartPrices['shipmentTaxPerID'][$calcID];
					if(!isset($this->_cart->cartData['VatTax'][$calcID]['virtuemart_calc_id'])) $this->_cart->cartData['VatTax'][$calcID]['virtuemart_calc_id'] = $calcID;
				}
			}
		}
		if ($this->_cart->cartPrices['payment_calc_id']) {
			if(!is_array($this->_cart->cartPrices['payment_calc_id'])) $this->_cart->cartPrices['payment_calc_id'] = array($this->_cart->cartPrices['payment_calc_id']);
			foreach($this->_cart->cartPrices['payment_calc_id'] as $calcID) {
				if(isset($this->_cart->cartPrices['paymentTaxPerID'][$calcID])){
					$this->_cart->cartData['VatTax'][$calcID]['paymentTax'] = $this->_cart->cartPrices['paymentTaxPerID'][$calcID];
					if(!isset($this->_cart->cartData['VatTax'][$calcID]['virtuemart_calc_id'])) $this->_cart->cartData['VatTax'][$calcID]['virtuemart_calc_id'] = $calcID;
				}
			}
		}

		foreach($this->_cart->cartData['VatTax'] as &$vattax){
			$vattax['result'] = isset($vattax['taxAmount']) ? $vattax['taxAmount'] : 0;
			if (isset($vattax['discountTaxAmount'])) $vattax['result'] += $vattax['discountTaxAmount'];
			if (isset($vattax['shipmentTax'])) $vattax['result'] += $vattax['shipmentTax'];
			if (isset($vattax['paymentTax'])) $vattax['result'] += $vattax['paymentTax'];
			if (!isset($vattax['virtuemart_calc_id'])) $vattax['virtuemart_calc_id'] = $this->getCalcRuleData($vattax['virtuemart_calc_id'])->virtuemart_calc_id;
			if (!isset($vattax['calc_name'])) $vattax['calc_name'] = $this->getCalcRuleData($vattax['virtuemart_calc_id'])->calc_name;
			if (!isset($vattax['calc_value'])) $vattax['calc_value'] = $this->getCalcRuleData($vattax['virtuemart_calc_id'])->calc_value;
		}

		foreach ($this->_cart->cartData['taxRulesBill'] as &$rule) {
			$this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['result'] = isset($this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['result']) ? $this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['result'] : 0;
			$this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['result'] += $this->roundInternal($this->_cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff']);
			if(!isset($this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['virtuemart_calc_id'])) $this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['virtuemart_calc_id'] = $rule['virtuemart_calc_id'];
			if(!isset($this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['calc_name'])) $this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['calc_name'] = $rule['calc_name'];
			if(!isset($this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['calc_value'])) $this->_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['calc_value'] = $rule['calc_value'];
		}


	}


	/**
	 * Get the data of the CalcRule ID if it is not there
	 * @author Maik Kuennemann
	 * @param $VatTaxID ID of the taxe rule
	 */
	protected function getCalcRuleData($calcRuleID) {
		if(empty($this->_calcModel)){
			$this->_calcModel = VmModel::getModel('calc');
		}
		$calcRule = $this->_calcModel->getCalc($calcRuleID);
		return $calcRule;

	}

	/**
	 * Get coupon details and calculate the value
	 * @author Oscar van Eijk
	 * @param $_code Coupon code
	 */
	protected function couponHandler($_code) {

		JPluginHelper::importPlugin('vmcoupon');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmCouponHandler', array($_code,&$this->_cart->cartData, &$this->_cart->cartPrices));
		if(!empty($returnValues)){
			foreach ($returnValues as $returnValue) {
				if ($returnValue !== null  ) {
					return $returnValue;
				}
			}
		}

		if (!class_exists('CouponHelper'))
			require(VMPATH_SITE . DS . 'helpers' . DS . 'coupon.php');
		if (!($_data = CouponHelper::getCouponDetails($_code))) {
			return; // TODO give some error here
		}

		$_value_is_total = ($_data->percent_or_total == 'total');

		$this->_cart->cartData['couponCode'] = $_code;

		if($_value_is_total){
			$this->_cart->cartData['couponDescr'] = $this->_currencyDisplay->priceDisplay($_data->coupon_value);
		} else {
			$this->_cart->cartData['couponDescr'] = rtrim(rtrim($_data->coupon_value,'0'),'.') . ' %';
		}

		$this->_cart->cartPrices['salesPriceCoupon'] = ($_value_is_total ? $_data->coupon_value * -1 : ($this->_cart->cartPrices['salesPrice'] * ($_data->coupon_value / 100)) * -1);

		$this->_cart->cartPrices['couponTax'] = 0;
		$this->_cart->cartPrices['couponValue'] = $this->_cart->cartPrices['salesPriceCoupon'] - $this->_cart->cartPrices['couponTax'];

	}

	/**
	 * Function to calculate discount/tax of cart rules.
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers, Maik Künnemann
	 *
	 * @return int 	$price  	the discount/tax
	 */
	function cartRuleCalculation($rules, $baseprice, $TaxID = 0, $DBTax = false) {

		if (empty($rules))return 0;

		$rulesEffSorted = $this->record_sort($rules, 'ordering',$this->_revert);

		if (isset($rulesEffSorted)) {

			$discount = 0;

			foreach ($rulesEffSorted as &$rule) {
				if(isset($rule['subTotal'])) {
					$cIn = $rule['subTotal'];
				} else {
					$cIn = $baseprice;
				}

				$cOut = $this->interpreteMathOp($rule, $cIn);
				//vmdebug('my cout ',$cIn,$cOut,$TaxID,$rule);
				$this->_cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'] = $this->roundInternal($this->roundInternal($cOut) - $cIn);
				//$discount += round($this->_cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'],$this->_currencyDisplay->_priceConfig['salesPrice'][1]);
				$discount += $this->roundInternal($this->_cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff']);
				//vmdebug('my cout ',$cOut,$discount,$rule['subTotal'],$TaxID);
				if(isset($rule['subTotal']) and $TaxID != 0 and $DBTax == true) {
					if(isset($rule['subTotalPerTaxID'][$TaxID])) {
						$cIn = $rule['subTotalPerTaxID'][$TaxID];
						$cOut = $this->interpreteMathOp($rule, $cIn);
						$this->_cart->cartData['VatTax'][$TaxID]['DBTax'][$rule['virtuemart_calc_id'] . 'DBTax'] = round($this->roundInternal($this->roundInternal($cOut) - $cIn),$this->_currencyDisplay->_priceConfig['salesPrice'][1]);
						if(!isset($this->_cart->cartData['VatTax'][$TaxID]['virtuemart_calc_id'])) $this->_cart->cartData['VatTax'][$TaxID]['virtuemart_calc_id'] = $TaxID;
					}
				} else {
					//vmdebug('cartRuleCalculation is missing a condition and is not calculated ',$rule,$TaxID);
				}
			}
		}

		return $discount;
	}

	/**
	 * Function to execute the calculation of the gathered rules Ids.
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 		$rules 		The Ids of the products
	 * @param 		$price 		The input price, if no rule is affecting, 0 gets returned
	 * @return int 	$price  	the endprice
	 */
	function executeCalculation($rules, $baseprice, $relateToBaseAmount=false,$setCartPrices = true) {

		if (empty($rules))return 0;

		$rulesEffSorted = $this->record_sort($rules, 'ordering',$this->_revert);

		$price = $baseprice;
		$finalprice = $baseprice;
		if (isset($rulesEffSorted)) {

			foreach ($rulesEffSorted as $rule) {

				if(isset($rule['subTotal'])){
					$cIn = $rule['subTotal'];
				}
				else if ($relateToBaseAmount) {
					$cIn = $baseprice;
				} else {
					$cIn = $price;
				}

				$cOut = $this->interpreteMathOp($rule, $cIn);
				$tmp = $this->roundInternal($this->roundInternal($cOut) - $cIn);

				if($setCartPrices){
					$this->_cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'] = $tmp;
				}
				//okey, this is a bit flawless logic, but should work
				if ($relateToBaseAmount) {
					$finalprice = $finalprice + $tmp;
				} else {
					$price = $cOut;
				}
			}
		}

		//okey done with it
		if (!$relateToBaseAmount) {
			$finalprice = $price;
		}

		return $finalprice;
	}

	/**
	 * Gatheres the rules which affects the product.
	 *
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param	$entrypoint The entrypoint how it should behave. Valid values should be
	 * 						Profit (Commission is a profit rule that is shared, maybe we remove shared and make a new entrypoint called profit)
	 * 						DBTax (Discount for wares, coupons)
	 * 						Tax
	 * 						DATax (Discount on money)
	 * 						Duty
	 * @return	$rules The rules that effects the product as Ids
	 */
	function gatherEffectingRulesForProductPrice($entrypoint, $id) {

		$testedRules = array();
		if ($id === -1) return $testedRules;
		//virtuemart_calc_id 	virtuemart_vendor_id	calc_shopper_published	calc_vendor_published	published 	shared calc_amount_cond
		$countries = '';
		$states = '';
		$shopperGroup = '';
		$entrypoint = (string) $entrypoint;
		if(empty($this->allrules[$this->productVendorId][$entrypoint])){
			return $testedRules;
		}

		foreach ($this->allrules[$this->productVendorId][$entrypoint] as $i => &$rule) {
			$rule = (array) $rule;
			if(!empty($id)){
				if($rule['virtuemart_calc_id']==$id){
					$testedRules[$rule['virtuemart_calc_id']] = $rule;
				}
				continue;
			}

			if(!empty($rule['for_override'])){
				continue;
			}
			if(!isset($rule['calc_categories'])){

				$q = 'SELECT `virtuemart_category_id` FROM #__virtuemart_calc_categories WHERE `virtuemart_calc_id`="' . $rule['virtuemart_calc_id'] . '"';
				$this->_db->setQuery($q);
				$rule['calc_categories'] = $this->_db->loadColumn();
				//vmdebug('loaded cat rules '.$rule['virtuemart_calc_id']);
			}

			$hitsCategory = true;
			if (isset($this->_cats)) {
				if ($this->_debug) vmdebug('loaded cat rules ',$this->_cats,$rule['calc_categories']);
				$hitsCategory = $this->testRulePartEffecting($rule['calc_categories'], $this->_cats);
			}

			if(!isset($rule['virtuemart_shoppergroup_ids'])){
				$q = 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_calc_shoppergroups WHERE `virtuemart_calc_id`="' . $rule['virtuemart_calc_id'] . '"';
				$this->_db->setQuery($q);
				$rule['virtuemart_shoppergroup_ids'] = $this->_db->loadColumn();
				//vmdebug('loaded shoppergrp rules '.$rule['virtuemart_calc_id']);
			}

			$hitsShopper = true;
			if (isset($this->_shopperGroupId)) {
				$hitsShopper = $this->testRulePartEffecting($rule['virtuemart_shoppergroup_ids'], $this->_shopperGroupId);
			}

			if(!isset($rule['calc_countries'])){
				$q = 'SELECT `virtuemart_country_id` FROM #__virtuemart_calc_countries WHERE `virtuemart_calc_id`="' . $rule["virtuemart_calc_id"] . '"';
				$this->_db->setQuery($q);
				$rule['calc_countries'] = $this->_db->loadColumn();
				//vmdebug('loaded country rules '.$rule['virtuemart_calc_id']);
			}

			if(!isset($rule['virtuemart_state_ids'])){
				$q = 'SELECT `virtuemart_state_id` FROM #__virtuemart_calc_states WHERE `virtuemart_calc_id`="' . $rule["virtuemart_calc_id"] . '"';
				$this->_db->setQuery($q);
				$rule['virtuemart_state_ids'] = $this->_db->loadColumn();
				//vmdebug('loaded state rules '.$rule['virtuemart_calc_id']);
			}

			$hitsDeliveryArea = true;
			if(!empty($rule['virtuemart_state_ids'])){
				if (!empty($this->_deliveryState)){
					$hitsDeliveryArea = $this->testRulePartEffecting($rule['virtuemart_state_ids'], $this->_deliveryState);
				} else {
					$hitsDeliveryArea = false;
				}
			} else if(!empty($rule['calc_countries'])){
				if (!empty($this->_deliveryCountry)){
					$hitsDeliveryArea = $this->testRulePartEffecting($rule['calc_countries'], $this->_deliveryCountry);
				} else {
					$hitsDeliveryArea = false;
				}
			}

			if(!isset($rule['virtuemart_manufacturers'])){
				$q = 'SELECT `virtuemart_manufacturer_id` FROM #__virtuemart_calc_manufacturers WHERE `virtuemart_calc_id`="' . $rule['virtuemart_calc_id'] . '"';
				$this->_db->setQuery($q);
				$rule['virtuemart_manufacturers'] = $this->_db->loadColumn();
				//vmdebug('loaded manus rules '.$rule['virtuemart_calc_id']);
			}

			$hitsManufacturer = true;
			if (isset($this->_manufacturerId)) {
				$hitsManufacturer = $this->testRulePartEffecting($rule['virtuemart_manufacturers'], $this->_manufacturerId);
			}

			if ($hitsCategory and $hitsShopper and $hitsDeliveryArea and $hitsManufacturer) {
				if ($this->_debug)
					echo '<br/ >Add rule ForProductPrice ' . $rule["virtuemart_calc_id"];

				$testedRules[$rule['virtuemart_calc_id']] = $rule;
			} else {
				if ($this->_debug) vmdebug('plgVmInGatherEffectRulesProduct $hitsCategory '.(int)$hitsCategory.' $hitsShopper'.(int)$hitsShopper.' $hitsDeliveryArea'.(int)$hitsDeliveryArea.' '.(int)$hitsManufacturer,$rule);
			}
		}

		//Test rules in plugins
		if(!empty($testedRules) and count($testedRules)>0){
			JPluginHelper::importPlugin('vmcalculation');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('plgVmInGatherEffectRulesProduct',array(&$this,&$testedRules));
			if ($this->_debug) vmdebug('plgVmInGatherEffectRulesProduct rules',$testedRules);
		}

		return $testedRules;
	}

	/**
	 * Gathers the effecting rules for the calculation of the bill
	 *
	 * @copyright Copyright (c) 2009-2014 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param	$entrypoint
	 * @param	$cartVendorId
	 * @return $rules The rules that effects the Bill as Ids
	 */
	function gatherEffectingRulesForBill($entrypoint, $cartVendorId=1) {

		static $testedRulesCached = array();
		$testedRules = array();
		if(isset($testedRulesCached[$cartVendorId][$entrypoint])){
			return $testedRulesCached[$cartVendorId][$entrypoint];
		}
		if(empty($this->_calcModel)){
			$this->_calcModel = VmModel::getModel('calc');
		}

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q = 'SELECT * FROM #__virtuemart_calcs ';
		$shopperGrpJoin = '';
		if(!empty($this->_shopperGroupId) and count($this->_shopperGroupId)>0){
			$q .= ' LEFT JOIN #__virtuemart_calc_shoppergroups using(virtuemart_calc_id)';
			$shopperGrpJoin = "\n AND (";
			foreach($this->_shopperGroupId as $gr){
				$shopperGrpJoin .= ' virtuemart_shoppergroup_id = '.(int)$gr.' OR';
			}
			$shopperGrpJoin .=' (virtuemart_shoppergroup_id) IS NULL) ';
		}

		$countryGrpJoin = '';
		if(!empty($this->_deliveryCountry)){
			$q .= ' LEFT JOIN #__virtuemart_calc_countries using(virtuemart_calc_id) ';
			$countryGrpJoin = "\n AND (";
			$countryGrpJoin .= ' virtuemart_country_id = '.(int)$this->_deliveryCountry;
			$countryGrpJoin .=' OR (virtuemart_country_id) IS NULL) ';
		}

		$stateGrpJoin = '';
		if(!empty($this->_deliveryState)){
			$q .= ' LEFT JOIN #__virtuemart_calc_states using(virtuemart_calc_id) ';
			$stateGrpJoin = "\n AND (";
			$stateGrpJoin .= ' virtuemart_state_id = '.(int)$this->_deliveryState;
			$stateGrpJoin .=' OR (virtuemart_state_id) IS NULL) ';
		}
		$q .= 'WHERE
                `calc_kind`="' . $entrypoint . '"
                AND `published`="1"
                AND (`virtuemart_vendor_id`="' . $cartVendorId . '" OR `shared`="1" )
				AND ( publish_up = "' . $this->_db->escape($this->_nullDate) . '" OR publish_up <= "' . $this->_db->escape($this->_now) . '" )
				AND ( publish_down = "' . $this->_db->escape($this->_nullDate) . '" OR publish_down >= "' . $this->_db->escape($this->_now) . '" )';
		$q .= $shopperGrpJoin.$countryGrpJoin.$stateGrpJoin;

		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();

		foreach ($rules as $rule) {

			$rule = (array)$this->_calcModel->getCalc($rule['virtuemart_calc_id']);

			$hitsDeliveryArea = true;
			//vmdebug('gatherEffectingRulesForBill $hitsDeliveryArea $countries and states  ',$countries,$states,$q);
			if (!empty($rule['calc_countries']) && empty($rule['virtuemart_state_ids'])) {
				$hitsDeliveryArea = $this->testRulePartEffecting($rule['calc_countries'], $this->_deliveryCountry);
			} else if (!empty($rule['virtuemart_state_ids']) ) {
				$hitsDeliveryArea = $this->testRulePartEffecting($rule['virtuemart_state_ids'], $this->_deliveryState);
				vmdebug('gatherEffectingRulesForBill $hitsDeliveryArea '.(int)$hitsDeliveryArea.' '.$this->_deliveryState,$rule['virtuemart_state_ids']);
			}


			//vmdebug('$this->_shopperGroupId',$this->_shopperGroupId,$rule['virtuemart_shoppergroup_ids']);
			$hitsShopper = true;
			if (!empty($rule['virtuemart_shoppergroup_ids'])) {
				$hitsShopper = $this->testRulePartEffecting($rule['virtuemart_shoppergroup_ids'], $this->_shopperGroupId);
			}

			if ($hitsDeliveryArea && $hitsShopper) {
				if ($this->_debug)
					echo '<br/ >Add Checkout rule ' . $rule["virtuemart_calc_id"] . '<br/ >';
				$testedRules[$rule['virtuemart_calc_id']] = $rule;
			} else {
				if ($this->_debug) vmdebug(' NO HIT my _deliveryCountry _deliveryState',(int)$hitsDeliveryArea, (int)$hitsShopper,$this->_shopperGroupId);
			}
		}

		//Test rules in plugins
		if(!empty($testedRules) and count($testedRules)>0){
			JPluginHelper::importPlugin('vmcalculation');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('plgVmInGatherEffectRulesBill', array(&$this, &$testedRules));
		}

		$testedRulesCached[$cartVendorId][$entrypoint] = $testedRules;
		return $testedRules;
	}

	/**
	 * New idea, we load for the display all plugins
	 * @param $type
	 */
	function calculateDisplayedPlugins($type){

		// Handling shipment plugins
		if (!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');

		JPluginHelper::importPlugin('vm'.$type);

		//We use one trigger to load all possible plugins and store as result an array of the pluginmethods and their display.
		//we select the first if there is one.
		//We work always with linked variables, so the trigger should give back if any method for a plugin was found. So if there is a
		//positive return value, we know there exists at least one method.
		//The plugin write the results into the cart array $cartData['$type'] = array($methods);
		//The methods must have the rendered display

		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmCalculateDisplayedCartOptions'.ucfirst($type),array(&$this->_cart));

		//Plugin return true if no method is configured for the plugin
		foreach ($returnValues as $returnValue) {
			if(!$returnValue){
				//Throw error only for admins or log it, something like that
			}
		}

		//Now set the option
		$virtuemart_typemethod_id = 'virtuemart_'.$type.'method_id';

		$valid = false;
		if(!empty($this->_cart->cartData[$type])){

			if(!empty($this->_cart->cartData[$type][0])){
				$this->_cart->$virtuemart_typemethod_id = $this->_cart->cartData[$type][0][$virtuemart_typemethod_id];
				$this->_cart->cartData[$type.'Name'] = vmText::_($this->_cart->cartData[$type][0][$type.'Name']);
				//Here the values then
				$this->_cart->cartPrices[$type.'Value'] = $this->_cart->cartData[$type][0][$type.'Value']; //could be automatically set to a default set in the globalconfig
				$this->_cart->cartPrices[$type.'Tax'] = $this->_cart->cartData[$type][0][$type.'Tax'];
				$this->_cart->cartPrices[$type.'SalesPrice'] = $this->_cart->cartData[$type][0][$type.'SalesPrice'];
				$this->_cart->cartPrices[$type.'_calc_id'] = $this->_cart->cartData[$type][0][$type.'_calc_id'];
				//Just for legacy atm
				$this->_cart->cartPrices['salesPrice'.ucfirst($type)] = $this->_cart->cartData[$type][0]['salesPrice'.ucfirst($type)];;
				$valid=true;
			} else {

			}
		}


		return $this->_cart->cartPrices;
	}

	/**
	 * Calculates the effecting Shipment prices for the calculation
	 * @copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 * @param 	$code 	The Id of the coupon
	 * @return 	$rules 	ids of the coupons
	 */
	function calculateShipmentPrice( ) {

		$this->_cart->cartData['shipmentName'] = vmText::_('COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED');
		$this->_cart->cartPrices['shipmentValue'] = 0; //could be automatically set to a default set in the globalconfig
		$this->_cart->cartPrices['shipmentTax'] = 0;
		$this->_cart->cartPrices['salesPriceShipment'] = 0;
		$this->_cart->cartPrices['shipment_calc_id'] = 0;

		// Handling shipment plugins
		if (!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');

		JPluginHelper::importPlugin('vmshipment');
		$this->_cart->checkAutomaticSelectedPlug('shipment');
		if (empty($this->_cart->virtuemart_shipmentmethod_id)) return;

		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmOnSelectedCalculatePriceShipment',array(  $this->_cart, &$this->_cart->cartPrices, &$this->_cart->cartData['shipmentName']  ));

		//Plugin return true if shipment rate is still valid false if not any more
		$shipmentValid=0;
		foreach ($returnValues as $returnValue) {
			$shipmentValid += $returnValue;
		}
		if (!$shipmentValid) {
			vmdebug('calculateShipmentPrice $shipment INVALID set cart->virtuemart_shipmentmethod_id = 0 ',$this->_cart->virtuemart_shipmentmethod_id);
			$this->_cart->virtuemart_shipmentmethod_id = 0;
			$this->_cart->setCartIntoSession(false,true);
		}

		return $this->_cart->cartPrices;
	}

	/**
	 * Calculates the effecting Payment prices for the calculation
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 * @param 	$code 	The Id of the paymentmethod
	 * @param	$value	amount of the money to transfere
	 * @param	$value	$cartVendorId
	 * @return 	$paymentCosts 	The amount of money the customer has to pay. Calculated in shop currency
	 */
	function calculatePaymentPrice() {

		$this->_cart->cartData['paymentName'] = vmText::_('COM_VIRTUEMART_CART_NO_PAYMENT_SELECTED');
		$this->_cart->cartPrices['paymentValue'] = 0; //could be automatically set to a default set in the globalconfig
		$this->_cart->cartPrices['paymentTax'] = 0;
		$this->_cart->cartPrices['paymentTotal'] = 0;
		$this->_cart->cartPrices['salesPricePayment'] = 0;
		$this->_cart->cartPrices['payment_calc_id'] = 0;

		if (!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
		JPluginHelper::importPlugin('vmpayment');

		$this->_cart->checkAutomaticSelectedPlug('payment');
		if (empty($this->_cart->virtuemart_paymentmethod_id)) return;

		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmonSelectedCalculatePricePayment',array( $this->_cart, &$this->_cart->cartPrices, &$this->_cart->cartData['paymentName']  ));

		// Plugin return true if payment plugin is  valid false if not  valid anymore only one value is returned
		$paymentValid=0;
		foreach ($returnValues as $returnValue) {
			$paymentValid += $returnValue;
		}
		if (!$paymentValid) {
			$this->_cart->virtuemart_paymentmethod_id = 0;
			$this->_cart->setCartIntoSession();
		}
		return $this->_cart->cartPrices;
	}

	function calculateCustomPriceWithTax($price) {

		$price = $this->_currencyDisplay->convertCurrencyTo((int) $this->productCurrency, $price,true);

		if(VmConfig::get('cVarswT',1)){
			$taxRules = $this->gatherEffectingRulesForProductPrice('Tax', $this->product_tax_id);
			$vattaxRules = $this->gatherEffectingRulesForProductPrice('VatTax', $this->product_tax_id);
			$rules = array_merge($taxRules,$vattaxRules);
			if(!empty($rules)){
				$price = $this->executeCalculation($rules, $price, true);
			}
			$price = $this->roundInternal($price);
		}
		return $price;
	}

	/**
	 * This function just writes the query for gatherEffectingRulesForProductPrice
	 * When a condition is not set, it is handled like a set condition that affects it. So the users have only to add a value
	 * for the conditions they want to (You dont need to enter a start or end date when the rule should count everytime).
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param $data		the ids of the rule, for exampel the ids of the categories that affect the rule
	 * @param $field	the name of the field in the db, for exampel calc_categories to write a rule that asks for the field calc_categories
	 * @return $q		The query
	 */
	function writeRulePartEffectingQuery($data, $field, $setAnd=0) {
		$q = '';
		if (!empty($data)) {
			if ($setAnd) {
				$q = ' AND (';
			} else {
				$q = ' (';
			}
			foreach ($data as $id) {
				$q = $q . '`' . $field . '`="' . $id . '" OR';
			}
			$q = $q . '`' . $field . '`="0" )';
		}
		return $q;
	}

	/**
	 * This functions interprets the String that is entered in the calc_value_mathop field
	 * The first char is the signum of the function. The more this function can be enhanced
	 * maybe with function that works like operators, the easier it will be to make more complex disount/commission/profit formulas
	 * progressive, nonprogressive and so on.
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$mathop 	String reprasentation of the mathematical operation, valid ('+','-','+%','-%')
	 * @param	$value 	float	The value that affects the price
	 * @param 	$currency int	the currency which should be used
	 * @param	$price 	float	The price to calculate
	 */
	function interpreteMathOp($rule, $price) {

		$mathop = $rule['calc_value_mathop'];
		$value = (float)$rule['calc_value'];
		$currency = $rule['calc_currency'];
		$coreMathOp = array('+','-','+%','-%');

		if(!$this->_revert){
			$plus = '+';
			$minus = '-';
		} else {
			$plus = '-';
			$minus = '+';
		}

		if(in_array($mathop,$coreMathOp)){
			$sign = substr($mathop, 0, 1);

			$calculated = false;
			if (strlen($mathop) == 2) {
				$cmd = substr($mathop, 1, 2);
				if ($cmd == '%') {
					if(!$this->_revert){
						$calculated = $price * $value / 100.0;
					} else {
						if(!empty($value)){
							if($sign == $plus){
								$calculated =  abs($price /(1 -  (100.0 / $value)));
							} else {
								$calculated = abs($price /(1 +  (100.0 / $value)));
							}
						} else {
							vmdebug('interpreteMathOp $value is empty '.$rule['calc_name']);
						}
					}
				}
			} else if (strlen($mathop) == 1){
				$calculated = $this->_currencyDisplay->convertCurrencyTo($currency, $value);
			}

			if($sign == $plus){
				return $price + (float)$calculated;
			} else if($sign == $minus){
				return $price - (float)$calculated;
			} else {
				VmError('Unrecognised mathop '.$mathop.' in calculation rule found');
				return $price;
			}
		} else {

			JPluginHelper::importPlugin('vmcalculation');
			$dispatcher = JDispatcher::getInstance();

			$calculated = $dispatcher->trigger('plgVmInterpreteMathOp', array($this, $rule, $price,$this->_revert));
			if($calculated){
				foreach($calculated as $calc){
					if($calc) return $calc;
				}
			} else {
				VmError('Unrecognised mathop '.$mathop.' in calculation rule found, seems you created this rule with plugin not longer accesible (deactivated, uninstalled?)');
				return $price;
			}
		}

	}

	/**
	 * Standard round function, we round every number with 6 fractionnumbers
	 * We need at least 4 to calculate something like 9.25% => 0.0925
	 * 2 digits
	 * Should be setable via config (just for the crazy case)
	 */
	function roundInternal($value,$name = 0) {

		if(!$this->_roundindig and $name!==0){
			if(isset($this->_currencyDisplay->_priceConfig[$name][1])){
				//vmdebug('roundInternal rounding use '.$this->_currencyDisplay->_priceConfig[$name][1].' digits');
				return round($value,$this->_currencyDisplay->_priceConfig[$name][1]);
			} else {
				vmdebug('roundInternal rounding not found for '.$name,$this->_currencyDisplay->_priceConfig[$name]);
				return round($value, $this->_internalDigits);
			}
		} else {
			return round($value, $this->_internalDigits);
		}

	}


	/**
	 * Can test the tablefields Category, Country, State
	 *  If the the data is 0 false is returned
	 */
	function testRulePartEffecting($rule, $data) {

		if (!isset($rule))
			return true;
		if (!isset($data))
			return false;

		if (is_array($rule)) {
			if (count($rule) == 0)
				return true;
		} else {
			$rule = array($rule);
		}
		if (!is_array($data))
			$data = array($data);

		$intersect = array_intersect($rule, $data);
		if ($intersect) {
			return true;
		} else {
			return false;
		}
	}

	/** Sorts indexed 2D array by a specified sub array key
	 *
	 * Copyright richard at happymango dot me dot uk
	 * @author Max Milbers
	 */
	function record_sort($records, $field, $reverse=false) {
		if (is_array($records)) {
			$hash = array();

			foreach ($records as $record) {
				if(isset($record[$field])){
					$keyToUse = $record[$field];
					while (array_key_exists($keyToUse, $hash)) {
						$keyToUse = $keyToUse + 1;
					}
					$hash[$keyToUse] = $record;
				}
			}
			($reverse) ? krsort($hash) : ksort($hash);
			$records = array();
			foreach ($hash as $record) {
				$records [] = $record;
			}
		}
		return $records;
	}

}

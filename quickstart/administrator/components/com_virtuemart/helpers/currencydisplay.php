<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );



/**
 *
 * @version $Id: currencydisplay.php 9573 2017-06-07 15:06:51Z kkmediaproduction $
 * @package VirtueMart
 * @subpackage classes
 *
 * @author Max Milbers
 * @copyright Copyright (C) 2004-2008 Soeren Eberhardt-Biermann, 2011-2014 The Virtuemart Team and Author - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

class CurrencyDisplay {

	static $_instance = array();
	private $_currencyConverter;

	private $_currency_id   = '0';		// string ID related with the currency (ex : language)
	private $_symbol    		= 'udef';	// Printable symbol
	private $_nbDecimal 		= 2;	// Number of decimals past colon (or other)
	private $_decimal   		= ',';	// Decimal symbol ('.', ',', ...)
	private $_thousands 		= ' '; 	// Thousands separator ('', ' ', ',')
	private $_positivePos	= '{number}{symbol}';	// Currency symbol position with Positive values :
	private $_negativePos	= '{sign}{number}{symbol}';	// Currency symbol position with Negative values :
	private $_numeric_code = 0;
	var $_priceConfig	= array();	//holds arrays of 0 and 1 first is if price should be shown, second is rounding
	var $exchangeRateShopper = 1.0;
	var $_vendorCurrency = 0;
	var $_vendorCurrency_code_3 = 0;
	var $_vendorCurrency_numeric = 0;

	public static $priceNames = array('basePrice','variantModification','basePriceVariant',
	'basePriceWithTax','discountedPriceWithoutTax', 'discountedPriceWithoutTaxTt',
	'salesPrice', 'salesPriceTt', 'priceWithoutTax', 'priceWithoutTaxTt',
	'salesPriceWithDiscount','discountAmount','discountAmountTt','taxAmount', 'taxAmountTt','unitPrice');

	private function __construct ($vendorId = 0){

		if(empty($vendorId)) $vendorId = 1;

		$vendorM = VmModel::getModel('vendor');
		$vendorCurrency = $vendorM->getVendorCurrency($vendorId);
		if($vendorCurrency){
			$this->_vendorCurrency = $vendorCurrency->vendor_currency;
			$this->_vendorCurrency_code_3 = $vendorCurrency->currency_code_3;
			$this->_vendorCurrency_numeric = $vendorCurrency->currency_numeric_code;
		}

		$converterFile  = VmConfig::get('currency_converter_module','convertECB.php');

		if (file_exists( VMPATH_ADMIN.DS.'plugins'.DS.'currency_converter'.DS.$converterFile ) and !is_dir(VMPATH_ADMIN.DS.'plugins'.DS.'currency_converter'.DS.$converterFile)) {
			$module_filename=substr($converterFile, 0, -4);
			require_once(VMPATH_ADMIN.DS.'plugins'.DS.'currency_converter'.DS.$converterFile);
			if( class_exists( $module_filename )) {
				$this->_currencyConverter = new $module_filename();
			}
		} else {

			if(!class_exists('convertECB')) require(VMPATH_ADMIN.DS.'plugins'.DS.'currency_converter'.DS.'convertECB.php');
			$this->_currencyConverter = new convertECB();

		}


	}

	/**
	 *
	 * Gives back the format of the currency, gets $style if none is set, with the currency Id, when nothing is found it tries the vendorId.
	 * When no param is set, you get the format of the mainvendor
	 *
	 * @author Max Milbers
	 * @param int 		$currencyId Id of the currency
	 * @param int 		$vendorId Id of the vendor
	 * @param string 	$style The vendor_currency_display_code
	 *   FORMAT:
	 1: id,
	 2: CurrencySymbol,
	 3: NumberOfDecimalsAfterDecimalSymbol,
	 4: DecimalSymbol,
	 5: Thousands separator
	 6: Currency symbol position with Positive values :
	 7: Currency symbol position with Negative values :

	 EXAMPLE: ||&euro;|2|,||1|8
	 * @return string
	 */
	static public function getInstance($currencyId=0,$vendorId=0){

		$h = $currencyId.'.'.$vendorId;
		if (!isset(self::$_instance[$h])) {
			self::$_instance[$h] = new CurrencyDisplay($vendorId);

			if(empty($currencyId)){
				$app = JFactory::getApplication();
				if($app->isSite()){
					self::$_instance[$h]->_currency_id = $app->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',vRequest::getInt('virtuemart_currency_id', 0));
				}
				if(empty(self::$_instance[$h]->_currency_id)){
					self::$_instance[$h]->_currency_id = self::$_instance[$h]->_vendorCurrency;
				}

			} else {
				self::$_instance[$h]->_currency_id = $currencyId;
			}

			$curM = VmModel::getModel('currency');
			$style = $curM->getData((int)self::$_instance[$h]->_currency_id);

			if(!empty($style)){
				self::$_instance[$h]->setCurrencyDisplayToStyleStr($style);
			} else {
				vmLanguage::loadJLang('com_virtuemart');

				if(empty(self::$_instance[$h]->_currency_id)){
					$link = JURI::root().'administrator/index.php?option=com_virtuemart&view=user&task=editshop';
					vmWarn(vmText::sprintf('COM_VIRTUEMART_CONF_WARN_NO_CURRENCY_DEFINED','<a href="'.$link.'">'.$link.'</a>'));
				} else{
					if(vRequest::getCmd('view')!='currency'){
						$link = JURI::root().'administrator/index.php?option=com_virtuemart&view=currency&task=edit&cid[]='.self::$_instance[$h]->_currency_id;
						vmWarn(vmText::sprintf('COM_VIRTUEMART_CONF_WARN_NO_FORMAT_DEFINED','<a href="'.$link.'">'.$link.'</a>'));
					}
				}
			}
		}
		self::$_instance[$h]->setPriceArray();

		return self::$_instance[$h];
	}

	/**
	 * Parse the given currency display string into the currency diplsy values.
	 *
	 * This function takes the currency style string as saved in the vendor
	 * record and parses it into its appropriate values.  An example style
	 * string would be 1|&euro;|2|,|.|0|0
	 *
	 * @author Max Milbers
	 * @param String $currencyStyle String containing the currency display settings
	 */
	private function setCurrencyDisplayToStyleStr($style) {
		//vmdebug('setCurrencyDisplayToStyleStr ',$style);
		$this->_currency_id = $style->virtuemart_currency_id;
		$this->_symbol = $style->currency_symbol;
		$this->_nbDecimal = $style->currency_decimal_place;
		$this->_decimal = $style->currency_decimal_symbol;
		$this->_numeric_code = (int)$style->currency_numeric_code;
		$this->_thousands = $style->currency_thousands;
		$this->_positivePos = $style->currency_positive_style;
		$this->_negativePos = $style->currency_negative_style;

	}

	/**
	 * This function sets an array, which holds the information if
	 * a price is to be shown and the number of rounding digits
	 *
	 * @author Max Milbers
	 */
	function setPriceArray(){

		if(count($this->_priceConfig)>0)return true;

		$userModel = VmModel::getModel('user');
		$user = $userModel->getCurrentUser();
		$shopperModel = VmModel::getModel('shoppergroup');

		if(count($user->shopper_groups)>0){
			$sprgrp = $shopperModel->getShopperGroup($user->shopper_groups[0]);
		} else {
			//This Fallback is not tested
			$sprgrp = $shopperModel->getDefault($user->JUser->guest);

		}

		if($sprgrp){

			if($sprgrp->custom_price_display){
				if($sprgrp->show_prices){
					foreach(self::$priceNames as $name){
						$show = (int)$sprgrp->$name;
						$text = (int)$sprgrp->{$name.'Text'};
						$round = (int)$sprgrp->{$name.'Rounding'};
						if($round==-1){
							$round = $this->_nbDecimal;
						}
						$this->_priceConfig[$name] = array($show,$round,$text);
					}
				}
			} else {
				if(VmConfig::get('show_prices', 1)){
					foreach(self::$priceNames as $name){
						$show = VmConfig::get($name,0);
						$text = VmConfig::get($name.'Text',0);
						$round = VmConfig::get($name.'Rounding',$this->_nbDecimal);
						if($round==-1){
							$round = $this->_nbDecimal;
						}
						$this->_priceConfig[$name] = array($show,$round,$text);
					}
				}
			}
		}

		if(!count($this->_priceConfig)){
			foreach(self::$priceNames as $name){
				$this->_priceConfig[$name] = array(0,0,0);
			}
		}

	}

	/**
	 * getCurrencyForDisplay: get The actual displayed Currency
	 * Use this only in a view, plugin or modul, never in a model
	 *
	 * @param integer $currencyId
	 * return integer $currencyId: displayed Currency
	 */
	public function getCurrencyForDisplay( $currencyId=0 ){


		return $this->_currency_id;
	}

	/**
	 * This function is for the gui only!
	 * Use this only in a view, plugin or modul, never in a model
	 * TODO for vm3 remove quantity option
	 * @param float $price
	 * @param integer $currencyId
	 * return string formatted price
	 */
	public function priceDisplay($price, $currencyId=0,$quantity = 1.0,$inToShopCurrency = false,$nb= -1){

		$price = $this->roundForDisplay($price,$currencyId, $quantity ,$inToShopCurrency, $nb);
		return $this->getFormattedCurrency($price,$nb);
	}

	public function roundForDisplay($price, $currencyId=0,$quantity = 1.0,$inToShopCurrency = false,$nb= -1){

		if(empty($currencyId)) $currencyId = $this->getCurrencyForDisplay($currencyId);

		if($nb==-1){
			$nb = $this->_nbDecimal;
		}

		$price = (float)$price * (float)$quantity;

		$price = $this->convertCurrencyTo($currencyId,$price,$inToShopCurrency);

		if($this->_numeric_code===756 and VmConfig::get('rappenrundung',FALSE)=="1"){
			$price = round((float)$price * 2,1) * 0.5;
		} else {
			$price = round($price,$nb);
		}
		return $price;
	}

	/**
	 * Format, Round and Display Value
	 * @author Max Milbers
	 * @param val number
	 */
	public function getFormattedCurrency( $nb, $nbDecimal=-1){

		//TODO $this->_nbDecimal is the config of the currency and $nbDecimal is the config of the price type.
		if($nbDecimal==-1) $nbDecimal = $this->_nbDecimal;
		if($nb>=0){
			$format = $this->_positivePos;
			$sign = '+';
		} else {
			$format = $this->_negativePos;
			$sign = '-';
			$nb = abs($nb);
		}

		$res = number_format((float)$nb,(int)$nbDecimal,$this->_decimal,$this->_thousands);
		$search = array('{sign}', '{number}', '{symbol}');
		$replace = array($sign, $res, $this->_symbol);
		$formattedRounded = str_replace ($search,$replace,$format);

		return $formattedRounded;
	}

	public function getFormattedNumber($n,$dec){
		return number_format((float)$n,(int)$dec,$this->_decimal,$this->_thousands);
	}

	/**
	 * function to create a div to show the prices, is necessary for JS
	 *
	 * @author Max Milbers
	 * @author Patrick Kohl
	 * @param string name of the price
	 * @param String description key
	 * @param array the prices of the product
	 * return a div for prices which is visible according to config and have all ids and class set
	 */
	public function createPriceDiv($name,$description,$product_price,$priceOnly=false,$switchSequel=false,$quantity = 1.0,$forceNoLabel=false, $force = false){

		if(empty($product_price) and $name != 'billTotal' and $name != 'billTaxAmount') return '';

		//The fallback, when this price is not configured
		if(empty($this->_priceConfig[$name])){
			$this->_priceConfig[$name] = $this->_priceConfig['salesPrice'];
		}

		//This is a fallback because we removed the "salesPriceWithDiscount" ;
		if(is_array($product_price)){
			if(isset($product_price[$name])){
				$price = $product_price[$name] ;
			} else {
				return '';
			}
		} else {
			$price = $product_price;
		}

		//This could be easily extended by product specific settings
		if(!empty($this->_priceConfig[$name][0]) or $force){
			if(!empty($price) or $name == 'billTotal' or $name == 'billTaxAmount'){
				$vis = " vm-display vm-price-value";
				$priceFormatted = $this->priceDisplay($price,0,(float)$quantity,false,$this->_priceConfig[$name][1],$name );
			} else {
				$priceFormatted = '';
				$vis = " vm-nodisplay";
			}
			if($priceOnly){
				return $priceFormatted;
			}
			if($this->_priceConfig[$name][2] and !$forceNoLabel) {
				$descr = vmText::_($description);
				if($switchSequel){
					return '<div class="Price'.$name.$vis.'"><span class="Price'.$name.'">'.$priceFormatted.'</span>'.$descr.'</div>';
				} else {
					return '<div class="Price'.$name.$vis.'"><span class="vm-price-desc">'.$descr.'</span><span class="Price'.$name.'">'.$priceFormatted.'</span></div>';
				}
			} else {
				return '<div class="Price'.$name.$vis.'"><span class="Price'.$name.'">'.$priceFormatted.'</span></div>';
			}
		}

	}

	/**
	 *
	 * @author Max Milbers
	 * @param unknown_type $currency
	 * @param unknown_type $price
	 * @param unknown_type $shop
	 */
	function convertCurrencyTo($currency,$price,$shop=true){


		if(empty($currency)){
			return $price;
		}

		// If both currency codes match, do nothing
		if( (is_Object($currency) and $currency->_currency_id == $this->_vendorCurrency)  or (!is_Object($currency) and $currency == $this->_vendorCurrency)) {
			return $price;
		}

		if(is_Object($currency)){
			$exchangeRate = (float)$currency->exchangeRateShopper;
		}
		else {
			static $currency_exchange_rate = array();
			if(!isset($currency_exchange_rate[$currency])){
				$db = JFactory::getDBO();
				$q = 'SELECT `currency_exchange_rate` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` ="'.(int)$currency.'" ';
				$db->setQuery($q);
				$currency_exchange_rate[$currency] = (float)$db->loadResult();
			}

			if(!empty($currency_exchange_rate[$currency])){
				$exchangeRate = $currency_exchange_rate[$currency];
			} else {
				$exchangeRate = 0;
			}
		}

		if(!empty($exchangeRate) ){

			if($shop){
				$price = $price / $exchangeRate;
			} else {
				$price = $price * $exchangeRate;
			}

		} else {
			$currencyCode = self::ensureUsingCurrencyCode($currency);
			$vendorCurrencyCode = self::ensureUsingCurrencyCode($this->_vendorCurrency);

			if($shop){
				$price = $this ->_currencyConverter->convert( $price, $currencyCode, $vendorCurrencyCode);
			} else {
				$price = $this ->_currencyConverter->convert( $price , $vendorCurrencyCode, $currencyCode);
			}
		}

		return $price;
	}


	/**
	 * Changes the virtuemart_currency_id into the right currency_code
	 * For exampel 47 => EUR
	 *
	 * @author Max Milbers
	 * @author Frederic Bidon
	 */
	function ensureUsingCurrencyCode($curr){

		if(is_numeric($curr) and $curr!=0){
			if (!class_exists('ShopFunctions'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
			return ShopFunctions::getCurrencyByID($curr,'currency_code_3');
		}
		return $curr;
	}

	/**
	 * Changes the currency_code into the right virtuemart_currency_id
	 * For exampel 'currency_code_3' : EUR => 47
	 *
	 * @author Max Milbers
	 * @author Kohl Patrick
	 */
	function getCurrencyIdByField($value=0,$fieldName ='currency_code_3'){
		if(is_string($value) ){
			if (!class_exists('ShopFunctions'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');

			return ShopFunctions::getCurrencyIDByName($value,$fieldName);
		}
		return $value;
	}


	/**
	 *
	 * @author Horvath, Sandor [HU] http://de.php.net/manual/de/function.number-format.php
	 * @author Max Milbers
	 * @param double $number
	 * @param int $decimals
	 * @param string $thousand_separator
	 * @param string $decimal_point
	 */
	function formatNumber($number, $decimals = 2, $decimal_point = '.', $thousand_separator = '&nbsp;' ){
		return number_format($number,$decimals,$decimal_point,$thousand_separator);
	}

	/**
	 * Return the currency symbol
	 */
	public function getSymbol() {
		return($this->_symbol);
	}

	/**
	 * Return the currency ID
	 */
	public function getId() {
		return($this->_currency_id);
	}

}
// pure php no closing tag

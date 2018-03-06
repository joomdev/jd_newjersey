<?php

if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Calculation plugin for quantity based price rules
 *
 * @version $Id:$
 * @package VirtueMart
 * @subpackage Plugins - avalara
 * @author Max Milbers
 * @copyright Copyright (C) 2012 - 2013 iStraxx - All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 *
 *
 */

if (!class_exists('vmCalculationPlugin')) require(VMPATH_PLUGINLIBS.DS.'vmcalculationplugin.php');

defined('AVATAX_DEBUG') or define('AVATAX_DEBUG', 1);

function avadebug($string,$arg=NULL){
	if(AVATAX_DEBUG) vmdebug($string,$arg);
}

class plgVmCalculationAvalara extends vmCalculationPlugin {

	var $_connectionType = 'Production';
	var $vmVersion = '2.0.22e';

	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$varsToPush = array(
			'activated'          => array(0, 'int'),
			'company_code'       => array('', 'char'),
			'account'       => array('', 'char'),
			'license'     => array('', 'char'),
			'committ'   => array(0,'int'),
			'only_cart' => array(1,'int'),
            'dev' => array(1,'int'),
			'avatax_virtuemart_country_id'  => array(0,'int'),
            'avatax_virtuemart_state_id'  => array(0,'int'),
			'accrual'		=> array(0,'int'),
			'prevCheckoutAddInv' => array(1,'int'),
			'taxfreightcode' => array('','char')
		);

		$this->setConfigParameterable ('calc_params', $varsToPush);

		$this->setPluginLoggable();
		$this->tableFields = array('id', 'virtuemart_order_id', 'client_ip', 'sentValue','recievedValue');
		$this->_tableId = 'id';
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		if (JVM_VERSION > 1) {
			define ('VMAVALARA_PATH', JPATH_ROOT . DS . 'plugins' . DS . 'vmcalculation' . DS . 'avalara' );
		} else {
			define ('VMAVALARA_PATH', JPATH_ROOT . DS . 'plugins' . DS . 'vmcalculation' );
		}
		define('VMAVALARA_CLASS_PATH', VMAVALARA_PATH . DS . 'classes' );

		require(VMAVALARA_PATH.DS.'AvaTax.php');	// include in all Avalara Scripts

		if(!class_exists('ATConfig')) require (VMAVALARA_CLASS_PATH.DS.'ATConfig.class.php');

	}


	function plgVmOnStoreInstallPluginTable($jplugin_name,$name,$table=0) {
		//vmdebug('plgVmOnStoreInstallPluginTable',$jplugin_name,$name);
		if(!defined('VM_VERSION') or VM_VERSION < 3){
			return $this->onStoreInstallPluginTable($jplugin_name,$name);
		} else {
			$this->onStoreInstallPluginTable ($jplugin_name);
			$this->plgVmStorePluginInternalDataCalc($name);
		}

	}

	function getTableSQLFields() {
		$SQLfields = array(
			'id' => ' mediumint(1) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_calc_id' => 'mediumint(1) UNSIGNED NOT NULL DEFAULT \'0\'',
			'activated' => 'tinyint(1) NOT NULL DEFAULT \'0\'',
			'company_code' => ' char(255)',
			'account' => ' char(255)',
			'license' => ' char(255)'
		);
		return $SQLfields;
	}

	/**
	 * Gets the sql for creation of the table
	 * @author Max Milbers
	 */
	public function getVmPluginCreateTableSQL() {

 		return "CREATE TABLE IF NOT EXISTS `" . $this->_tablename . "` (
 			    `id` mediumint(1) unsigned NOT NULL AUTO_INCREMENT ,
 			    `virtuemart_calc_id` mediumint(1) UNSIGNED DEFAULT NULL,
 			    `activated` int(1),
 			    `account` char(255),
 			    `license` char(255),
 			    `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
 			    `created_by` int(11) NOT NULL DEFAULT 0,
 			    `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 			    `modified_by` int(11) NOT NULL DEFAULT 0,
 			    `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 			    `locked_by` int(11) NOT NULL DEFAULT 0,
 			     PRIMARY KEY (`id`),
 			     KEY `idx_virtuemart_calc_id` (`virtuemart_calc_id`)
 			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Table for avalara' AUTO_INCREMENT=1 ;";

	}


	function plgVmAddMathOp(&$entryPoints){
 		$entryPoints[] = array('calc_value_mathop' => 'avalara', 'calc_value_mathop_name' => 'Avalara');
	}

	function plgVmOnDisplayEdit(&$calc,&$html){

		$html .= '<fieldset>
	<legend>'.vmText::_('VMCALCULATION_AVALARA').'</legend>
	<table class="admintable">';

		$html .= VmHTML::row('checkbox','VMCALCULATION_AVALARA_ACTIVATED','activated',$calc->activated);
		$html .= VmHTML::row('input','VMCALCULATION_AVALARA_COMPANY_CODE','company_code',$calc->company_code);
		$html .= VmHTML::row('input','VMCALCULATION_AVALARA_ACCOUNT','account',$calc->account);
		$html .= VmHTML::row('input','VMCALCULATION_AVALARA_LICENSE','license',$calc->license);
		$html .= VmHTML::row('checkbox','VMCALCULATION_AVALARA_COMMITT','committ',$calc->committ);
		$html .= VmHTML::row('checkbox','VMCALCULATION_AVALARA_ONLYCART','only_cart',$calc->only_cart);
        $html .= VmHTML::row('checkbox','VMCALCULATION_AVALARA_ACCRUAL','accrual',$calc->accrual);
		$html .= VmHTML::row('checkbox','VMCALCULATION_AVALARA_DEV','dev',$calc->dev);
		$html .= VmHTML::row('checkbox','VMCALCULATION_AVALARA_PREVCHECKOUT_AD_INVALID','prevCheckoutAddInv',$calc->prevCheckoutAddInv);
		$html .= VmHTML::row('input','VMCALCULATION_AVALARA_TAXFREIGHTCODE','taxfreightcode',$calc->taxfreightcode);
		$label = 'VMCALCULATION_AVALARA_VADDRESS';
		$lang =JFactory::getLanguage();
		$label = $lang->hasKey($label.'_TIP') ? '<span class="hasTip" title="'.vmText::_($label.'_TIP').'">'.vmText::_($label).'</span>' : vmText::_($label) ;
        	$html .= '
            <tr>
                <td class="key">
                    '.$label.'
                </td>
                <td>
                    '.shopfunctionsF::renderCountryList($calc->avatax_virtuemart_country_id,TRUE,array(),'avatax_').'
                </td>';

    /*   $countriesList = ShopFunctions::renderCountryList($calc->calc_countries,True);
                $this->assignRef('countriesList', $countriesList);
                $statesList = ShopFunctions::renderStateList($calc->virtuemart_state_ids,'', True);
                $this->assignRef('statesList', $statesList);

            $label = 'VMCALCULATION_AVALARA_VADDRESS';
            $lang =JFactory::getLanguage();
            $label = $lang->hasKey($label.'_TIP') ? '<span class="hasTip" title="'.vmText::_($label.'_TIP').'">'.vmText::_($label).'</span>' : cmText::_($label) ;
         $html .= '
			<td>
				'.shopfunctions::renderStateList($calc->avatax_virtuemart_state_id,'avatax_',TRUE).'
			</td> */
        $html .= '</tr>';

		//$html .= VmHTML::row('checkbox','VMCALCULATION_AVALARA_VADDRESS','vAddress',$calc->vAddress);
	//	$html .= VmHTML::row('checkbox','VMCALCULATION_ISTRAXX_AVALARA_TRACE','trace',$calc->trace);

		$html .= '</table>';
		if(!class_exists('SoapClient')){
			vmWarn('Please enable the SOAP client in your php configuration.');
		} else {
			if ($calc->activated) {
				$html .= $this->ping($calc);
			}
		}

		$html .= vmText::_('VMCALCULATION_AVALARA_MANUAL').'</fieldset>';


		return TRUE;
	}

	/**
	 * We can only calculate it for the productdetails view
	 * @param $calculationHelper
	 * @param $rules
	 */
	public function plgVmInGatherEffectRulesProduct(&$calculationHelper,&$rules){

		//If in cart, the tax is calculated per bill, so the rule per product must be removed
		if($calculationHelper->inCart){
			foreach($rules as $k=>$rule){
				if($rule['calc_value_mathop']=='avalara'){
					unset($rules[$k]);
				}
			}
		}
	}



	public function plgVmStorePluginInternalDataCalc(&$data){

		if (!class_exists ('TableCalcs')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'calcs.php');
		}
		if(!empty($data['avatax_virtuemart_country_id'])){
			$data['avatax_virtuemart_country_id'] = json_encode($data['avatax_virtuemart_country_id']);
		}

		$db = JFactory::getDBO ();
		$table = new TableCalcs($db);
		$table->setUniqueName('calc_name');
		$table->setObligatoryKeys('calc_kind');
		$table->setLoggable();
		$table->setParameterable ($this->_xParams, $this->_varsToPushParam);
		$table->bindChecknStore($data);
	}

	public function plgVmGetPluginInternalDataCalc(&$calcData){

		$calcData->setParameterable ($this->_xParams, $this->_varsToPushParam);

		if (!class_exists ('VmTable')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmtable.php');
		}
		VmTable::bindParameterable ($calcData, $this->_xParams, $this->_varsToPushParam);
		if(!is_array($calcData->avatax_virtuemart_country_id)){
			//Suppress Warning
			$calcData->avatax_virtuemart_country_id = json_decode($calcData->avatax_virtuemart_country_id,true);

		}
		return TRUE;
	}

	public function plgVmDeleteCalculationRow($id){
		$this->removePluginInternalData($id);
	}

	function newATConfig($calc){

		if(is_object($calc)){
			$calc = get_object_vars($calc);
		}

		if(!empty($calc['dev'])){
			$this->_connectionType = 'Development';
		} else {
			$this->_connectionType = 'Production';
		}

		if(!class_exists('TextCase')) require (VMAVALARA_CLASS_PATH.DS.'TextCase.class.php');

		$__wsdldir = VMAVALARA_CLASS_PATH."/wsdl";
		$standard = array(
			'url'       => 'no url specified',
			'addressService' => '/Address/AddressSvc.asmx',
			'taxService' => '/Tax/TaxSvc.asmx',
			'batchService'=> '/Batch/BatchSvc.asmx',
			'avacertService'=> '/AvaCert/AvaCertSvc.asmx',
			'addressWSDL' => 'file://'.$__wsdldir.'/Address.wsdl',
			'taxWSDL'  => 'file://'.$__wsdldir.'/Tax.wsdl',
			'batchWSDL'  => 'file://'.$__wsdldir.'/BatchSvc.wsdl',
			'avacertWSDL'  => 'file://'.$__wsdldir.'/AvaCertSvc.wsdl',
			'account'   => '<your account number here>',
			'license'   => '<your license key here>',
			'adapter'   => 'avatax4php,5.10.0.0',
			'client'    => 'VirtueMart'.$this->vmVersion,
			'name'    => 'PHPAdapter',
			'TextCase' => TextCase::$Mixed,
			'trace'     => TRUE);

		//VmConfig::$echoDebug = TRUE;
		//if(!is_object())avadebug($calc);
		if(!class_exists('ATConfig')) require (VMAVALARA_CLASS_PATH.DS.'ATConfig.class.php');

		if($this->_connectionType == 'Development'){
			$devValues = array(
				'url'       => 'https://development.avalara.net',
				'account'   => $calc['account'],
				'license'   => $calc['license'],
				'trace'     => TRUE); // change to false for production
			$resultingConfig = array_merge($standard,$devValues);
			$config = new ATConfig($this->_connectionType, $resultingConfig);

		} else {
			$prodValues = array(
				'url'       => 'https://avatax.avalara.net',
				'account'   => $calc['account'],
				'license'   => $calc['license'],
				'trace'     => FALSE);
			$resultingConfig = array_merge($standard,$prodValues);
			$config = new ATConfig($this->_connectionType, $resultingConfig);
		}

		return $config;
	}

	function ping ($calc) {

		$html = '';
		$this->newATConfig($calc);

		if(!class_exists('TaxServiceSoap')) require (VMAVALARA_CLASS_PATH.DS.'TaxServiceSoap.class.php');
		$client = new TaxServiceSoap($this->_connectionType);

		try
		{
			if(!class_exists('PingResult')) require (VMAVALARA_CLASS_PATH.DS.'PingResult.class.php');
			$result = $client->ping("TEST");
			vmInfo('Avalara Ping ResultCode is: '. $result->getResultCode() );

			if(!class_exists('SeverityLevel')) require (VMAVALARA_CLASS_PATH.DS.'SeverityLevel.class.php');
			if($result->getResultCode() != SeverityLevel::$Success){
				foreach($result->Messages() as $msg){
					$html .= $msg->Name().": ".$msg->Summary()."<br />";
				}
			} else {
				vmInfo('Avalara used Ping Version is: '. $result->getVersion() );
			}
		}
		catch(SoapFault $exception)
		{

			$err = "Exception: ping ";
			if($exception)
				$err .= $exception->faultstring;

			$err .='<br />';
			$err .='last request: '. $client->__getLastRequest().'<br />';
			$err .='last response: '. $client->__getLastResponse().'<br />';
			vmError($err);
			avadebug('AvaTax the ping throws exception ',$exception->getMessage());
		}

		return $html;
	}

	public function plgVmInterpreteMathOp ($calculationHelper, $rule, $price,$revert){

		$rule = (object)$rule;
		if(empty($rule->published)) return $price;

		$mathop = $rule->calc_value_mathop;
		$tax = 0.0;
		if($calculationHelper->inCart){
			static $done = false;
			if($done) return $price;
		}
		if ($mathop=='avalara') {
			$requestedProductId = vRequest::getVar ('virtuemart_product_id',0);
			if(is_array($requestedProductId) and count($requestedProductId) > 0) {
				$requestedProductId = $requestedProductId[0];
			}
			$requestedProductId = (int) $requestedProductId;

			if(isset($calculationHelper->_product)){
				$productId = $calculationHelper->_product->virtuemart_product_id;
			} else {
				$productId = $requestedProductId;
			}

			if($calculationHelper->inCart){
				$products = $this->getCartProducts($calculationHelper);
				if(!$products){
					$this->blockCheckout();
					return $price;
				}
			}
			//avadebug('plgVmInterpreteMathOp avalara ',$rule);
			if(($productId!=0 and $productId==$requestedProductId) or $calculationHelper->inCart ){
				VmTable::bindParameterable ($rule, $this->_xParams, $this->_varsToPushParam);
				if($rule->activated==0) return $price;
				if(empty($this->addresses)){
					$vmadd = $this->getShopperDataFromCart($rule);
					$this->addresses = $this->fillValidateAvalaraAddress($rule,$vmadd);
				}

				if($this->addresses){

					if(empty($products))$products = $this->prepareSingleProduct($calculationHelper,$price);

					if($calculationHelper->inCart){
						$prices =  $calculationHelper->getCartPrices();
						if(!empty($prices['salesPriceCoupon'])){
							if(!isset($products['discountAmount'])) $products['discountAmount'] = 0.0;
							$products['discountAmount'] -= $prices['salesPriceCoupon'];
							vmdebug('Adding couponvalue to discount '.$products['discountAmount']);
						}
					}
					$tax = $this->getAvaTax( $rule,$products);
					if($calculationHelper->inCart){
						$tax = 0.0;
						$prices =  $calculationHelper->getCartPrices();
						//avadebug('My prices',$prices);
						$toSet = self::$_taxResult;
						//$toSet['salesPrice'] = 0.0;
						foreach(self::$_taxResult as $k => $line){
							if(is_integer($k)){
								$toSet[$k]['salesPrice'] = $prices[$k]['priceBeforeTax'] + $line['taxAmount'];
								$toSet[$k]['subtotal_with_tax'] = $prices[$k]['subtotal_with_tax'] + $line['taxAmountQuantity'];
								//$toSet['salesPrice'] += $toSet[$k]['subtotal_with_tax'];
							}
						}
						$toSet['taxAmount'] = self::$_taxResult['totalTax'];
						$toSet['toTax'] = $prices['toTax'] + self::$_taxResult['totalTax'];
						if(isset($prices['shipmentValue']) and isset(self::$_taxResult['shipmentTax'] )) {
							$toSet['shipmentTax'] = self::$_taxResult['shipmentTax'];
							$toSet['salesPriceShipment'] = $prices['shipmentValue'] + self::$_taxResult['shipmentTax'] ;
						}

						if(isset($prices['paymentValue']) ) { //and isset(self::$_taxResult['paymentTax'] )) {
							$toSet['paymentTax'] = 0.0;
							$toSet['salesPricePayment'] = $prices['paymentValue'];// + self::$_taxResult['paymentTax'] );
						}

						avadebug('avatax plgVmInterpreteMathOp result',self::$_taxResult,$toSet);
						$calculationHelper->setCartPricesMerge($toSet);
						//$prices =  $calculationHelper->getCartPrices();
						//avadebug('My merged prices',$prices);
						//$done = true;
					}
				} else if($rule->prevCheckoutAddInv){
					if($calculationHelper->inCart){
						VmInfo('VMCALCULATION_AVALARA_INVALID_INFO');
					}
					$this->blockCheckout();
				}
			}
		}

		if($revert){
			$tax = -$tax;
		}

		return $price + (float)$tax;
	}

	function plgVmConfirmedOrder ($cart, $order) {

		$avaTaxRule = 0;
		if(isset($order['calc_rules'])){
			foreach($order['calc_rules'] as $rule){
				if($rule->calc_mathop == 'avalara' and $rule->calc_kind == 'taxRulesBill'){
					$avaTaxRule=$rule;
					break;
				}
			}
		}

		if($avaTaxRule!==0){
			if(!empty($avaTaxRule->calc_params)){
				VmTable::bindParameterable ($avaTaxRule, $this->_xParams, $this->_varsToPushParam);

				if($rule->activated==0)return false;
				if($rule->accrual==0)return false;
				if(empty($this->addresses)){
					$vmadd = $this->getShopperDataFromCart($rule);
					$this->addresses = $this->fillValidateAvalaraAddress($rule,$vmadd);
				}
				if($this->addresses){
					if (!class_exists ('calculationHelper')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
					$calculator = calculationHelper::getInstance ();
					$orderModel = VmModel::getModel('orders');
					$invoiceNumber = 'onr_'.$order['details']['BT']->order_number;
					vRequest::setVar('create_invoice',1);
					$orderModel -> createInvoiceNumber($order['details']['BT'],$invoiceNumber);


					avadebug('avatax plgVmConfirmedOrder $order',$invoiceNumber,$order);
					if(is_array($invoiceNumber)) $invoiceNumber = $invoiceNumber[0];
					$products = $this->getCartProducts($calculator);
					$tax = $this->getAvaTax( $rule,$products,$invoiceNumber,$order['details']['BT']->order_number);
					//Todo adjust for BE
					$prices =  $calculator->getCartPrices();
					if($prices) {
						self::$_taxResult['salesPriceShipment'] = ($prices['shipmentValue'] + self::$_taxResult['shipmentTax'] );
						self::$_taxResult['paymentTax'] = 0.0;
					}
					$calculator->setCartPricesMerge(self::$_taxResult);
				}
			}
		}

	}

	static $vmadd = NULL;
	private function getShopperDataFromCart($calc){

		if(!isset(self::$vmadd)){

			$view = vRequest::getCmd('view',0);
			if($calc->only_cart == 1 and $view != 'cart'){
				self::$vmadd = FALSE;
				return self::$vmadd;
			}
			//We need for the tax calculation the shipment Address
			//We have this usually in our cart.
			if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
			$cart = VirtueMartCart::getCart();

			//Test first for ST
			if($cart->STsameAsBT){
				if(!empty($cart->BT)) $vmadd = $cart->BT;
			} else if(!empty($cart->ST)){
				$vmadd = $cart->ST;
			} else {
				if(!empty($cart->BT)) $vmadd = $cart->BT;
			}

			$vmadd['customer_number'] = $cart->customer_number;

			if(empty($vmadd) or !is_array($vmadd) or (is_array($vmadd) and count($vmadd) <2) ){
				vmInfo('VMCALCULATION_AVALARA_INSUF_INFO');
				$vmadd=FALSE;
			}

			self::$vmadd = $vmadd;
		}

		return self::$vmadd;
	}


	static $validatedAddresses = NULL;

	private function fillValidateAvalaraAddress($calc,$vmadd){


		if(!empty($vmadd)){

			if(is_object($vmadd)){
				$vmadd = get_object_vars($vmadd);
			}

			if(is_object($calc)){
				$calc = get_object_vars($calc);
			}

			//avadebug('my $vmadd',$vmadd);
			//First country check
			if(empty($vmadd['virtuemart_country_id'])){

				self::$validatedAddresses = FALSE;
				return self::$validatedAddresses;
			} else {
				if(empty($calc['avatax_virtuemart_country_id'])){
					vmError('AvaTax, please select countries, to validate. Use fallback for USA and Canada');
					//But lets use a fallback
					$calc['avatax_virtuemart_country_id'] = array('223','38');	//For USA and Canada
				}

				if(!is_array($calc['avatax_virtuemart_country_id'])){
					//Suppress Warning
					$calc['avatax_virtuemart_country_id'] = json_decode($calc['avatax_virtuemart_country_id'],true);
				}
				if(!in_array($vmadd['virtuemart_country_id'],$calc['avatax_virtuemart_country_id'])){
					avadebug('fillValidateAvalaraAddress not validated, country not set',$vmadd['virtuemart_country_id'],$calc['avatax_virtuemart_country_id']);
					self::$validatedAddresses = FALSE;
					return self::$validatedAddresses;
				}

			}

			if(!class_exists('Address')) require (VMAVALARA_CLASS_PATH.DS.'Address.class.php');
			$address = new Address();
			if(isset($vmadd['address_1'])) $address->setLine1($vmadd['address_1']);
			if(isset($vmadd['address_2'])) $address->setLine2($vmadd['address_2']);
			if(isset($vmadd['city'])) $address->setCity($vmadd['city']);

			if(isset($vmadd['virtuemart_country_id'])){

				$vmadd['country'] = ShopFunctions::getCountryByID($vmadd['virtuemart_country_id'],'country_2_code');
				if(isset($vmadd['country'])) $address->setCountry($vmadd['country']);
			}
			if(isset($vmadd['virtuemart_state_id'])){
				$vmadd['state'] = ShopFunctions::getStateByID($vmadd['virtuemart_state_id'],'state_2_code');
				if(isset($vmadd['state'])) $address->setRegion($vmadd['state']);
			}

			if(isset($vmadd['zip'])) $address->setPostalCode($vmadd['zip']);

			$hash = md5(implode($vmadd,','));
			$session = JFactory::getSession ();
			$validatedAddress = $session->get ('vm_avatax_address_checked.' . $hash, FALSE, 'vm');
			if(!$validatedAddress){


				$config = $this->newATConfig($calc);

				if(!class_exists('AddressServiceSoap')) require (VMAVALARA_CLASS_PATH.DS.'AddressServiceSoap.class.php');
				$client = new AddressServiceSoap($this->_connectionType,$config);

				if(!class_exists('SeverityLevel')) require (VMAVALARA_CLASS_PATH.DS.'SeverityLevel.class.php');
				if(!class_exists('Message')) require (VMAVALARA_CLASS_PATH.DS.'Message.class.php');

				//if($calc->vAddress==0){
			/*	if(isset($vmadd['country']) and $vmadd['country']!= 'US' and $vmadd['country']!= 'CA'){

					self::$validatedAddresses = array($address);
					return self::$validatedAddresses;
				}*/

				$address->Coordinates = 1;
				$address->Taxability = TRUE;
				$textCase = TextCase::$Mixed;
				$coordinates = 1;

				if(!class_exists('ValidateResult')) require (VMAVALARA_CLASS_PATH.DS.'ValidateResult.class.php');
				if(!class_exists('ValidateRequest')) require (VMAVALARA_CLASS_PATH.DS.'ValidateRequest.class.php');
				if(!class_exists('ValidAddress')) require (VMAVALARA_CLASS_PATH.DS.'ValidAddress.class.php');

				//TODO add customer code //shopper_number
				try
				{
					$request = new ValidateRequest($address, ($textCase ? $textCase : TextCase::$Default), $coordinates);
					vmSetStartTime('avaValAd');
					//avadebug('my request for validate address ',$request);
					$result = $client->Validate($request);
					vmTime('Avatax validate Address','avaValAd');
					//avadebug('Validate ResultCode is: '. $result->getResultCode());;
					if($result->getResultCode() != SeverityLevel::$Success)
					{
						foreach($result->getMessages() as $msg)
						{
							avadebug('fillValidateAvalaraAddress ' . $msg->getName().": ".$msg->getSummary()."\n");
						}
					}
					else
					{

						self::$validatedAddresses = $result->getvalidAddresses();
						$session->set ('vm_avatax_address_checked.' . $hash, TRUE, 'vm');
					}

				}
				catch(SoapFault $exception)
				{
					$msg = "Exception: fillValidateAvalaraAddress ";
					if($exception)
						$msg .= $exception->faultstring;

					$msg .= "\n";
					$msg .= $client->__getLastRequest()."\n";
					$msg .= $client->__getLastResponse()."\n";
					vmError($msg);
				}
			} else {
				self::$validatedAddresses[] = $address;

			}

				//then for BT and/or $cart->STsameAsBT
		}

		if(empty(self::$validatedAddresses)){
			self::$validatedAddresses = FALSE;
		}

		return self::$validatedAddresses;
	}

	private function getCartProducts($calculationHelper){

			if(!class_exists('VirtueMartCart')) require(VMPATH_SITE.DS.'helpers'.DS.'cart.php');
			$cart = VirtueMartCart::getCart();
			$count = count($cart->products);
			if($count===0){
				avadebug('getCartProducts No Product');
				return false;
			}
			$products = $cart->products;
			$prices = $calculationHelper->getCartPrices();
			foreach($products as $k => &$product){
				$product = (array) $product;

			/*	if(!empty($prices[$k]['discountedPriceWithoutTax'])){
					$price = $prices[$k]['discountedPriceWithoutTax'];
					avadebug('getAvatax getCartProducts take discountedPriceWithoutTax for i='.$k.' '.$price);
				} else */
				if(!empty($prices[$k]['basePriceVariant'])){
					$price = $prices[$k]['basePriceVariant'];
				} else {
					avadebug('There is no price in getTax for product '.$k.' ',$prices);
					$price = 0.0;
				}
				$product['price'] = $price;

				if(!empty($prices[$k]['discountAmount'])){
					$product['discount'] = abs($prices[$k]['discountAmount']);
				} else {
					//avadebug('no discount for '.$k,$prices[$k]);
					$product['discount'] = FALSE;
				}
			}

			if(!empty($cart->virtuemart_shipmentmethod_id)){
				$shipment = array();
				$shipment['product_sku'] = 'VMShipmentId_'.$cart->virtuemart_shipmentmethod_id;
				$shipmentModel = VmModel::getModel('Shipmentmethod');
				$shipmentModel->setId($cart->virtuemart_shipmentmethod_id);
				$shipmentMethod = $shipmentModel->getShipment();

				$shipment['product_name'] = $shipmentMethod->shipment_name;
				$shipment['amount'] = 1;
				$shipment['price'] = $prices['shipmentValue'];              //decimal // TotalAmmount
				$shipment['discount'] = 0.0;
				$shipment['shipment'] = 1;
				$products[] = $shipment;
			}

			/*if(!empty($cart->virtuemart_paymentmethod_id)){
				$payment = array();
				$payment['product_sku'] = 'VMPaymentId_'.$cart->virtuemart_paymentmethod_id;
				$paymentModel = VmModel::getModel('Paymentmethod');
				$paymentModel->setId($cart->virtuemart_paymentmethod_id);
				$paymentMethod = $paymentModel->getPayment();
				$payment['product_name'] = $paymentMethod->payment_name;
				$payment['amount'] = 1;
				if(isset($prices['paymentValue'])){
					$payment['price'] = $prices['paymentValue'];              //decimal // TotalAmmount
				} else {
					$payment['price'] = 0.0;
				}
				$payment['discount'] = 0.0;

				$products[] = $payment;
			}*/

			$products['discountAmount'] = abs($prices['discountAmount']);


		return $products;
	}

	function prepareSingleProduct($calculationHelper,$price){

		$products = array();
		$calculationHelper->_product->price = $price;

		$products[0] = $calculationHelper->_product;
		if(!isset($products[0]->amount)){
			$products[0]->amount = 1;
		}

		if(isset($calculationHelper->productPrices['discountAmount'])){
			$products[0]->discount = $calculationHelper->productPrices['discountAmount'];
		} else {
			$products[0]->discount = FALSE;
		}
		return $products;
	}

	private static $_taxResult = NULL;
	function getAvaTax($calc,$products,$invoiceNumber=false,$orderNumber = false){

		if($calc->activated==0) return false;

		if(count($products) == 0){
			$this->blockCheckout();
			return false;
		}

		if(!self::$validatedAddresses and $calc->prevCheckoutAddInv){
			$this->blockCheckout();
			return false;
		}

		$request = $this->createStandardRequest($calc,$products);
		//avadebug('My request to avatax',$request);
		if($orderNumber){
			$request->setPurchaseOrderNo($orderNumber);     //string Optional
		}

		$totalTax = 0.0;
		$hash = '';
		$session = JFactory::getSession ();
		if($calc->committ and $invoiceNumber){
			$request->setDocType(DocumentType::$SalesInvoice);   	// Only supported types are SalesInvoice or SalesOrder
			$request->setCommit(true);
			//invoice number, problem is that the invoice number is at this time not known, but the order_number may reachable
			$request->setDocCode($invoiceNumber);

			self::$_taxResult = FALSE;
			avadebug('Request as SalesInvoice with invoiceNumber '.$invoiceNumber);
		} else {

			$hash = json_encode(self::$vmadd). json_encode($request->getLines()). json_encode($request->getDiscount());
			$hash = md5($hash);

			$request->setDocType(DocumentType::$SalesOrder);
			$request->setCommit(false);
			//invoice number, problem is that the invoice number is at this time not known, neither the order_number
			$request->setDocCode('VM'.$this->vmVersion.'_order_request');

			//Requests are allowed to be cached
			if(!AVATAX_DEBUG) self::$_taxResult = $session->get ('vm_avatax_tax.' . $hash, FALSE, 'vm');
		}
		if(!self::$_taxResult){
			vmSetStartTime('avagetTax');
			self::$_taxResult = $this->executeRequest($request);
			vmTime('Avalara executeRequest hash '.$hash,'avagetTax');
			if(self::$_taxResult!==FALSE){
				$session->set ('vm_avatax_tax.' . $hash,  serialize(self::$_taxResult), 'vm');
			}

		} else {
			if(is_string(self::$_taxResult )){
				self::$_taxResult =  unserialize(self::$_taxResult);
			}
		}

		if(self::$_taxResult){
			if(isset(self::$_taxResult['totalTax'])){
				$totalTax = self::$_taxResult['totalTax'];
			}
		}

		return $totalTax;
	}

	function createStandardRequest($calc,$products,$sign=1){

		if(!class_exists('TaxServiceSoap')) require (VMAVALARA_CLASS_PATH.DS.'TaxServiceSoap.class.php');
		if(!class_exists('DocumentType')) require (VMAVALARA_CLASS_PATH.DS.'DocumentType.class.php');
		if(!class_exists('DetailLevel')) require (VMAVALARA_CLASS_PATH.DS.'DetailLevel.class.php');
		if(!class_exists('Line')) require (VMAVALARA_CLASS_PATH.DS.'Line.class.php');
		if(!class_exists('ServiceMode')) require (VMAVALARA_CLASS_PATH.DS.'ServiceMode.class.php');
		if(!class_exists('Line')) require (VMAVALARA_CLASS_PATH.DS.'Line.class.php');
		if(!class_exists('GetTaxRequest')) require (VMAVALARA_CLASS_PATH.DS.'GetTaxRequest.class.php');
		if(!class_exists('GetTaxResult')) require (VMAVALARA_CLASS_PATH.DS.'GetTaxResult.class.php');
		if(!class_exists('Address')) require (VMAVALARA_CLASS_PATH.DS.'Address.class.php');

		if(is_object($calc)){
			$calc = get_object_vars($calc);
		}
		$request= new GetTaxRequest();
		$origin = new Address();

		//In Virtuemart we have not differenct warehouses, but we have a shipment address
		//So when the vendor has a shipment address, we assume that it is his warehouse
		//Later we can combine products with shipment addresses for different warehouse (yehye, future music)
		//But for now we just use the BT address
		if (!class_exists ('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');

		$userId = VirtueMartModelVendor::getUserIdByVendorId ($calc['virtuemart_vendor_id']);
		$userModel = VmModel::getModel ('user');
		$virtuemart_userinfo_id = $userModel->getBTuserinfo_id ($userId);
		// this is needed to set the correct user id for the vendor when the user is logged
		$userModel->getVendor($calc['virtuemart_vendor_id']);
		$vendorFieldsArray = $userModel->getUserInfoInUserFields ('mail', 'BT', $virtuemart_userinfo_id, FALSE, TRUE);
		$vendorFields = $vendorFieldsArray[$virtuemart_userinfo_id];

		$origin->setLine1($vendorFields['fields']['address_1']['value']);
		$origin->setLine2($vendorFields['fields']['address_2']['value']);
		$origin->setCity($vendorFields['fields']['city']['value']);

		$origin->setCountry($vendorFields['fields']['virtuemart_country_id']['country_2_code']);
		$origin->setRegion($vendorFields['fields']['virtuemart_state_id']['state_2_code']);
		$origin->setPostalCode($vendorFields['fields']['zip']['value']);

		$request->setOriginAddress($origin);	      //Address

		if(isset($this->addresses[0])){
			$destination = $this->addresses[0];
		} else {
			return FALSE;
		}

		if (!class_exists ('calculationHelper')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		$calculator = calculationHelper::getInstance ();
		$request->setCurrencyCode($calculator->_currencyDisplay->_vendorCurrency_code_3); //CurrencyCode
		$request->setDestinationAddress	($destination);     //Address
		$request->setCompanyCode($calc['company_code']);   // Your Company Code From the Dashboard
		$request->setDocDate(date('Y-m-d'));           //date, checked
		$request->setCustomerCode(self::$vmadd['customer_number']);  //string Required

		if(isset(self::$vmadd['tax_usage_type'])){
			$request->setCustomerUsageType(self::$vmadd['tax_usage_type']);   //string   Entity Usage
		}

		if(isset(self::$vmadd['tax_exemption_number'])){
			$request->setExemptionNo(self::$vmadd['tax_exemption_number']);         //string   if not using ECMS which keys on customer code
		}

		if(isset(self::$vmadd['taxOverride'])){

			$request->setTaxOverride(self::$vmadd['taxOverride']);
			avadebug('I set tax override ',self::$vmadd['taxOverride']);
		}

		$setAllDiscounted = false;
		if(isset($products['discountAmount'])){
			if(!empty($products['discountAmount'])){
				//$request->setDiscount($sign * $products['discountAmount'] * (-1));            //decimal
				$request->setDiscount($sign * $products['discountAmount'] );            //decimal
				vmdebug('We sent as discount '.$request->getDiscount());
				$setAllDiscounted = true;
			}
			unset($products['discountAmount']);
		}

		$request->setDetailLevel('Tax');         //Summary or Document or Line or Tax or Diagnostic

		$lines = array();
		$n = 0;
		$this->_lineNumbersToCartProductId = array();

		foreach($products as $k=>$product){

			$n++;
			$this->_lineNumbersToCartProductId[$n] = $k;
			$line = new Line();
			$line->setNo ($n);                  //string  // line Number of invoice
			$line->setItemCode($product['product_sku']);            //string
			$line->setDescription($product['product_name']);         //product description, like in cart, atm only the name, todo add customfields

			if(!empty($product['categories'])){

				//avadebug('AvaTax setTaxCode Product has categories !',$catNames);
				if (!class_exists ('TableCategories')) {
					require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'categories.php');
				}
				$db = JFactory::getDbo();
				$catTable = new TableCategories($db);
				foreach($product['categories'] as $cat){
					$catTable->load ($cat);
					$catslug = $catTable->slug;

					if(strpos($catslug,'avatax-')!==FALSE){
						$taxCode = substr($catslug,7);
						if(!empty($taxCode)){
							$line->setTaxCode($taxCode);
						} else {
							vmError('AvaTax setTaxCode, category could not be parsed '.$catslug);
						}

						break;
					}
				}
			}

			//$line->setTaxCode("");             //string
			$line->setQty($product['amount']);                 //decimal
			$line->setAmount($sign * $product['price'] * $product['amount']);              //decimal // TotalAmmount

			if($setAllDiscounted or !empty($product['discount'])) {
				$line->setDiscounted(1);
			} else {
				$line->setDiscounted(0);
			}

			$line->setRevAcct("");             //string
			$line->setRef1("");                //string
			$line->setRef2("");                //string

			if(isset(self::$vmadd['tax_usage_type'])){
				$line->setCustomerUsageType(self::$vmadd['tax_usage_type']);   //string   Entity Usage
			}

			if(isset(self::$vmadd['tax_exemption_number'])){
				$line->setExemptionNo(self::$vmadd['tax_exemption_number']);         //string   if not using ECMS which keys on customer code
			}

			if(isset(self::$vmadd['taxOverride'])){

				//create new TaxOverride Object set
				//$line->setTaxOverride(self::$vmadd['taxOverride']);
			}

			if(!empty($product['shipment'])){
				if(!empty($calc->taxfreightcode)) $line->setTaxCode($calc->taxfreightcode);
			}
			$lines[] = $line;
		}
		$this->newATConfig($calc);
		$request->setLines($lines);

		return $request;
	}

	function executeRequest($request){

		$prices = array();
		$client = new TaxServiceSoap($this->_connectionType);
		try
		{
			if(!class_exists('TaxLine')) require (VMAVALARA_CLASS_PATH.DS.'TaxLine.class.php');
			if(!class_exists('TaxDetail')) require (VMAVALARA_CLASS_PATH.DS.'TaxDetail.class.php');
			if(!class_exists('SeverityLevel')) require (VMAVALARA_CLASS_PATH.DS.'SeverityLevel.class.php');
			//avadebug('executeRequest $request',$request);
			$_taxResult = $client->getTax($request);
			//avadebug('executeRequest $_taxResult',$_taxResult);


			if ( $_taxResult->getResultCode() == SeverityLevel::$Success){

				//avadebug("DocCode: ".$request->getDocCode() );
				//avadebug("DocId: ".self::$_taxResult->getDocId()."\n");
				//avadebug("TotalAmount: ".self::$_taxResult->getTotalAmount() );

				$totalTax = $_taxResult->getTotalTax();
				$taxlines = $_taxResult->getTaxLines();
				$taxlinexCount = count($taxlines);
				//avadebug('my $request, $taxlines',$taxlines);

				foreach($taxlines as $ctl){

					$nr = $ctl->getNo();
					if(isset($this->_lineNumbersToCartProductId[$nr]) and $nr <= $taxlinexCount){

						$line = $request->getLine($nr);
						//vmdebug('my $line',$line);

						if(strpos($line->getItemCode(),'VMShipmentId')===0){
							$prices['shipmentTax'] = $ctl->getTax();
							$totalTax = $totalTax - $ctl->getTax();
						/*} else if(strpos($line->getItemCode(),'VMPaymentId')===0){

							$prices['paymentTax'] = $ctl->getTax();
							$totalTax = $totalTax - $ctl->getTax();
							vmdebug('VMPaymentId '.$prices['paymentTax']);*/
						}else {
							$quantity = $line->getQty();
							//avadebug('my $request qty ',$quantity);
							//on the long hand, the taxAmount must be replaced by taxAmountQuantity to avoid rounding errors
							$prices[$this->_lineNumbersToCartProductId[$nr]]['taxAmount'] = $ctl->getTax()/$quantity;
							$prices[$this->_lineNumbersToCartProductId[$nr]]['taxAmountQuantity'] = $ctl->getTax();
						}
					} else {
						avadebug('got more lines back, then requested => my $ctl',$ctl);
					}
				}
				$prices['totalTax'] = $totalTax;
			}
			else {
				$this->blockCheckout();
				foreach($_taxResult->getMessages() as $msg){
					vmError($msg->getName().": ".$msg->getSummary(),'AvaTax Error '.$msg->getSummary());
				}
				vmdebug('Error, but no exception in getAvaTax '.SeverityLevel::$Success,$_taxResult);
				return false;
			}
		}
		catch(SoapFault $exception)
		{
			$this->blockCheckout();
			$msg = "Exception: in getAvaTax, while executeRequest ";
			if($exception) $msg .= $exception->faultstring;
			avadebug( $msg,$request);
			return false;
		}
		return $prices;
	}

	public function plgVmOnUpdateOrderPayment($data,$old_order_status){

		if($data->order_status=='X'){
			avadebug('plgVmOnUpdateOrderPayment cancel order for Avatax '.$old_order_status,$data->order_status);
			$this->cancelOrder($data,$old_order_status);
		}

		$toInvoice = VmConfig::get('inv_os',array('C'));
		if(!is_array($toInvoice)) $toInvoice = (array)$toInvoice;
		if($data->order_status=='R' or in_array($data->order_status,$toInvoice)){
			$this->creditMemo($data);
		}
	}

	private function creditMemo($data){

		$orderModel = VmModel::getModel('orders');
		$orderDetails = $orderModel->getOrder($data->virtuemart_order_id);
		$calc = $this->getOrderCalc($orderDetails);
		if(!$calc) return false;

		if(!is_array($calc['avatax_virtuemart_country_id'])){
			$calc['avatax_virtuemart_country_id'] = json_decode($calc['avatax_virtuemart_country_id'],true);
		}

		if($calc['activated']==0){
			avadebug('Avatax creditMemo rule not activated',$calc);
			return false;
		}

		if($calc['accrual'] and $data->order_status != 'R'){
			avadebug('Avatax creditMemo, type is accrual and not a Refund',$calc);
			return false;
		}

		if(!class_exists('TaxServiceSoap')) require (VMAVALARA_CLASS_PATH.DS.'TaxServiceSoap.class.php');
		if(!class_exists('DocumentType')) require (VMAVALARA_CLASS_PATH.DS.'DocumentType.class.php');
		if(!class_exists('DetailLevel')) require (VMAVALARA_CLASS_PATH.DS.'DetailLevel.class.php');
		if(!class_exists('Line')) require (VMAVALARA_CLASS_PATH.DS.'Line.class.php');
		if(!class_exists('ServiceMode')) require (VMAVALARA_CLASS_PATH.DS.'ServiceMode.class.php');
		if(!class_exists('Line')) require (VMAVALARA_CLASS_PATH.DS.'Line.class.php');
		if(!class_exists('GetTaxResult')) require (VMAVALARA_CLASS_PATH.DS.'GetTaxResult.class.php');

		$this->addresses = $this->fillValidateAvalaraAddress($calc,$orderDetails['details']['BT']);

		if(!$this->addresses){
			vmdebug('Avatax: on order status update: no valid addresses');
			return false;
		}

		if(is_object($orderDetails['details']['BT'])){
			self::$vmadd = get_object_vars($orderDetails['details']['BT']);
		} else {
			self::$vmadd = $orderDetails['details']['BT'];
		}

		$toInvoice = VmConfig::get('inv_os',array('C'));
		if(!is_array($toInvoice)) $toInvoice = (array)$toInvoice;

		//Lets find first if the committ was already done, the committ was already done, if one of history orderstatuses
		//have one status for create invoice.
		//vmdebug('my orderDetails ',$orderDetails);
		self::$vmadd['taxOverride'] = null;
		foreach($orderDetails['history'] as $item){
			if(in_array($item->order_status_code,$toInvoice)){
				//the date of the order status used to create the invoice
				self::$vmadd['taxOverride'] = $this->createTaxOverride(substr($item->created_on,0,10),$data->order_status,$item->comments);
				//self::$vmadd['paymentDate'] = substr($item->created_on,0,10);
					//Date when order is created
				//self::$vmadd['taxOverride'] = $orderDetails['details']['BT']->created_on;
				break;
			}
		}

		//Accrual Accounting means the committ is done directly after pressing the confirm button in the cart
		//Therefore the date of the committ/invoice is the first order date and we dont need to check the order history
		if(empty(self::$vmadd['taxOverride']) and $calc['accrual']){
			self::$vmadd['taxOverride'] = $this->createTaxOverride($orderDetails['details']['BT']->created_on,$data->order_status);
		}

		//create the products
		$products = array();
		foreach($orderDetails['items'] as $k => $item){
			$product = array();
			$item = (array)$item;
			//vmdebug('my item',$item);
			$product['product_sku'] = $item['order_item_sku'];
			$product['product_name'] = $item['order_item_name'];
			$product['amount'] = $item['product_quantity'];
			//$product['price'] = $item['product_final_price'];
			$product['price'] = $item['product_item_price'];
			$product['discount'] = $item['product_subtotal_discount'];
			$model = VmModel::getModel('product');
			$rProduct = $model->getProduct($item['virtuemart_product_id']);
			$product['categories'] = $rProduct->categories;
			$products[] = $product;
		}
		if(!empty($orderDetails['details']['BT']->virtuemart_shipmentmethod_id)){
			$shipment = array();
			$shipment['product_sku'] = 'VMShipmentId_'.$orderDetails['details']['BT']->virtuemart_shipmentmethod_id;
			$shipmentModel = VmModel::getModel('Shipmentmethod');
			$shipmentModel->setId($orderDetails['details']['BT']->virtuemart_shipmentmethod_id);
			$shipmentMethod = $shipmentModel->getShipment();
			$shipment['product_name'] = $shipmentMethod->shipment_name;
			$shipment['amount'] = 1;
			$shipment['price'] = $orderDetails['details']['BT']->order_shipment;              //decimal // TotalAmmount
			$shipment['discount'] = 0.0;
			$shipment['shipment'] = 1;
			$products[] = $shipment;
		}
		$products['discountAmount'] = $orderDetails['details']['BT']->order_discountAmount - $orderDetails['details']['BT']->coupon_discount;

		if($data->order_status=='R') {
			$sign = -1;
		} else {
			$sign = 1;
		}

		$request = $this->createStandardRequest($calc,$products,$sign);
		$request->setCompanyCode($calc['company_code']);   // Your Company Code From the Dashboard
		$request->setDocDate(date('Y-m-d'));           //date
		$request->setCustomerCode($orderDetails['details']['BT']->customer_number);  //string Required
		if($orderDetails['details']['BT']->order_number){
			$request->setPurchaseOrderNo($orderDetails['details']['BT']->order_number);     //string Optional
		}
		$totalTax = 0.0;

		$invoiceNumber = 'onr_'.$orderDetails['details']['BT']->order_number;
		vRequest::setVar('create_invoice',1);
		$orderModel -> createInvoiceNumber($orderDetails['details']['BT'],$invoiceNumber);
		if(is_array($invoiceNumber)) $invoiceNumber = $invoiceNumber[0];

		if($calc['committ'] and $invoiceNumber){
			if($data->order_status=='R') {
				$request->setDocType(DocumentType::$ReturnInvoice);
			} else {
				$request->setDocType(DocumentType::$SalesInvoice);
			}

			// Only supported types are SalesInvoice or SalesOrder
			$request->setCommit(true);

			$request->setDocCode($invoiceNumber);
			self::$_taxResult = FALSE;
		}

		vmSetStartTime('avagetTax');
		self::$_taxResult = $this->executeRequest($request);
		vmTime('Avalara executeRequest ','avagetTax');

		if(self::$_taxResult){
			if(isset(self::$_taxResult['totalTax'])){
				$totalTax = self::$_taxResult['totalTax'];
			}
		}

		return $totalTax;
	}

	private function cancelOrder($data,$old_order_status){
	//	vmdebug('my data on cancelOrder ',$data);
	//	if(empty($data->invoice_number)) return false;

		$orderModel = VmModel::getModel('orders');
		$orderDetails = $orderModel->getOrder($data->virtuemart_order_id);
		$calc = $this->getOrderCalc($orderDetails);
		if(!$calc) return false;

		$invoiceNumber = 'onr_'.$orderDetails['details']['BT']->order_number;
		$orderModel -> createInvoiceNumber($orderDetails['details']['BT'],$invoiceNumber);
		if(!$invoiceNumber){
			vmInfo('No invoice created, no reason to cancel at Avatax');
			return false;
		}
		if(is_array($invoiceNumber)) $invoiceNumber = $invoiceNumber[0];#

		if(!function_exists('EnsureIsArray')) require(VMAVALARA_PATH.DS.'AvaTax.php');	// include in all Avalara Scripts
		if(!class_exists('TaxServiceSoap')) require (VMAVALARA_CLASS_PATH.DS.'TaxServiceSoap.class.php');
		if(!class_exists('TaxRequest')) require (VMAVALARA_CLASS_PATH.DS.'TaxRequest.class.php');
		if(!class_exists('CancelTaxRequest')) require (VMAVALARA_CLASS_PATH.DS.'CancelTaxRequest.class.php');

		$this->newATConfig($calc);

		$client = new TaxServiceSoap($this->_connectionType);
		$request= new CancelTaxRequest();

		$request->setDocCode($invoiceNumber);

		$request->setDocType(DocumentType::$SalesInvoice);

		$request->setCompanyCode($calc['company_code']);	// Dashboard Company Code

		if($calc['committ']==0) return false;

		//CancelCode: Enter D for DocDeleted, or P for PostFailed: [D]
		//I do not know the difference, I use always D (I assume this means order got deleted, cancelled, or refund)
		$code = CancelCode::$DocDeleted;
		$request->setCancelCode($code);

		try
		{
			avadebug('plgVmOnCancelPayment used request',$request);
			$result = $client->cancelTax($request);

			if ($result->getResultCode() != "Success")
			{
				$msg = '';
				foreach($result->getMessages() as $rmsg)
				{
					$msg .= $rmsg->getName().": ".$rmsg->getSummary()."\n";
				}
				vmError($msg);
			} else {
				vmInfo('CancelTax ResultCode is: '.$result->getResultCode());
			}
		}
		catch(SoapFault $exception)
		{
			$msg = "Exception: ";
			if($exception)
				$msg .= $exception->faultstring;

			$msg .="\n";
			$msg .= $client->__getLastRequest()."\n";
			$msg .= $client->__getLastResponse()."\n";
			vmError($msg);
		}

	}

	private function getOrderCalc($orderDetails){
		$calc = 0;
		if(!empty($orderDetails['calc_rules'])){
			foreach($orderDetails['calc_rules'] as $rule){
				if($rule->calc_kind=='taxRulesBill' and $rule->calc_mathop == 'avalara'){
					$calc = $rule;
					break;
				}
			}
		}
		if(empty($calc)){
			$id = empty($orderDetails->virtuemart_order_id)? '':$orderDetails->virtuemart_order_id;
			avadebug('Retrieving calculation rule for avatax failed',$id);
			return false;
		}

		if(is_object($calc)){
			$calc = get_object_vars($calc);
		}
		if(!empty($calc['calc_params'])){
			VmTable::bindParameterable ($calc, $this->_xParams, $this->_varsToPushParam);
			return $calc;
		} else {
			avadebug('rule had no parameters',$calc);
			return false;
		}
	}

	private function createTaxOverride($date,$orderStatus='R',$reason=''){

		if(!class_exists('TaxOverride')) require (VMAVALARA_CLASS_PATH.DS.'TaxOverride.class.php');
		if(!class_exists('TaxOverrideType')) require (VMAVALARA_CLASS_PATH.DS.'TaxOverrideType.class.php');
		$taxOverride = new TaxOverride();
		$taxOverride->setTaxOverrideType(TaxOverrideType::$TaxDate);   //TaxOverrideType $None, $TaxAmount, $Exemption, $TaxDate ???
		//$taxOverride->setTaxAmount($value);         //decimal
		$taxOverride->setTaxDate($date);        //date format?

		if(empty($reason)){
			$user = JFactory::getUser();
			$reason = 'Vm_'.$orderStatus.'_by_'.$user->name;
		}
		$taxOverride->setReason($reason);

		return $taxOverride;
	}

	public function blockCheckout(){

		$app = JFactory::getApplication();
		if($app->isSite()){
			if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
			$cart = VirtueMartCart::getCart();
			$cart->blockConfirm();
		}

	}
}

// No closing tag

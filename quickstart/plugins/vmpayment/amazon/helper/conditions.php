<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: amazon.php 8585 2014-11-25 11:11:13Z alatak $
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2014 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */

class vmAmazonConditions {

	private $_currentMethod = null;
	var $languages_region = array(
	'en' => 'UK',
	'de' => 'DE',
	);

	public function checkConditions($cart, $method, $cart_prices) {
		//vmTrace('checkConditions', true);
		//$this->debugLog( $cart_prices['salesPrice'], 'checkConditions','debug');
		$this->_currentMethod = $method;
		if($this->isValidLanguage() && $this->isValidAmount($cart_prices['salesPrice']) && $this->isValidProductCategories($cart) && $this->isValidIP()
		) {
			return true;
		}

		return false;
	}

	/**
	 * in VM, the payment is not showed if the buyer browse in another language
	 * @return bool
	 */

	private function isValidLanguage() {
		return true;
		/*if(!$this->_currentMethod->language_restriction) {
			return true;
		}
		$lang = JFactory::getLanguage();
		$tag = strtolower(substr($lang->get('tag'), 0, 2));
		if(array_key_exists($tag, $this->languages_region) AND $this->languages_region[$tag] == $this->_currentMethod->region) {
			vmdebug('AMAZON checkConditions isValidLanguage false ',$tag);
			return true;
		}

		return false;*/
	}

	/**
	 * @return bool
	 */
	private function isValidCountry($virtuemart_country_id) {
		$countries = array();
		if(!empty($this->_currentMethod->countries)) {
			if(!is_array($this->_currentMethod->countries)) {
				$countries[0] = $this->_currentMethod->countries;
			} else {
				$countries = $this->_currentMethod->countries;
			}
		}
		if(count($countries) == 0 || in_array($virtuemart_country_id, $countries)) {
			return TRUE;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	private function isValidAmount($amount) {
		$this->_currentMethod->min_amount = (float)str_replace(',', '.', $this->_currentMethod->min_amount);
		$this->_currentMethod->max_amount = (float)str_replace(',', '.', $this->_currentMethod->max_amount);
		$amount_cond = ($amount > 0 AND $amount >= $this->_currentMethod->min_amount AND $amount <= $this->_currentMethod->max_amount OR ($this->_currentMethod->min_amount <= $amount AND ($this->_currentMethod->max_amount == 0)));
		if($amount == 0 or !$amount_cond) {
			vmdebug('AMAZON checkConditions $amount_cond false');

			return false;
		}

		return true;
	}


	/**
	 * Exclusion of unsupported items: product categories as “not available via Amazon Payments”.
	 * @param $cart
	 * @return bool
	 */
	private function isValidProductCategories($cart) {
		if(!is_array($this->_currentMethod->exclude_categories)) {
			$exclude_categories[0] = $this->_currentMethod->exclude_categories;
		} else {
			$exclude_categories = $this->_currentMethod->exclude_categories;
		}

		foreach ($cart->products as $product) {
			if(array_intersect($exclude_categories, $product->categories)) {
				vmdebug('AMAZON checkConditions one of the products is not allowed to be payed via amazon ',$product);
				return false;
			}
		}

		return true;
	}

	/**
	 * Switch for enabling / disabling Hidden Button Mode.
	 * @return bool
	 */
	private function isValidIP() {
		if(empty($this->_currentMethod->ip_whitelist)) {
			return true;
		}
		if(!class_exists('ShopFunctions')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
		}
		$clientIp = ShopFunctions::getClientIP();
		if(strpos( $this->_currentMethod->ip_whitelist,';')!==false){
			$ip_whitelist = explode(";", $this->_currentMethod->ip_whitelist);
		} else {
			$ip_whitelist = explode("\n", $this->_currentMethod->ip_whitelist);
		}

		foreach($ip_whitelist as $ip){
			$ip = trim($ip);
			if($ip==$clientIp) return true;
		}
		vmdebug('AMAZON checkConditions isValidIP false '.$clientIp, $ip_whitelist);
		return false;
	}

}
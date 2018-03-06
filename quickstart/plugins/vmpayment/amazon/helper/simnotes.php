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

class simNotes {
	/**
	 * SellerNote can contain Sandbox Simulation string to test the Constraints
	 *
	 * @return null|string
	 */
	public static function getSellerNote () {

		return NULL;

		return self::getSetOrderReferenceSandboxSimulationString();
	}

	/**
	 * @return string
	 */
	public static function getSellerAuthorizationNote ($m) {

		if($m->environment != 'sandbox' AND empty($m->sandbox_error_simulation_auth)) {
			return NULL;
		}

		return self::getSandboxSimulationString( $m->sandbox_error_simulation_auth, $m );
	}


	/**
	 * @return null|string
	 */
	public static function getSellerRefundNote ($m) {

		if($m->environment != 'sandbox' AND empty($m->sandbox_error_simulation_refund)) {
			return NULL;
		}

		return self::getSandboxSimulationString( $m->sandbox_error_simulation_refund, $m );
	}

	/**
	 *
	 */
	public static function getSetOrderReferenceSandboxSimulationString () {

		return NULL;
		if($m->environment != 'sandbox' AND empty($m->sandbox_error_simulation)) {
			return NULL;
		}
		$setOrderReferenceSandboxSimulation = array(
		'InvalidPaymentMethod',
			//'PaymentMethodNotAllowed',
			//	'AmazonRejected',
			//	'TransactionTimedOut',
			//	'ExpiredUnused',
			//	'AmazonClosed',
		);

		return self::getSandboxSimulationString( $setOrderReferenceSandboxSimulation, $m->sandbox_error_simulation );
	}


	/**
	 *
	 * @param $authorizedSimulationReasons
	 * @param $reason
	 * @return null|string
	 */
	public static function getSandboxSimulationString ($reason,$m) {

		if($m->environment != 'sandbox' or empty($reason)) {
			return NULL;
		}


		$sandboxSimulationStrings = array(
		'InvalidPaymentMethod' => '{"SandboxSimulation":{"State":"Declined","ReasonCode":"InvalidPaymentMethod"}}',
			//'PaymentMethodNotAllowed' => '{"SandboxSimulation": {"State":"Declined","ReasonCode":"InvalidPaymentMethod","PaymentMethodUpdateTimeInMins":100}}',
		'AmazonRejected' => '{"SandboxSimulation":{"State":"Declined","ReasonCode":"AmazonRejected" }}',
		'TransactionTimedOut' => '{"SandboxSimulation":{"State":"Declined","ReasonCode":"TransactionTimedOut"}}',
		'ExpiredUnused' => '{"SandboxSimulation":{"State":"Declined","ReasonCode":"ExpiredUnused" ,"ExpirationTimeInMins":1}}',
		'AmazonClosed' => '{"SandboxSimulation":{"State":"Closed", "ReasonCode":"AmazonClosed"}}',
		'Pending' => '{"SandboxSimulation":{"State":"Pending"}}',

		);

		$simulationString = $sandboxSimulationStrings[$reason];

		return $simulationString;

	}
}
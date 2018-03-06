<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment Amazon
 * @version $Id: helper.php 8585 2014-11-25 11:11:13Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
abstract class amazonHelper {
	var $amazonData = null;
	var $plugin = null;
	var $_currentMethod = null;

	public function __construct ($amazonData, $method) {
		$this->amazonData = $amazonData;
		$this->_currentMethod = $method;
	}

	function getAmazonResponseState ($status) {
		$amazonResponseState = new stdClass();

		if ($status->isSetState()) {
			$amazonResponseState->amazon_response_state = $status->getState();
		}
		if ($status->isSetReasonCode()) {
			$amazonResponseState->amazon_response_reasonCode = $status->getReasonCode();
		}
		if ($status->isSetReasonDescription()) {
			$amazonResponseState->amazon_response_reasonDescription = $status->getReasonDescription();
		}


		return $amazonResponseState;
	}

	function getVmReferenceId ($referenceId) {
		$pos = strrpos($referenceId, '-');
		if ($pos === false) {
			return $referenceId;
		} else {
			return substr($referenceId, 0, $pos);
		}

	}


	function getCustomerNotified() {

		if (($this->_currentMethod->erp_mode == "erp_mode_enabled" AND $this->_currentMethod->authorization_mode_erp_enabled != 'automatic_synchronous') ) {
			return true;
		} else {
			return false;
		}
	}

	public function onNotificationNextOperation ($order, $payments, $amazonState) {
		return false;
	}

	protected abstract function getContents ();

	function tableStart ($title) {
		$contents = '<table class="adminlist table">';
		$contents .= '	<tr><th colspan="3">';
		$contents .= $title;
		$contents .= '</th></tr>';
		return $contents;
	}

	function tableEnd () {
		$contents = '</table>';
		return $contents;
	}

	function getRow ($title, $value) {
		$contents = '<tr><td></td><td>';

		$contents .= $title;
		$contents .= '</td><td>';

		$contents .= $value;
		$contents .= '</td></tr>';
		return $contents;
	}

	function getRowFirstCol ($title) {
		$contents = '<tr><td colspan="3">';
		$contents .= $title;
		$contents .= '</td><tr>';

		return $contents;
	}


	public function getContentsResponseMetadata ($responseMetadata) {
		$contents = '';
		if ($responseMetadata->isSetRequestId()) {
			$contents .= '<tr><td>';
			$contents .= "RequestId: ";
			$contents .= '</td><td>';

			$contents .= $responseMetadata->getRequestId();
			$contents .= '</td><td>';
			$contents .= '</td><td>';
			$contents .= '</td></tr>';

		}
		return $contents;

	}

	public function getContentsResponseHeaderMetadata ($responseHeaderMetadata) {
		$contents = '';
		$contents .= '<tr><td>';
		$contents .= "ResponseHeaderMetadata: ";
		$contents .= '</td><td>';

		$contents .= $responseHeaderMetadata;
		$contents .= '</td><td>';
		$contents .= '</td><td>';
		$contents .= '</td></tr>';

		return $contents;

	}
}
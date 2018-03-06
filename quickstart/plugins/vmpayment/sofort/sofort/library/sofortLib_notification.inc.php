<?php
defined('_JEXEC') or die('Restricted access');

/**
 * Instance of this class handles the callback of Payment Network to notify about a status change
 *
 * In rare cases notifications might be doubled or even wrong alltogether (if
 * send by a malicious user). So don't use this to change your status but instead
 * use the transaction id to query the webservice for detailed data (SofortLib_TransactionData)
 *
 * eg: $notificationObj = new SofortLib_Notification();
 *
 * $transactionId = $notificationObj->getNotification();
 *
 * Copyright (c) 2012 SOFORT AG
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:15:47 +0100 (Fri, 23 Nov 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_notification.inc.php 5773 2012-11-23 16:15:47Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_Notification extends SofortLib_Abstract {
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	private $_transactionId = '';
	
	private $_time;
	
	
	/**
	 * Constructor for SofortLib_Notification
	 */
	public function __construct() {
		parent::__construct('', '', '');
	}
	
	
	/**
	 * reads the input and tries to read the transaction id
	 *
	 * @return array transactionid=>status
	 */
	public function getNotification($source = 'php://input') {
		$data = file_get_contents($source);
		$this->log(__CLASS__.' DATA FROM <- file_get_contents'.var_export($data,true));
		$this->log(__CLASS__.' <- FROM _POST '.var_export($_POST,true));
		if (empty($data)) {
			$error = error_get_last();
			$this->log(__CLASS__.' <- '.var_export($error,true));
			$this->errors['error']['message'] = 'file_get_contents in getNotification returned NULL';
			return false;
		}
		//we don't really need a huge parser, simply extract the transaction-id
		if (!preg_match('#<transaction>([0-9a-z-]+)</transaction>#i', $data, $matches)) {
			$this->log(__CLASS__.' <- '.$data);
			$this->errors['error']['message'] = 'could not parse message';
			return false;
		}
		
		$this->_transactionId = $matches[1];
		$this->log(__CLASS__.' <- '.$data);
		preg_match('#<time>(.+)</time>#i', $data, $matches);
		
		if (isset($matches[1])) {
			$this->_time = $matches[1];
		}
		
		return $this->_transactionId;
	}
	
	
	/**
	 * Sending a request is not possible
	 * (non-PHPdoc)
	 * @see SofortLib_Abstract::sendRequest()
	 */
	public function sendRequest() {
		trigger_error('sendRequest() not possible in this case', E_USER_NOTICE);
	}
	
	
	/**
	 * 
	 * Getter for variable time
	 */
	public function getTime() {
		return $this->_time;
	}
	
	
	/**
	 * 
	 * Getter for transaction
	 */
	public function getTransactionId() {
		return $this->_transactionId;
	}
}
?>
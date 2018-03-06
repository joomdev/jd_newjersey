<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * AddCustomer.class.php
 */

/**
 * Adds or updates an exempt customer record in AvaCert and returns the result of operation in a AddCustomerResult object.
 * 
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */
class AddCustomer {
  private $AddCustomerRequest; // AddCustomerRequest

  public function setAddCustomerRequest($value){$this->AddCustomerRequest=$value;} // AddCustomerRequest
  public function getAddCustomerRequest(){return $this->AddCustomerRequest;} // AddCustomerRequest

}

?>

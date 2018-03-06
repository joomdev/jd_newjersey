<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * CommunicationMode.class.php
 */

/**
 * CommunicationMode indicates the mode to use for communicating with the customer.
 * @see InitiateExemptCertRequest
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
*/
class CommunicationMode {
  public static $Email = 'Email';
  public static $Mail = 'Mail';
  public static $Fax = 'Fax';
  
	public static function Values()
	{
		return array(
			CommunicationMode::$Email,
			CommunicationMode::$Mail,
			CommunicationMode::$Fax
					
		);
	}

}

?>

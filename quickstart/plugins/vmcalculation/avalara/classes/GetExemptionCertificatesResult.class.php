<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * GetExemptionCertificatesResult.class.php
 */

/**
 * Result data returned from {@link AvaCertServiceSoap#GetExemptionCertificates}.
 *
 * @see GetExemptionCertificatesRequest
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */

class GetExemptionCertificatesResult extends BaseResult {
  private $ExemptionCertificates; // ArrayOfExemptionCertificate
  private $RecordCount; // int

  public function getExemptionCertificates(){return $this->ExemptionCertificates;} // ArrayOfExemptionCertificate

  public function getRecordCount(){return $this->RecordCount;} // int

}

?>

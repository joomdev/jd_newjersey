<?php

/**
 * Heidelpay response page for Heidelpay plugin
 * @author Heidelberger Paymenrt GmbH <Jens Richter> 
 * @version 15.09.14
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) Heidelberger Payment GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
 
include('../../../../configuration.php');
$config = new JConfig();
//echo $config->password ;

foreach ($_POST as $key => $value) {
	$key = preg_replace('/_x$/', '', trim($key));
	$_POST[$key] = $value;
}

foreach ($_GET as $key => $value) {
	$key = preg_replace('/_x$/', '', trim($key));
	$_GET[$key] = $value;
}

if ( $_SERVER['SERVER_PORT'] == "443" ) {
	$Protocol = "https://";
} else {
	$Protocol = "http://";
}

$PATH = preg_replace('@plugins\/vmpayment\/heidelpay\/heidelpay\/heidelpay_response\.php@','', $_SERVER['SCRIPT_NAME']);
$URL = $_SERVER['HTTP_HOST'] . $PATH ; 

if(preg_match('/^[A-Za-z0-9 _-]+$/',($_GET['on']))){ $on = $_GET['on']; }else{ $on = ''; }
$pm			= (int)$_GET['pm'];

$redirectURL	 = $Protocol.$URL.'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&on='.$on.'&pm='.$pm;
$cancelURL	 = $Protocol.$URL.'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on='.$on.'&pm='.$pm;

function updateHeidelpay($orderID, $connect, $on, $pm) {
	$comment="";
	if ( preg_match('/^[A-Za-z0-9 -]+$/', $orderID , $str)) {
		$link = mysqli_connect($connect->host, $connect->user , $connect->password, $connect->db);
		$result = mysqli_query($link,"SELECT virtuemart_order_id FROM ".$connect->dbprefix."virtuemart_orders"." WHERE  order_number = '".mysqli_real_escape_string($link,$orderID)."';");
		$row = mysqli_fetch_object($result);
		$paymentCode = explode('.' , $_POST['PAYMENT_CODE']);
		if ($_POST['PROCESSING_RESULT'] == "NOK") {
				$comment = $_POST['PROCESSING_RETURN'];
		} elseif ($paymentCode[0] == "PP" or $paymentCode[0] == "IV") {
			
			if($_POST['ACCOUNT_BRAND'] == 'BILLSAFE'){
				if (strtoupper ($_POST['CRITERION_LANG']) == 'DE') {
						$comment = '<b>Bitte &uuml;berweisen Sie uns den Betrag von '.$_POST['CRITERION_BILLSAFE_CURRENCY'].' '.sprintf('%1.2f', $_POST['CRITERION_BILLSAFE_AMOUNT']).' auf folgendes Konto:</b>
						<br /><br/>
						Kontoinhaber : '.$_POST['CRITERION_BILLSAFE_RECIPIENT'].'<br />
						Konto-Nr. : '.$_POST['CRITERION_BILLSAFE_ACCOUNTNUMBER'].'<br />
						Bankleitzahl:  '.$_POST['CRITERION_BILLSAFE_BANKCODE'].'<br />
						Bank: '.$_POST['CRITERION_BILLSAFE_BANKNAME'].'<br />
						IBAN: '.$_POST['CRITERION_BILLSAFE_IBAN'].'<br />
						BIC: '.$_POST['CRITERION_BILLSAFE_BIC'].'<br />
						<br />
						<b>Geben sie bitte im Verwendungszweck UNBEDINGT die Identifikationsnummer<br />
						'.$_POST['CRITERION_BILLSAFE_REFERENCE'].'<br />
						und NICHTS ANDERES an.</b><br /><br/>'.
						$_POST['CRITERION_BILLSAFE_LEGALNOTE'].'<br />
						Bitte &uuml;berweisen Sie den ausstehenden Betrag '.$_POST['CRITERION_BILLSAFE_PERIOD'].' Tage nach dem Sie &uuml;ber den Versand informiert wurden.';
				} else {
						$comment = '<b>Please transfer the amount of '.$_POST['CRITERION_BILLSAFE_CURRENCY'].' '.sprintf('%1.2f', $_POST['CRITERION_BILLSAFE_AMOUNT']).' to the following account:</b>
						<br /><br/>
						Account holder: '.$_POST['CRITERION_BILLSAFE_RECIPIENT'].'<br />
						Account No.: '.$_POST['CRITERION_BILLSAFE_ACCOUNTNUMBER'].'<br />
						Bank Code:  '.$_POST['CRITERION_BILLSAFE_BANKCODE'].'<br />
						Bank: '.$_POST['CRITERION_BILLSAFE_BANKNAME'].'<br />
						IBAN: '.$_POST['CRITERION_BILLSAFE_IBAN'].'<br />
						BIC: '.$_POST['CRITERION_BILLSAFE_BIC'].'<br />
						<br />
						<b>When you transfer the money you HAVE TO use the identification number<br />
						'.$_POST['CRITERION_BILLSAFE_REFERENCE'].'<br />
						as the descriptor and nothing else. Otherwise we cannot match your transaction!</b><br /><br />'.
						$_POST['CRITERION_BILLSAFE_LEGALNOTE'].'<br />
						Please remit the outstanding amount '.$_POST['CRITERION_BILLSAFE_PERIOD'].' days after you have been notified about shipping';
				}
			}else{
				if (strtoupper ($_POST['CRITERION_LANG']) == 'DE') {
						$comment = '<b>Bitte &uuml;berweisen Sie uns den Betrag von '.$_POST['CLEARING_CURRENCY'].' '.$_POST['PRESENTATION_AMOUNT'].' auf folgendes Konto:</b>
						<br /><br/>
						Land : '.$_POST['CONNECTOR_ACCOUNT_COUNTRY'].'<br />
						Kontoinhaber : '.$_POST['CONNECTOR_ACCOUNT_HOLDER'].'<br />
						Konto-Nr. : '.$_POST['CONNECTOR_ACCOUNT_NUMBER'].'<br />
						Bankleitzahl:  '.$_POST['CONNECTOR_ACCOUNT_BANK'].'<br />
						IBAN: '.$_POST['CONNECTOR_ACCOUNT_IBAN'].'<br />
						BIC: '.$_POST['CONNECTOR_ACCOUNT_BIC'].'<br />
						<br />
						<b>Geben sie bitte im Verwendungszweck UNBEDINGT die Identifikationsnummer<br />
						'.$_POST['IDENTIFICATION_SHORTID'].'<br />
						und NICHTS ANDERES an.</b><br />';
				} else {
						$comment = '<b>Please transfer the amount of '.$_POST['CLEARING_CURRENCY'].' '.$_POST['PRESENTATION_AMOUNT'].' to the following account:</b>
						<br /><br/>
						Country: '.$_POST['CONNECTOR_ACCOUNT_COUNTRY'].'<br />
						Account holder: '.$_POST['CONNECTOR_ACCOUNT_HOLDER'].'<br />
						Account No.: '.$_POST['CONNECTOR_ACCOUNT_NUMBER'].'<br />
						Bank Code:  '.$_POST['CONNECTOR_ACCOUNT_BANK'].'<br />
						IBAN: '.$_POST['CONNECTOR_ACCOUNT_IBAN'].'<br />
						BIC: '.$_POST['CONNECTOR_ACCOUNT_BIC'].'<br />
						<br />
						<b>When you transfer the money you HAVE TO use the identification number<br />
						'.$_POST['IDENTIFICATION_SHORTID'].'<br />
						as the descriptor and nothing else. Otherwise we cannot match your transaction!</b><br />';
				}				
			}
		
			if($_POST['ACCOUNT_BRAND'] == 'BARPAY'){
				$comment = '(-'.$_POST['CRITERION_BARPAY_PAYCODE_URL'].'-)
					</b><br />
					</b><br />
					Drucken Sie den Barcode aus oder speichern Sie diesen auf Ihrem mobilen Endger&auml;t.
					Gehen Sie nun zu einer Kasse der 18.000 Akzeptanzstellen in Deutschland und bezahlen
					Sie ganz einfach in bar. In dem Augenblick, wenn der Rechnungsbetrag beglichen wird,
					erh&auml;lt der Online-H&auml;ndler die Information &uuml;ber den Zahlungseingang.Die bestellte Ware
					oder Dienstleistung geht umgehend in den Versand';
			}
		}elseif($paymentCode[0] == "DD"){
			if(strtoupper ($_POST['CRITERION_LANG']) == 'DE'){
				$identCreditor = '';
				if($_POST['IDENTIFICATION_CREDITOR_ID'] != ''){
					$identCreditor = 'und die Gl&auml;ubiger ID: '.$_POST['IDENTIFICATION_CREDITOR_ID'].'<br />';
				}
				$comment = 'Der Betrag wird in den n&auml;chsten Tagen von folgendem Konto abgebucht:<br /><br />
				IBAN: '.$_POST['ACCOUNT_IBAN'].'<br />
				BIC: '.$_POST['ACCOUNT_BIC'].'<br />
				Die Abbuchung enth&auml;lt die Mandatsreferenz-ID: '.$_POST['ACCOUNT_IDENTIFICATION'].'<br />
				'.$identCreditor.'
				<br />Bitte sorgen Sie f&uuml;r ausreichende Deckung auf dem entsprechenden Konto.';
			}else{
				$identCreditor = '';
				if($_POST['IDENTIFICATION_CREDITOR_ID'] != ''){
					$identCreditor = 'and the creditor identifier: '.$_POST['IDENTIFICATION_CREDITOR_ID'].'<br />';
				}
				$comment = 'The amount will be debited from this account within the next days:<br /><br />
				IBAN: '.$_POST['ACCOUNT_IBAN'].'<br />
				BIC: '.$_POST['ACCOUNT_BIC'].'<br />
				The booking contains the mandate reference ID: '.$_POST['ACCOUNT_IDENTIFICATION'].'<br />
				'.$identCreditor.'
				<br />Please ensure that there will be sufficient funds on the corresponding account.';
			}
		}
		
		
		
		if (!empty($row->virtuemart_order_id)) {
	
					$timestamp = time();
					$datum = date("Y-m-d",$timestamp);
					$uhrzeit = date("H:i:s",$timestamp);
					$created_on = $datum." ".$uhrzeit;
					
			$sql = 'INSERT INTO `'.$connect->dbprefix.'virtuemart_payment_plg_heidelpay` SET ' .
					'`virtuemart_order_id` = '.mysqli_real_escape_string($link,$row->virtuemart_order_id). ',' .
					'`order_number` = "'.mysqli_real_escape_string($link,$on). '",' .
					'`virtuemart_paymentmethod_id` = "'.mysqli_real_escape_string($link,$pm). '",' .
					'`unique_id` = "'.mysqli_real_escape_string($link,$_POST['IDENTIFICATION_UNIQUEID']). '",' .
					'`short_id` = "'.mysqli_real_escape_string($link,$_POST['IDENTIFICATION_SHORTID']).'",' .
					'`payment_code` = "'.mysqli_real_escape_string($link,$_POST['PROCESSING_REASON_CODE']). '",' .
					'`comment` = "'.mysqli_real_escape_string($link,$comment). '",' .
					'`payment_methode` = "'.mysqli_real_escape_string($link,$paymentCode[0]). '",' .
					'`payment_type` = "'.mysqli_real_escape_string($link,$paymentCode[1]). '",' .
					'`transaction_mode` = "'.mysqli_real_escape_string($link,$_POST['TRANSACTION_MODE']). '",' .
					'`payment_name` = "'.mysqli_real_escape_string($link,$_POST['CRITERION_PAYMENT_NAME']). '",' .
					'`processing_result` = "'.mysqli_real_escape_string($link,$_POST['PROCESSING_RESULT']). '",' .
					'`secret_hash` = "'.mysqli_real_escape_string($link,trim($_POST['CRITERION_SECRET'])). '",' .
					'`response_ip` = "'.mysqli_real_escape_string($link,$_SERVER['REMOTE_ADDR']). '",'.
					'`created_on` = "'.$created_on. '",'.
					'`modified_on` = "'.$created_on. '",'.
					'`locked_on` = "'.$created_on. '"'.
					';';
					
			$dbEerror = mysqli_query($link,$sql);
		}
	}
}

$returnvalue=$_POST['PROCESSING_RESULT'];
if (!empty($returnvalue)){
	if (strstr($returnvalue,"ACK")) {
		print $redirectURL;
		updateHeidelpay($_POST['IDENTIFICATION_TRANSACTIONID'], $config, $on, $pm);
	} else if ($_POST['FRONTEND_REQUEST_CANCELLED'] == 'true'){
		print $cancelURL ;
	} else {
		updateHeidelpay($_POST['IDENTIFICATION_TRANSACTIONID'], $config, $on, $pm);
		print $cancelURL;
	}
} else {
	echo 'FAIL';
}

?>

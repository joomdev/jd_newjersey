<?php
defined ('_JEXEC') or die();
/**
 * @version $Id: payment_form.php 8517 2014-10-23 11:22:29Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (C) 2012 iStraxx - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

$code2 = $viewData['payment_params']['countryCode'];
$sType = $viewData['payment_params']['sType'];
if ($sType=='part') {
	$imageType='account';
} else {
	$imageType='invoice';
}
// missing house_extension,ysalary,companyName
?>
<!-- KLARNA BOX -->
<?php echo $viewData['payment_params']['checkout']; ?>
<script type="text/javascript">
	<!--
	klarna.countryCode = '<?php echo $viewData['payment_params']['countryCode']; ?>';
	klarna.language = '<?php echo $viewData['payment_params']['langISO']; ?>';
	klarna.sum = '<?php echo $viewData['payment_params']['sum']; ?>';
	klarna.eid = '<?php echo $viewData['payment_params']['eid']; ?>';
	klarna.flag = '<?php echo $viewData['payment_params']['flag']; ?>';
	klarna.unary_checkout = '<?php echo @$viewData['payment_params']['unary_checkout']; ?>';
	klarna.type = '<?php echo $sType ?>';
	klarna.lang_companyNotAllowed = '<?php echo vmText::_ ('VMPAYMENT_KLARNA_COMPANY_NOT_ALLOWED'); ?>';
	klarna.pid = '<?php echo $viewData['payment_params']['payment_id']; ?>';
	if (typeof klarna.red_baloon_content == "undefined" || klarna.red_baloon_content == "") {
		klarna.red_baloon_content = '<?php echo @$viewData['payment_params']['red_baloon_content']; ?>';
		klarna.red_baloon_box = '<?php echo @$viewData['payment_params']['red_baloon_paymentBox']; ?>';
	}

	klarna.lang_personNum = '<?php echo vmText::_ ('VMPAYMENT_KLARNA_PERSON_NUMBER'); ?>';
	klarna.lang_orgNum = '<?php echo vmText::_ ('VMPAYMENT_KLARNA_ORGANISATION_NUMBER'); ?>';

	klarna.select_bday = '<?php echo @$viewData['payment_params']['fields']['birth_day']; ?>';
	klarna.select_bmonth = '<?php echo @$viewData['payment_params']['fields']['birth_month']; ?>';
	klarna.select_byear = '<?php echo @$viewData['payment_params']['fields']['birth_year']; ?>';
	klarna.gender = '<?php echo @$viewData['payment_params']['fields']['gender']; ?>';

	klarna.invoice_ITId = 'klarna_invoice_type';
	// Mapping to the real field names which may be prefixed
	klarna.params = {
		birth_day:'klarna_birth_day',
		birth_month:'klarna_birth_month',
		birth_year:'klarna_birth_year',
		companyName:'klarna_company_name',
		socialNumber:'klarna_socialNumber',
		firstName:'klarna_firstName',
		lastName:'klarna_lastName',
		gender:'klarna_gender',
		street:'klarna_street',
		homenumber:'klarna_homenumber',
		house_extension:'klarna_house_extension',
		city:'klarna_city',
		zipcode:'klarna_zip',
		reference:'klarna_reference',
		phoneNumber:'klarna_phone',
		emailAddress:'klarna_email',
		invoiceType:'klarna_invoice_type',
		shipmentAddressInput:'klarna_shipment_address',
		consent:'klarna_consent'


	}


	//-->
</script>
<?php if ($sType == 'spec') { ?>
<script src="http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js" type="text/javascript"></script>
<?php } ?>
<script type="text/javascript">
	jQuery(function () {
		klarna.methodReady('<?php echo $sType ?>');
	});
</script>
<div class="klarna_baloon" id="klarna_baloon_<?php echo $sType ?>" style="display: none">
	<div class="klarna_baloon_top"></div>
	<div class="klarna_baloon_middle" id="klarna_baloon_content">
		<div></div>
	</div>
	<div class="klarna_baloon_bottom"></div>
</div>
<div class="klarna_red_baloon" id="klarna_red_baloon_<?php echo $sType ?>" style="display: none">
    <div class="klarna_red_baloon_top"></div>
    <div class="klarna_red_baloon_middle" id="klarna_red_baloon_content_<?php echo $sType ?>">
        <div><?php echo @$viewData['payment_params']['red_baloon_content']; ?></div>
    </div>
    <div class="klarna_red_baloon_bottom"></div>
</div>
<div class="klarna_blue_baloon" id="klarna_blue_baloon"
     style="display: none">
	<div class="klarna_blue_baloon_top"></div>
	<div class="klarna_blue_baloon_middle" id="klarna_blue_baloon_content_<?php echo $sType ?>">
		<div></div>
	</div>
	<div class="klarna_blue_baloon_bottom"></div>
</div>
<div class="klarna_box_container">
<div class="klarna_box" id="klarna_box_<?php echo $sType ?>">
<script type="text/javascript">
	openAgreement('<?php echo $viewData['payment_params']['countryCode']; ?>');
</script>
<div class="klarna_box_top">
	<div id="klarna_box_<?php echo $sType ?>_top_right" class="klarna_box_top_right">

		<div class="klarna_box_top_agreement">
			<?php if ($sType == 'spec') { ?>
			<!-- Special payment External js(SPEC) -->
			<a id="specialCampaignPopupLink" href="javascript:ShowKlarnaSpecialPaymentPopup()"></a>
			<?php
		}
		else {
			$popupTotal = ($sType == 'part') ? $viewData['payment_params']['sum'] : $viewData['payment_params']['fee'];
			?>
			<!-- Part/invoice payment External js -->
			<a href="javascript:ShowKlarnaPopup('<?php echo $viewData["payment_params"]["eid"]; ?>', '<?php echo $popupTotal; ?>','<?php echo $sType; ?>')">
				<?php echo vmText::_ ('VMPAYMENT_KLARNA_KLARNA_'.$sType.'_AGREEMENT'); ?>
			</a>
			<!-- payment External js END -->
			<?php } ?>
		</div>
		<div class="klarna_box_bottom_languageInfo">
			<img src="<?php echo VMKLARNAPLUGINWEBASSETS . '/images/' ?>share/notice.png"
			     alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_LANGUAGESETTING_NOTE_' . $code2); ?>"/>
		</div>
	</div>
	<?php
	if ($sType == 'spec') {
		$logo = VMKLARNAPLUGINWEBASSETS . '/images/' . 'logo/klarna_logo.png';
	}
	else {
		//$logo = VMKLARNAPLUGINWEBASSETS . '/images/' . 'logo/klarna_' . $sType . '_' . $code2 . '.png';
		$logo ="https://cdn.klarna.com/public/images/".strtoupper($code2)."/badges/v1/". $imageType ."/".$code2."_". $imageType ."_badge_std_blue.png?height=55&eid=". $viewData['payment_params']['eid'];
	}
	?>
	<img class="klarna_logo" src="<?php echo $logo ?>"
	     alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_IMG_LOGO_'.$sType); ?>"/>
</div>
<div class="klarna_box_bottom">
<div class="klarna_box_bottom_contents">

<div class="klarna_box_bottom_left">
	<div class="klarna_box_bottom_content">
		<?php if ($sType !== 'invoice') { ?>
		<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_' . $sType . '_PAYMENT'); ?></div>
		<ol id="paymentPlan">
			<?php
			if (!empty($viewData['payment_params']['pClasses'])) {
				foreach ($viewData['payment_params']['pClasses'] as $pClass) {
					?>
					<li <?php echo $pClass['class'] ?> >
						<div> <?php echo  $pClass['string'] ?>
						</div>
						<span style="display: none"> <?php echo $pClass['classId'] ?> </span>
					</li>
					<?php
				}
			}
			?>


		</ol>
		<input type="hidden" name="<?php echo $sType ?>_klarna_paymentPlan" value="<?php echo $viewData['payment_params']['paymentPlan']; ?>"
		       class="paymentPlan"/>
	<?php } ?>
		<div class="klarna_box_bottom_content_listPriceInfo">
			<?php echo $viewData['payment_currency_info'] ?></div>
		<?php if ( $code2 == 'nl' and $sType == 'part') { ?>
	<div class="klarna_box_bottom_content_listPriceInfo">
		<img src="<?php echo VMKLARNAPLUGINWEBASSETS . '/images/notice_nl.png' ?> "/>
                            </div>
		<?php } ?>
	</div>

</div>

<div class="klarna_box_bottom_right">
<div class="klarna_box_bottom_content">
<?php if ($code2 != 'de' and $code2 != 'nl') { ?>
<?php // Now it is also asked for account payments
		if ($sType == 'invoice') { ?>
		<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_INVOICE_TYPE'); ?></div>
		<input type="radio" name="<?php echo $sType ?>_klarna_invoice_type" id="private" value="private" checked="checked" class="Klarna_radio"/>
		<div class="klarna_box_bottom_radio_title" style="float: left">
			<label for="private"><?php echo vmText::_ ('VMPAYMENT_KLARNA_INVOICE_TYPE_PRIVATE'); ?></label>
		</div>
		<input type="radio" name="<?php echo $sType ?>_klarna_invoice_type" id="company" value="company" class="Klarna_radio"/>
		<div class="klarna_box_bottom_radio_title" style="float: none">
			<label for="company"><?php echo vmText::_ ('VMPAYMENT_KLARNA_INVOICE_TYPE_COMPANY'); ?></label>
		</div>
		<div class="klarna_box_bottom_input_combo"
		     style="width: 100%; display: none" id="invoice_box_company">
			<div id="left" style="width: 60%">
				<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_COMPANY_NAME'); ?></div>
				<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_COMPANY_NAME'); ?>" type="text"
				       name="klarna_company_name" value="<?php echo @$viewData['payment_params']['fields']['company_name']; ?>"
				       style="width: 98%" />
			</div>
		</div>
	<?php  } ?>
<?php  } ?>
<?php if ($code2 != 'se') { ?>
	<?php if ($code2 != 'de' and $code2 != 'nl') { ?>
		<?php // Now it is also asked for account payments ?>
	<div class="klarna_box_bottom_title"
	     id="invoice_perOrg_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_PERSON_NUMBER'); ?></div>
	<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_SOCIALNUMBER_' . strtoupper ($code2)); ?>" type="text"
	       name="<?php echo $sType ?>_klarna_socialNumber" value="<?php echo @$viewData['payment_params']['fields']['socialNumber']; ?>"
	       class="Klarna_fullwidth"/>
		<?php } ?>


<div class="klarna_box_bottom_input_combo">
	<div class="klarna_left" style="width: 60%">
		<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_FIRST_NAME'); ?></div>
		<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_FIRSTNAME'); ?>" type="text" name="<?php echo $sType ?>_klarna_firstName"
		       value="<?php echo $viewData['payment_params']['fields']['first_name']; ?>" style="width: 98%"/>
	</div>
	<div class="klarna_right" style="width: 40%">
		<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_LAST_NAME'); ?></div>
		<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_LASTNAME'); ?>" type="text" name="<?php echo $sType ?>_klarna_lastName"
		       value="<?php echo $viewData['payment_params']['fields']['last_name']; ?>" style="width: 100%"/>
	</div>
</div>
	<?php if ($code2 == 'de' or $code2 == 'nl') { ?>
	<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_SEX'); ?></div>
	<input type="radio" name="<?php echo $sType ?>_klarna_gender" value="1" id="<?php echo $sType ?>_male"
	       class="Klarna_radio gender"/>
	<div class="klarna_box_bottom_radio_title" style="float: left">
		<label for="<?php echo $sType ?>_male"><?php echo vmText::_ ('VMPAYMENT_KLARNA_SEX_MALE'); ?></label>
	</div>
	<input type="radio" name="<?php echo $sType ?>_klarna_gender" value="0" id="<?php echo $sType ?>_female"
	       class="Klarna_radio gender"/>
	<div class="klarna_box_bottom_radio_title" style="float: none">
		<label for="<?php echo $sType ?>_female"><?php echo vmText::_ ('VMPAYMENT_KLARNA_SEX_FEMALE'); ?></label>
	</div>
	<?php } ?>
<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER'); ?>" type="text" name="<?php echo $sType ?>_klarna_phone"
       value="<?php echo $viewData['payment_params']['fields']['phone']; ?>" class="Klarna_fullwidth"/>
	<?php
	if ($code2 == 'de') {
		$klarna_box_street = "60%";
		$klarna_box_house = "40%";
		$klarna_class = "klarna_left";
	}
	elseif ($code2 == 'nl') {
		$klarna_box_street = "40%";
		$klarna_box_house = "38%";
		$klarna_box_ext = "20%";
		$klarna_class = "klarna_left";
	}
	else {
		$klarna_box_street = "100%";
		$klarna_class = "klarna_right";
	}
	?>
<div class="klarna_box_bottom_input_combo">
	<div class="klarna_left" style="width: <?php echo  $klarna_box_street ?>">
		<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_ADDRESS_STREET'); ?></div>
		<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_STREETADDRESS'); ?>" type="text" name="<?php echo $sType ?>_klarna_street"
		       value="<?php echo $viewData['payment_params']['fields']['street']; ?>" style="width: 98%"/>
	</div>
	<?php if ($code2 == 'de' || $code2 == 'nl') { ?>
	<div class="<?php echo  $klarna_class ?>" style="width: <?php echo  $klarna_box_house ?>">
		<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_ADDRESS_HOMENUMBER'); ?></div>
		<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_HOUSENUMBER'); ?>" type="text" name="<?php echo $sType ?>_klarna_homenumber"
		       value="<?php echo $viewData['payment_params']['fields']['houseNr']; ?>" style="width: 100%"/>
	</div>
	<?php } ?>
	<?php if ($code2 == 'nl') { ?>
	<div class="klarna_right" style="width: <?php echo  $klarna_box_ext ?>">
		<div
			class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_ADDRESS_HOUSENUMBER_ADDITION'); ?></div>
		<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_HOUSE_EXTENSION'); ?>" type="text"
		       name="<?php echo $sType ?>_klarna_house_extension"
		       value="<?php echo @$viewData['payment_params']['fields']['houseExt']; ?>" style="width: 95%"
		       size="5"/>
	</div>
	<?php } ?>
</div>
<div class="klarna_box_bottom_input_combo" style="width: 100%">
	<div class="klarna_left" style="width: 60%">
		<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_ADDRESS_ZIP'); ?></div>
		<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_ZIP'); ?>" type="text" name="<?php echo $sType ?>_klarna_zipcode"
		       value="<?php echo $viewData['payment_params']['fields']['zip']; ?>" style="width: 98%"/>
	</div>
	<div class="klarna_right" style="width: 40%">
		<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_ADDRESS_CITY'); ?></div>
		<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_CITY'); ?>" type="text" name="<?php echo $sType ?>_klarna_city"
		       value="<?php echo $viewData['payment_params']['fields']['city']; ?>" style="width: 100%"/>
	</div>
</div>
	<?php
}
else {
	?>
<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_SOCIALSECURITYNUMBER'); ?></div>
<div class="klarna_box_bottom_content_loader">
	<img src="<?php echo VMKLARNAPLUGINWEBASSETS . '/images/' ?>share/loader1.gif" alt=""/>
</div>
<input type="text" alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_SOCIALNUMBER_SE'); ?>"
       name="<?php echo $sType ?>_klarna_socialNumber" value="<?php echo $viewData['payment_params']['fields']['socialNumber']; ?>"
       class="Klarna_pnoInputField"/>

<div class="referenceDiv" style="display: none">
	<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_REFERENCE'); ?></div>
	<input type="text" alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_REFERENCE'); ?>" name="<?php echo $sType ?>_klarna_reference"
	       value="<?php echo $viewData['payment_params']['fields']['reference']; ?>" class="Klarna_fullwidth"/>
</div>
<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_EMAIL'); ?></div>
<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_EMAIL'); ?>" type="text" name="<?php echo $sType ?>_klarna_emailAddress"
       value="<?php echo $viewData['payment_params']['fields']['email']; ?>" class="Klarna_fullwidth"/>
<br/> <br/>
<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
<input alt="<?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER'); ?>" type="text" name="<?php echo $sType ?>_klarna_phone"
       value="<?php echo $viewData['payment_params']['fields']['phone']; ?>" class="Klarna_fullwidth"/>
<br/> <br/>
<div class="klarna_box_bottom_address" style="display: none">
	<div class="klarna_box_bottom_address_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_DELIVERY_ADDRESS'); ?></div>
	<div class="klarna_box_bottom_address_content"></div>
</div>
<div class="klarna_additional_information">
	<?php echo @$viewData['payment_params']['additional_information']; ?>
</div>

	<?php
}
if ($code2 == 'de' || $code2 == 'nl') {
	?>
<div class="klarna_box_bottom_title"><?php echo vmText::_ ('VMPAYMENT_KLARNA_BIRTHDAY'); ?></div>
<div class="klarna_box_bottom_input_combo" style="width: 100%">
	<div class="klarna_left" style="width: 30%">
		<select style="width: 98%" name="<?php echo $sType ?>_klarna_birth_day"
		        class="selectBox_bday">
			<option value="0"><?php echo vmText::_ ('VMPAYMENT_KLARNA_DATE_DAY'); ?></option>
			<option value="01">01</option>
			<option value="02">02</option>
			<option value="03">03</option>
			<option value="04">04</option>
			<option value="05">05</option>
			<option value="06">06</option>
			<option value="07">07</option>
			<option value="08">08</option>
			<option value="09">09</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
			<option value="13">13</option>
			<option value="14">14</option>
			<option value="15">15</option>
			<option value="16">16</option>
			<option value="17">17</option>
			<option value="18">18</option>
			<option value="19">19</option>
			<option value="20">20</option>
			<option value="21">21</option>
			<option value="22">22</option>
			<option value="23">23</option>
			<option value="24">24</option>
			<option value="25">25</option>
			<option value="26">26</option>
			<option value="27">27</option>
			<option value="28">28</option>
			<option value="29">29</option>
			<option value="30">30</option>
			<option value="31">31</option>
		</select>
	</div>
	<div class="klarna_left" style="width: 40%">
		<select style="width: 98%" name="<?php echo $sType ?>_klarna_birth_month" class="selectBox_bmonth">
			<option value="0" selected="selected"><?php echo vmText::_ ('VMPAYMENT_KLARNA_DATE_MONTH'); ?></option>
			<option value="01"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_1'); ?></option>
			<option value="02"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_2'); ?></option>
			<option value="03"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_3'); ?></option>
			<option value="04"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_4'); ?></option>
			<option value="05"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_5'); ?></option>
			<option value="06"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_6'); ?></option>
			<option value="07"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_7'); ?></option>
			<option value="08"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_8'); ?></option>
			<option value="09"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_9'); ?></option>
			<option value="10"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_10'); ?></option>
			<option value="11"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_11'); ?></option>
			<option value="12"><?php echo vmText::_ ('VMPAYMENT_KLARNA_MONTH_12'); ?></option>
		</select>
	</div>
	<div class="klarna_right" style="width: 30%">
		<select style="width: 100%" name="<?php echo $sType ?>_klarna_birth_year" class="selectBox_year">
			<option selected="0"><?php echo vmText::_ ('VMPAYMENT_KLARNA_DATE_YEAR'); ?></option>
		</select>
	</div>
</div>

<div class="klarna_box_bottom_input_combo" style="width: 100%">
	<div class="klarna_box_bottom_title" style="width: 90%; margin-top: 3px">
		<em><?php echo vmText::_ ('VMPAYMENT_KLARNA_NOTICE_BILLING_SAME_AS_SHIPPING'); ?></em>
	</div>
</div>
	<?php
}
if ($code2 == 'de') {
	$url = $viewData['payment_params']['agb_link'] . '&tmpl=component';
	$document = JFactory::getDocument ();
	$document->addScriptDeclaration ("
	jQuery(document).ready(function($) {
		$('a.agb').click( function(){
			$.facebox({
				iframe: '" . $url . "',
				rev: 'iframe|550|550'
			});
			return false ;
		});

	});
");
	?>
<div class="klarna_box_bottom_input_combo" style="width: 100%">
	<input type="checkbox" name="<?php echo $sType ?>_klarna_consent"
	       id="box_klarna_consent_<?php echo $sType ?>"
	       style="float: left; margin-right: 3px"/>

	<div class="klarna_box_bottom_title" style="width: 80%; margin-top: 3px">Mit der &Uuml;bermittlung der f&uuml;r die
		Abwicklung des Rechnungskaufes und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an
		Klarna bin ich einverstanden. Meine <a
			href="javascript:ShowKlarnaConsentPopup('<?php echo $viewData["payment_params"]["eid"]; ?>','<?php echo $sType; ?>');">Einwilligung</a>
		kann ich jederzeit mit Wirkung f&uuml;r die Zukunft widerrufen. Es gelten die <a class="agb" rel="facebox"
		                                                                                 href="<?php echo $viewData['payment_params']['agb_link']; ?>">AGB</a>
		des H&auml;ndlers.
	</div>
</div>
	<?php } ?>
</div>
</div>
</div>
</div>
</div>
</div>
<input type="hidden" name="<?php echo $sType ?>_klarna_country_2_code" value="<?php echo $viewData['payment_params']['countryCode']; ?>"/>
<?php if ($code2 != 'se') { ?>
<input type="hidden" name="<?php echo $sType ?>_klarna_emailAddress" value="<?php echo $viewData["payment_params"]["fields"]['email']; ?>"/>
<?php } ?>
<!-- END KLARNA BOX -->

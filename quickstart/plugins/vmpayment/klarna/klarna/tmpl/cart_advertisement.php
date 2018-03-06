<?php defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id: cart_advertisement.php 9524 2017-05-05 16:25:08Z Milbo $
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

vmJsApi::css ('style', $assetsPath . VMKLARNAPLUGINWEBROOT . '/klarna/assets/css/');

JHTML::script('klarna_pp.js', VMKLARNAPLUGINWEBASSETS.'/js/', false);
JHTML::script('klarnapart.js', 'https://static.klarna.com:444/external/js/', false);
$document = JFactory::getDocument();
$document->addScriptDeclaration("

jQuery(function(){
	jQuery('.klarna_AdvertBox_bottomMid_readMore a').click( function(){
		InitKlarnaPartPaymentElements('klarna_partpayment', '". $viewData['eid'] ."', '". $viewData['country'] ."');
		ShowKlarnaPartPaymentPopup();
		return false;
	});
});
");
$js = '<script type="text/javascript">jQuery(document).find(".product_price").width("25%");</script>';
$js .= '<style>';
$js .= 'div.klarna_PPBox{z-index: 200 !important;}';
$js .= 'div.cbContainer{z-index: 10000 !important;}';
$js .= 'div.klarna_PPBox_bottomMid{overflow: visible !important;}';
$js .= '</style>';
//$html .= '<br>';
if ($viewData['country'] == 'nl') {
	$js .= '<style>.klarna_PPBox_topMid{width: 81%;}</style>';
}
$document = JFactory::getDocument();
//$document->addScriptDeclaration($js);
?>

<?php
if ($viewData['country']== "nl") {
	$country_width="klarna_PPBox_topMid_nl";
} else {
	$country_width="";
}
?>

<div class="klarna_AdvertisementBox">
 <div id="klarna_partpayment" style="display: none"></div>
                <div class="klarna_AdvertBox_bottomMid_readMore">
                    <a href="#"><?php echo vmText::sprintf('VMPAYMENT_KLARNA_ADVERTISEMENT',$viewData['sFee'] ); ?></a>
                </div>

        <?php
	$notice = (($viewData['country']  == 'nl') ? '<div class="nlBanner"><img src="' . VMKLARNAPLUGINWEBASSETS . '/images/notice_nl.png" /></div>' : "");
	echo $notice;
	 ?>
</div>
<div style="clear: both; height: 80px;"></div>

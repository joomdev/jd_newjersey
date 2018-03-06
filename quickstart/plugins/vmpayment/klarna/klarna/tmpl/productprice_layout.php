<?php defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id: productprice_layout.php 9524 2017-05-05 16:25:08Z Milbo $
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

vmJsApi::css ('style', VMKLARNAPLUGINWEBROOT . '/klarna/assets/css/');

JHTML::script('klarna_pp.js', VMKLARNAPLUGINWEBASSETS.'/js/', false);
JHTML::script('klarnapart.js', 'https://static.klarna.com:444/external/js/', false);
$document = JFactory::getDocument();

$document->addScriptDeclaration("

jQuery(function(){
	jQuery('.klarna_PPBox_bottomMid_readMore a').click( function(){
		InitKlarnaPartPaymentElements('klarna_partpayment', '". $viewData['eid'] ."', '".strtolower($viewData['country'])  ."');
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
$country_width="";
if (strcasecmp($viewData['country'],'nl')==0) {
	$js .= '<style>.klarna_PPBox_topMid_nl{width: 81%;}</style>';
	$country_width="klarna_PPBox_topMid_nl";
}
$document = JFactory::getDocument();
//$document->addScriptDeclaration($js);
?>



<div class="klarna_PPBox">
    <div id="klarna_partpayment" style="display: none"></div>
    <div class="klarna_PPBox_inner">
        <div class="klarna_PPBox_top">
            <span class="klarna_PPBox_topRight"></span>
            <span class="klarna_PPBox_topMid  <?php echo $country_width ?>">
                <p><?php echo vmText::_('VMPAYMENT_KLARNA_PPBOX_FROMTEXT'); ?><label> <?php echo $viewData['defaultMonth'] ?> </label><?php echo vmText::_('VMPAYMENT_KLARNA_PPBOX_MONTHTEXT'); ?><?php echo $viewData['asterisk']; ?></p>
            </span>
            <span class="klarna_PPBox_topLeft"></span>
        </div>
        <div class="klarna_PPBox_bottom">
            <div class="klarna_PPBox_bottomMid">
                <table cellpadding="0" cellspacing="0" width="100%" border="0">
                    <thead>
                        <tr>
                            <th style="text-align: left"><?php echo vmText::_('VMPAYMENT_KLARNA_PPBOX_TH_MONTH'); ?></th>
                            <th style="text-align: right"><?php echo vmText::_('VMPAYMENT_KLARNA_PPBOX_TH_SUM'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
						<?php foreach ($viewData['monthTable'] as $monthTable) { ?>
							<tr>
								<td style = 'text-align: left' >
								<?php echo $monthTable['pp_title'] ?>
								</td>
								<td class='klarna_PPBox_pricetag' >
								<?php echo   $monthTable['pp_price']   ?>
								</td>
							</tr>
						<?php } ?>
                    </tbody>
                </table>
                <div class="klarna_PPBox_bottomMid_readMore">
                    <a href="#"><?php echo vmText::_('VMPAYMENT_KLARNA_PPBOX_READMORE'); ?></a>
                </div>
                <div class="klarna_PPBox_pull" id="klarna_PPBox_pullUp">
                    <img src="<?php echo VMKLARNAPLUGINWEBASSETS ?>/images/productPrice/default/pullUp.png" alt="More info" />
                </div>
            </div>
        </div>
        <div class="klarna_PPBox_pull" id="klarna_PPBox_pullDown">
            <img src="<?php echo VMKLARNAPLUGINWEBASSETS ?>/images/productPrice/default/pullDown.png" alt="More info" />
        </div>
        <?php
	$notice = ((strcasecmp($viewData['country'],'nl')==0) ? '<div class="nlBanner"><img src="' . VMKLARNAPLUGINWEBASSETS . '/images/notice_nl.png" /></div>' : "");
	echo $notice;
	 ?>
    </div>
</div>
<div style="clear: both; height: 80px;"></div>

<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2016 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_priceconfig.php 7004 2013-06-20 08:34:18Z alatak $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); ?>

<table class="admintable" id="show_hide_prices">
<?php	if($showPricesLine) echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SHOW_PRICES', 'show_prices', $show_prices ); ?>
		    <tr>
			<th></th>
			<th><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES_LABEL'); ?></th>
			<th><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES_TEXT'); ?></th>
			<th><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES_ROUNDING'); ?></th>
		    </tr>
<?php
echo VirtuemartViewConfig::writePriceConfigLine($params, 'basePrice', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_BASEPRICE');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'variantModification', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_VARMOD');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'basePriceVariant', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_BASEPRICE_VAR');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'discountedPriceWithoutTax', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_DISCPRICE_WOTAX', 0);
echo VirtuemartViewConfig::writePriceConfigLine($params, 'discountedPriceWithoutTaxTt', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_DISCPRICE_WOTAX_TT', 0);
echo VirtuemartViewConfig::writePriceConfigLine($params, 'priceWithoutTax', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_SALESPRICE_WOTAX', 0);
echo VirtuemartViewConfig::writePriceConfigLine($params, 'priceWithoutTaxTt', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_SALESPRICE_WOTAX_TT', 0);
echo VirtuemartViewConfig::writePriceConfigLine($params, 'taxAmount', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_TAX_AMOUNT', 0);
echo VirtuemartViewConfig::writePriceConfigLine($params, 'taxAmountTt', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_TAX_AMOUNT_TT', 0);
echo VirtuemartViewConfig::writePriceConfigLine($params, 'basePriceWithTax', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_BASEPRICE_WTAX');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'salesPrice', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_SALESPRICE');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'salesPriceTt', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_SALESPRICE_TT');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'salesPriceWithDiscount', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_SALESPRICE_WD');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'discountAmount', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_DISC_AMOUNT');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'discountAmountTt', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_DISC_AMOUNT_TT');
echo VirtuemartViewConfig::writePriceConfigLine($params, 'unitPrice', 'COM_VIRTUEMART_ADMIN_CFG_PRICE_UNITPRICE');
?>
		</table>
		
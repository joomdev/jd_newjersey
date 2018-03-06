<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author RickG
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_pricing.php 9444 2017-02-06 13:18:21Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$js = 'Virtuemart.showprices;';
vmJsApi::addJScript('show_prices',$js,true);
?>
<table>
	<tr>
		<td valign="top">
			<fieldset>
				<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PRICE_CONFIGURATION'); ?></legend>
				<table class="admintable">
					<?php
					echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_PRICE_SHOW_TAX','show_tax',VmConfig::get('show_tax',1));
					echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_PRICE_ASKPRICE','askprice',VmConfig::get('askprice',1));
					echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_PRICE_RAPPENRUNDUNG','rappenrundung',VmConfig::get('rappenrundung',0));
					echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_PRICE_ROUNDINDIG','roundindig',VmConfig::get('roundindig',1));
					echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_PRICE_CVARSWT','cVarswT',VmConfig::get('cVarswT',1));

					echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_PRICE_ORDERBY',$this->orderDirs, 'price_orderby', '', 'value', 'text', VmConfig::get('price_orderby','DESC'));
					?>
				</table>
			</fieldset>
		</td>
		<td valign="top">
			<fieldset>
				<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES'); ?></legend>
				<table class="admintable">
					<?php
					echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SHOW_PRICES','show_prices',VmConfig::get('show_prices',1),1,0,'id="show_prices"');
					?>
				</table>
				<?php
				$params = $this->config->_params;
				$showPricesLine = false;
				include(VMPATH_ADMIN .'/views/config/tmpl/default_priceconfig.php');
                ?>
			</fieldset>
		</td>
	</tr>
</table>
<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus �hler
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: edit.php 9612 2017-07-31 07:52:19Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$js = 'Virtuemart.showprices;';
vmJsApi::addJScript('show_prices',$js,true);
AdminUIHelper::startAdminArea($this);
AdminUIHelper::imitateTabs('start', 'COM_VIRTUEMART_SHOPPERGROUP_NAME');
?>


<form action="index.php" method="post" name="adminForm" id="adminForm">

    <div class="col50">
	<fieldset>
	    <legend><?php echo vmText::_('COM_VIRTUEMART_SHOPPERGROUP_DETAILS'); ?></legend>
	    <table class="admintable">
			<?php
			echo VmHTML::row('input', 'COM_VIRTUEMART_SHOPPERGROUP_NAME', 'shopper_group_name', $this->shoppergroup->shopper_group_name,'class="required"');
			echo VmHTML::row('booleanlist', 'COM_VIRTUEMART_PUBLISHED', 'published', $this->shoppergroup->published);
			/*if($this->showVendors() ){
				echo VmHTML::row('raw','COM_VIRTUEMART_VENDOR', $this->vendorList );
			}*/
			if ($this->shoppergroup->default == 1) {
				?>
				<tr>
					<td width="110" class="key">
					<label for="default">
						<span class="hasTip" title="<?php echo vmText::_('COM_VIRTUEMART_SHOPPERGROUP_DEFAULT_TIP'); ?>">
						<?php echo vmText::_('COM_VIRTUEMART_SHOPPERGROUP_DEFAULT'); ?>
						</span>
					</label>
					</td>
					<td>
						<?php echo JHtml::_('image','menu/icon-16-default.png', vmText::_('COM_VIRTUEMART_SHOPPERGROUP_DEFAULT'), NULL, true); ?>
					</td>
				</tr>
		    <?php }
			echo VmHTML::row('textarea', 'COM_VIRTUEMART_SHOPPERGROUP_DESCRIPTION', 'shopper_group_desc', $this->shoppergroup->shopper_group_desc);

			if ($this->shoppergroup->default < 1) {
				echo VmHTML::row('checkbox', 'COM_VIRTUEMART_SHOPPERGROUP_ADDITIONAL', 'sgrp_additional', $this->shoppergroup->sgrp_additional);
			} else {
				echo '<tr></tr>';
			}
			?>
	    </table>
	</fieldset>

	<fieldset>
	    <legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES') ?></legend>

	    <table class="admintable">
			<?php
			$attributes='id="show_prices"';
			echo VmHTML::row('checkbox','COM_VIRTUEMART_SHOPPERGROUP_ENABLE_PRICE_DISPLAY', 'custom_price_display', $this->shoppergroup->custom_price_display,1,0,$attributes ); ?>
		</table>

        <?php
		$params = $this->shoppergroup;
		$show_prices = $this->shoppergroup->show_prices;
		$showPricesLine = true;
		if(!class_exists('VirtuemartViewConfig')) require (VMPATH_ADMIN .'/views/config/view.html.php');
            include(VMPATH_ADMIN .'/views/config/tmpl/default_priceconfig.php');
        ?>

	</fieldset>
    </div>

    <input type="hidden" name="default" value="<?php echo $this->shoppergroup->default ?>" />
    <input type="hidden" name="virtuemart_shoppergroup_id" value="<?php echo $this->shoppergroup->virtuemart_shoppergroup_id; ?>" />
<?php echo $this->addStandardHiddenToForm(); ?>

</form>

<?php
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea();
?>
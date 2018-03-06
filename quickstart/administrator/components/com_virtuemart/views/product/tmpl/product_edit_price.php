<?php
/**
 *
 * Main product information
 *
 * @package    VirtueMart
 * @subpackage Product
 * @author Max Milbers
 * @todo Price update calculations
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: product_edit_price.php 9413 2017-01-04 17:20:58Z Milbo $
 * http://www.seomoves.org/blog/web-design-development/dynotable-a-jquery-plugin-by-bob-tantlinger-2683/
 */

// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access'); ?>


<?php
$rowColor = 0;
?>
<table class="adminform productPriceTable">

    <tr class="row<?php echo $rowColor?>">
        <td width="120px">
            <div style="text-align: right; font-weight: bold;">
								<span
                                        class="hasTip"
                                        title="<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST_TIP'); ?>">
									<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST') ?>
								</span>
            </div>
        </td>
        <td width="140px"><input
                type="text"
                class="inputbox"
                name="mprices[product_price][]"
                size="12"
                style="text-align:right;"
                value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['costPrice']; ?>"/>
            <input type="hidden"
                   name="mprices[virtuemart_product_price_id][]"
                   value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['virtuemart_product_price_id']; ?>"/>
        </td>
        <td width="185px" >
			<?php echo $this->lists['currencies']; ?>
        </td>
		<td >
			  </td>
		<td style="background: #d5d5d5;padding:0;width:1px;"></td>

        <td colspan="2">
 			<span class="hasTip" style="font-weight: bold;"
               title="<?php echo vmText::_ ('COM_VIRTUEMART_SHOPPER_FORM_GROUP_PRICE_TIP'); ?>">
						<?php echo vmText::_('COM_VIRTUEMART_SHOPPER_FORM_GROUP') ?></span>
			<?php echo $this->lists['shoppergroups'];  ?>
        </td>
    </tr>
	<?php $rowColor = 1 - $rowColor; ?>
    <tr class="row<?php echo $rowColor?>">
        <td>
            <div style="text-align: right; font-weight: bold;">
								<span
                                        class="hasTip"
                                        title="<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE_TIP'); ?>">
									<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_FORM_PRICE_BASE') ?>
								</span>
            </div>
        </td>
        <td><input
                type="text"
                readonly
                class="inputbox readonly"
                name="mprices[basePrice][]"
                size="12"
                value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['basePrice']; ?>"/>&nbsp;
			<?php echo $this->vendor_currency_symb;   ?>
        </td>
		<?php /*    <td width="17%"><div style="text-align: right; font-weight: bold;">
							<?php echo vmText::_('COM_VIRTUEMART_RATE_FORM_VAT_ID') ?></div>
                        </td> */ ?>
        <td  >
			<?php echo $this->lists['taxrates']; ?><br/>
        </td>
        <td>
	                        <span class="hasTip" title="<?php echo vmText::_ ('COM_VIRTUEMART_RULES_EFFECTING_TIP') ?>">
							<?php echo vmText::_ ('COM_VIRTUEMART_TAX_EFFECTING') . '<br />' . $this->taxRules ?>
		                    </span>
        </td>
		<td style="background: #d5d5d5;padding:0;width:1px;"></td>
        <td>
			<?php   ?>
        </td>
        <td>
			<?php   ?>
        </td>
    </tr>
	<?php $rowColor = 1 - $rowColor; ?>
    <tr class="row<?php echo $rowColor?>">
        <td>
            <div style="text-align: right; font-weight: bold;">
				<span
                        class="hasTip"
                        title="<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL_TIP'); ?>">
					<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_FORM_PRICE_FINAL') ?>
				</span>
            </div>
        </td>
        <td><input
                type="text"
                name="mprices[salesPrice][]"
                size="12"
                style="text-align:right;"
                value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['salesPriceTemp']; ?>"/>
			<?php echo $this->vendor_currency_symb;   ?>
        </td>
		<?php /*  <td width="17%"><div style="text-align: right; font-weight: bold;">
							<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_TYPE') ?></div>
                        </td>*/ ?>
        <td  >
			<?php echo $this->lists['discounts']; ?> <br/>
        </td>
        <td>
	                    <span class="hasTip" title="<?php echo vmText::_ ('COM_VIRTUEMART_RULES_EFFECTING_TIP') ?>">
						<?php if (!empty($this->DBTaxRules)) {
		                    echo vmText::_ ('COM_VIRTUEMART_RULES_EFFECTING') . '</span><br />' . $this->DBTaxRules . '<br />';

	                    }
		                if (!empty($this->DATaxRules)) {
			               echo vmText::_ ('COM_VIRTUEMART_RULES_EFFECTING') . '<br />' . $this->DATaxRules;
		                }

		                    // 						vmdebug('my rules',$this->DBTaxRules,$this->DATaxRules); echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCOUNT_EFFECTING').$this->DBTaxRules;  ?>
						</span>
        </td>
		<td style="background: #d5d5d5;padding:0;width:1px;"></td>
        <td  nowrap>
			<?php echo  vmJsApi::jDate ($this->product->allPrices[$this->product->selectedPrice]['product_price_publish_up'], 'mprices[product_price_publish_up][]'); ?>
        </td>
        <td  nowrap>
			<?php echo  vmJsApi::jDate ($this->product->allPrices[$this->product->selectedPrice]['product_price_publish_down'], 'mprices[product_price_publish_down][]'); ?>
        </td>
    </tr>

<?php $rowColor = 1 - $rowColor; ?>
    <tr class="row<?php echo $rowColor?>">

        <td width="60px">
            <div style="text-align: right; font-weight: bold;">
				<span
                        class="hasTip"
                        title="<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE_TIP'); ?>">
					<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE') ?>
				</span>
            </div>
        </td>
        <td   colspan="3">
<div style="margin-right: 20px; display: inline">
			<input type="text"
                   size="12"
                   style="text-align:right;" name="mprices[product_override_price][]"
                   value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['product_override_price'] ?>"/>
			<?php echo $this->vendor_currency_symb;   ?>
</div>
			<?php
			$options = array(0 => vmText::_ ('JNO'), 1 => vmText::_ ('JYES'));
			// echo VmHtml::radioList ('mprices[use_desired_price][' . $this->priceCounter . ']', $this->product->override, $options);
			echo '<input type="checkbox" name="mprices[use_desired_price][' . $this->priceCounter . ']" value="1"/>'
			?>
			<strong>
			<span
				class="hasTip"
				title="<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_FORM_CALCULATE_PRICE_FINAL_TIP'); ?>">
			<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_FORM_CALCULATE_PRICE_FINAL'); ?>
			</span>
			</strong>

			<br />
 <?php
			// 							echo VmHtml::checkbox('override',$this->product->override);
			$options = array(0 => vmText::_ ('COM_VIRTUEMART_DISABLED'), 1 => vmText::_ ('COM_VIRTUEMART_OVERWRITE_FINAL'), -1 => vmText::_ ('COM_VIRTUEMART_OVERWRITE_PRICE_TAX'));

			echo VmHtml::radioList ('mprices[override][' . $this->priceCounter . ']', $this->product->allPrices[$this->product->selectedPrice]['override'], $options,'',' ');
			?>
        </td>
		<td style="background: #d5d5d5;padding:0;width:1px;"></td>
        <td>
            <div style="font-weight: bold;">
				<?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_PRICE_QUANTITY_RANGE') ?>
            </div>
            <input type="text"
                   size="12"
                   style="text-align:right;" name="mprices[price_quantity_start][]"
                   value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['price_quantity_start'] ?>"/>
        </td>
        <td>
            <br/>
            <input type="text"
                   size="12"
                   style="text-align:right;" name="mprices[price_quantity_end][]"
                   value="<?php echo $this->product->allPrices[$this->product->selectedPrice]['price_quantity_end']  ?>"/>
        </td>
    </tr>
</table>




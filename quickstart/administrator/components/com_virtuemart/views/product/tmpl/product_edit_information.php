<?php
/**
 *
 * Main product information
 *
 * @package	VirtueMart
 * @subpackage Product
 * @author Max Milbers
 * @todo Price update calculations
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: product_edit_information.php 9674 2017-11-16 14:17:23Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');



// set row counter
$i=0;
?>


<fieldset>
	<legend><?php
		$parentRel = '';
		if ($this->product->product_parent_id) {
			$parentRel = vmText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_PARENT',JHtml::_('link', JRoute::_('index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$this->product->product_parent_id),
				($this->product_parent->product_name), array('title' => vmText::_('COM_VIRTUEMART_EDIT').' '.vRequest::vmSpecialChars($this->product_parent->product_name))).' =&gt; ');
		}
		echo vmText::sprintf('COM_VIRTUEMART_PRODUCT_INFORMATION',$parentRel);
		echo ' id: '.$this->product->virtuemart_product_id ?>
	</legend>
    <table class="adminform" width="100%">
		<tr class="row<?php echo $i?>">
			<td style="min-width:75px;max-width:200px;width:130px;">
				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME') ?>
			</td>
			<td>
				<input class="required inputbox" type="text" name="product_name" id="product_name" value="<?php echo $this->product->product_name; ?>" size="32" maxlength="255" />
				<?php echo $this->origLang ?>
			</td>
			<td colspan="2">
				<label><?php echo VmHTML::checkbox('published', $this->product->published); ?><?php echo vmText::_('COM_VIRTUEMART_PUBLISHED') ?></label>
				<label><?php echo VmHTML::checkbox('product_special', $this->product->product_special); ?> <?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_SPECIAL') ?></label>
				<label><?php echo VmHTML::checkbox('product_discontinued', $this->product->product_discontinued); echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCONTINUED') ?></label>
			</td>
			<td>
				<span class="hastip" title="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_ALIAS_TIP');?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_ALIAS') ?></span>
			</td>
			<td height="18" >
				<input type="text" class="inputbox"  name="slug" id="slug" value="<?php echo $this->product->slug; ?>" size="32" maxlength="255" />
				<?php echo $this->origLang ?>
			</td>
		</tr>

		<?php $i = 1 - $i; ?>
		<tr class="row<?php echo $i?>">
			<td>
				<span  class="hastip" title="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_SKU_TIP') ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_SKU') ?></span>
			</td>
			<td>
				<input type="text" class="inputbox" name="product_sku" id="product_sku" value="<?php echo $this->product->product_sku; ?>" size="32" maxlength="255" />
			</td>
			<td width="130px">
				<span class="hastip" title="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_GTIN_TIP') ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_GTIN') ?></span>
			</td>
			<td>
				<input type="text" class="inputbox" name="product_gtin" id="product_gtin" value="<?php echo $this->product->product_gtin; ?>" size="32" maxlength="64" />
			</td>
			<td>
				<span class="hastip" title="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_MPN_TIP') ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_MPN') ?></span>
			</td>
			<td>
				<input type="text" class="inputbox" name="product_mpn" id="product_mpn" value="<?php echo $this->product->product_mpn; ?>" size="32" maxlength="64" />
			</td>
		</tr>

		<?php $i = 1 - $i; ?>
		<tr class="row<?php echo $i?>">
			<?php if(isset($this->lists['manufacturers'])) { ?>
				<td>
					<?php echo vmText::_('COM_VIRTUEMART_MANUFACTURER') ?>
				</td>
				<td>
					<?php echo $this->lists['manufacturers'];?>
				</td>
			<?php } else {
				echo '<td></td><td></td>';
			}?>
			<td>
				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS_PAGE') ?>
			</td>
			<td>
				<?php echo JHTML::_('Select.genericlist', $this->productLayouts, 'layout', 'size=1', 'value', 'text', $this->product->layout); ?>
			</td>
			<td>
				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_URL') ?>
			</td>
			<td>
				<input type="text" class="inputbox" name="product_url" value="<?php echo $this->product->product_url; ?>" size="32" maxlength="255" />
			</td>
		</tr>
		<?php $i = 1 - $i; ?>
		<tr class="row<?php echo $i?>">
			<td>
				<?php echo vmText::_('COM_VIRTUEMART_CATEGORY_S') ?>
			</td>
			<td>
				<select class="vm-drop" id="categories" name="categories[]" multiple="multiple"  data-placeholder="<?php echo vmText::_('COM_VIRTUEMART_DRDOWN_SELECT_SOME_OPTIONS')  ?>" size="100" >

				</select>			</td>
			<?php
			// It is important to have all product information in the form, since we do not preload the parent
			// I place the ordering here, maybe we make it editable later.
			if(!isset($this->product->ordering)) {
				$this->product->ordering = 0;
				?><input type="hidden" value="<?php echo $this->product->ordering ?>" name="ordering"> <?php
			} ?>
			<td>
				<span class="hasTip" title="<?php echo vmText::_ ('COM_VIRTUEMART_SHOPPER_FORM_GROUP_PRODUCT_TIP'); ?>">
				<?php echo vmText::_('COM_VIRTUEMART_SHOPPER_FORM_GROUP') ?></span>
			</td>
			<td>
				<?php echo $this->shoppergroupList; ?>
			</td>
			<?php if($this->showVendors()) { ?>
			<td>
				<?php echo vmText::_('COM_VIRTUEMART_VENDOR') ?>
			</td>
			<td>
				<?php echo $this->lists['vendors'];?>
			</td>
		<?php } else {
				echo '<td><td>';
			}?>
		</tr>
	</table>
</fieldset>

			<!-- Product pricing -->
			<fieldset>
			    <legend>
				    <?php
					echo vmText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_PRICES',$this->activeShoppergroups);
					if ($this->deliveryCountry) {
						echo vmText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_PRICES_COUNTRY', $this->deliveryCountry  );
					}
					if ($this->deliveryState)  {
						echo  vmText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_PRICES_STATE',$this->deliveryState   );
					}
					?>

				</legend>

				<?php
				//$product = $this->product;
			
				if (empty($this->product->prices)) {
					$this->product->prices[] = array();
				}
	$this->i = 0;
	$rowColor = 0;

	$calculator = $this->calculator;
	$currency_model = VmModel::getModel ('currency');
	$currencies = $currency_model->getCurrencies ();
	$nbPrice = count ($this->product->allPrices);
	$this->priceCounter = 0;
	$this->product->allPrices[$nbPrice] = VmModel::getModel()->fillVoidPrice();


	?>
    <table border="0" width="100%" cellpadding="2" cellspacing="3" id="mainPriceTable" class="adminform  ">
        <tbody id="productPriceBody">
		<?php

		foreach ($this->product->allPrices as $k => $sPrices) {


			if(empty($this->product->allPrices[$k]['product_currency'])){
				$this->product->allPrices[$k]['product_currency'] = $this->vendor->vendor_currency;
			}

			$this->product->selectedPrice = $k;
			$this->calculatedPrices = $calculator->getProductPrices ($this->product);
			$this->product->allPrices[$k] = array_merge($this->product->allPrices[$k],$this->calculatedPrices);

			$currency_model = VmModel::getModel ('currency');
			$this->lists['currencies'] = JHtml::_ ('select.genericlist', $currencies, 'mprices[product_currency][' . $this->priceCounter . ']', '', 'virtuemart_currency_id', 'currency_name', $this->product->allPrices[$k]['product_currency']);

			$DBTax = ''; //vmText::_('COM_VIRTUEMART_RULES_EFFECTING') ;
			foreach ($calculator->rules['DBTax'] as $rule) {
				$DBTax .= $rule['calc_name'] . '<br />';
			}
			$this->DBTaxRules = $DBTax;

			$tax = ''; //vmText::_('COM_VIRTUEMART_TAX_EFFECTING').'<br />';
			foreach ($calculator->rules['Tax'] as $rule) {
				$tax .= $rule['calc_name'] . '<br />';
			}
			foreach ($calculator->rules['VatTax'] as $rule) {
				$tax .= $rule['calc_name'] . '<br />';
			}
			$this->taxRules = $tax;

			$DATax = ''; //vmText::_('COM_VIRTUEMART_RULES_EFFECTING');
			foreach ($calculator->rules['DATax'] as $rule) {
				$DATax .= $rule['calc_name'] . '<br />';
			}
			$this->DATaxRules = $DATax;

			if (!isset($this->product->product_tax_id)) {
				$this->product->product_tax_id = 0;
			}
			if (!isset($this->product->allPrices[$k]['product_tax_id'])) {
				$this->product->allPrices[$k]['product_tax_id'] = 0;
			}
			$this->lists['taxrates'] = ShopFunctions::renderTaxList ($this->product->allPrices[$k]['product_tax_id'], 'mprices[product_tax_id][' . $this->priceCounter . ']');
			if (!isset($this->product->allPrices[$k]['product_discount_id'])) {
				$this->product->allPrices[$k]['product_discount_id'] = 0;
			}
			$this->lists['discounts'] = $this->renderDiscountList ($this->product->allPrices[$k]['product_discount_id'], 'mprices[product_discount_id][' . $this->priceCounter . ']');

			$this->lists['shoppergroups'] = ShopFunctions::renderShopperGroupList ($this->product->allPrices[$k]['virtuemart_shoppergroup_id'], false, 'mprices[virtuemart_shoppergroup_id][' . $this->priceCounter . ']');

			if ($this->priceCounter == $nbPrice) {
				$tmpl = "productPriceRowTmpl";
				$this->product->allPrices[$k]['virtuemart_product_price_id'] = '';
			} else {
				$tmpl = "productPriceRowTmpl_" . $this->priceCounter;
			}

			?>
        <tr id="<?php echo $tmpl ?>" class="removable row<?php echo $rowColor?>">
	            <td width="100%">
		        <span class="vmicon vmicon-16-move price_ordering"></span>
		        <?php /* <span class="vmicon vmicon-16-new price-clone" ></span> */ ?>
                <span class="vmicon vmicon-16-remove price-remove"></span>
				<?php //echo vmText::_ ('COM_VIRTUEMART_PRODUCT_PRICE_ORDER');
				echo $this->loadTemplate ('price'); ?>
			 </td>
        </tr>
			<?php
			$this->priceCounter++;
		}
		?>
        </tbody>
    </table>
    <div class="button2-left btn-wrapper">
        <div class="blank">
            <a  class="btn btn-small" href="#" id="add_new_price"><?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_ADD_PRICE') ?> </a>
        </div>
    </div>

</fieldset>


<?php
if ($this->product->virtuemart_product_id) {
	$link=JRoute::_('index.php?option=com_virtuemart&view=product&task=createChild&virtuemart_product_id='.$this->product->virtuemart_product_id.'&'.JSession::getFormToken().'=1' );
	$add_child_button="";
} else {
$link="";
$add_child_button=" not-active";
}

echo '<div class="button2-left '.$add_child_button.' btn-wrapper">
	<div class="blank">';
		if ($link) {
			echo  '<a href="'. $link .'" class="btn btn-small">';
			} else {
			echo  '<span class="hasTip" title="'.vmText::_ ('COM_VIRTUEMART_PRODUCT_ADD_CHILD_TIP').'">';
				}
echo  vmText::_('COM_VIRTUEMART_PRODUCT_ADD_CHILD');
if ($link) {
	echo  '</a>';
} else{
	echo  '</span>';
}
?>
	</div>
</div>
<span class="hastip" title="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_PARENTID_TIP') ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_PARENTID') ?></span>
<input type="text" class="inputbox" name="product_parent_id" id="product_parent_id" value="<?php echo $this->product->product_parent_id; ?>" size="16" maxlength="64" />

<div class="clear"></div>

		<fieldset>
			<legend>
				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_PRINT_INTNOTES'); ?>
			</legend>
			<textarea style="width: 100%;" class="inputbox" name="intnotes" id="intnotes" cols="35" rows="6"><?php echo $this->product->intnotes; ?></textarea>
		</fieldset>

<?php

$j = 'jQuery(document).ready(function ($) {
        jQuery("#mainPriceTable").dynoTable({
            removeClass: ".price-remove", //remove class name in  table
            cloneClass: ".price-clone", //Custom cloner class name in  table
            addRowTemplateId: "#productPriceRowTmpl", //Custom id for  row template
            addRowButtonId: "#add_new_price", //Click this to add a price
            lastRowRemovable:true, //let the table be empty.
            orderable:true, //prices can be rearranged
            dragHandleClass: ".price_ordering", //class for the click and draggable drag handle
            onRowRemove:function () {
            },
            onRowClone:function () {
            },
            onRowAdd:function () {
                //$(\'select\').chosen(\'destroy\');
                //Virtuemart.updateChosenDropdownLayout($);
                //$(".chzn-single").chosen();
               // $(\'select\').trigger(\'chosen:updated\');
            },
            onTableEmpty:function () {
            },
            onRowReorder:function () {
            }
        });
    });';
vmJsApi::addJScript('dynotable_ini',$j);
?>

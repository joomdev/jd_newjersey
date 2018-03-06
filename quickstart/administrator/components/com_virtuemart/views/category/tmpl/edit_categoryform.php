<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author RickG, jseros, Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


$mainframe = JFactory::getApplication();
?>
<table class="adminform">
	<tr>
		<td valign="top" colspan="2">
			<fieldset>
				<legend><?php echo vmText::_('COM_VIRTUEMART_FORM_GENERAL'); ?></legend>
				<table width="100%" border="0">
					<?php echo VmHTML::row('raw','COM_VIRTUEMART_CATEGORY_NAME',VmHtml::input('category_name',$this->category->category_name,'class="required inputbox"').$this->origLang); ?>
					<?php echo VmHTML::row('raw','COM_VIRTUEMART_SLUG',VmHtml::input('slug',$this->category->slug).$this->origLang); ?>
					<?php //echo VmHTML::row('input','COM_VIRTUEMART_SLUG','slug',$this->category->slug); ?><?php //echo $this->origLang ?>
					<?php echo VmHTML::row('checkbox','COM_VIRTUEMART_PUBLISHED','published',$this->category->published); ?>
					<?php if($this->showVendors() ){
						echo VmHTML::row('checkbox','COM_VIRTUEMART_SHARED', 'shared', $this->category->shared );
						echo VmHTML::row('raw','COM_VIRTUEMART_VENDOR', $this->vendorList );
					} ?>
				</table>
			</fieldset>
        </td>
        <td valign="top" style="width: 50%;" rowspan="2">
            <fieldset>
                <legend><?php echo vmText::_('COM_VIRTUEMART_METAINFO'); echo $this->origLang; ?></legend>
				<?php echo shopFunctions::renderMetaEdit($this->category); ?>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td valign="top" colspan="2">
			<fieldset>
				<legend><?php echo vmText::_('COM_VIRTUEMART_DETAILS'); ?></legend>
				<table>
					<?php echo VmHTML::row('raw','COM_VIRTUEMART_CATEGORY_ORDERING', ShopFunctions::getEnumeratedCategories(true, true, $this->parent->virtuemart_category_id, 'ordering', '', 'ordering', 'category_name', $this->category->ordering) ); ?>
					<?php $categorylist = '
						<select name="category_parent_id" id="category_parent_id" class="inputbox">
							<option value="">'.vmText::_('COM_VIRTUEMART_CATEGORY_FORM_TOP_LEVEL').'</option>
							'.$this->categorylist.'
						</select>';
					echo VmHTML::row('raw','COM_VIRTUEMART_CATEGORY_FORM_PARENT', $categorylist ); ?>
					<?php //echo VmHTML::row('input','COM_VIRTUEMART_CATEGORY_FORM_PRODUCTS_PER_ROW','products_per_row',$this->category->products_per_row,'','',4); ?>
					<?php echo VmHTML::row('input','COM_VIRTUEMART_CATEGORY_FORM_LIMIT_LIST_STEP','limit_list_step',$this->category->limit_list_step,'','',4); ?>
					<?php echo VmHTML::row('input','COM_VIRTUEMART_CATEGORY_FORM_INITIAL_DISPLAY_RECORDS','limit_list_initial',$this->category->limit_list_initial,'','',4); ?>
					<?php //echo VmHTML::row('select','COM_VIRTUEMART_CATEGORY_FORM_TEMPLATE', 'category_template', $this->jTemplateList ,$this->category->category_template,'','value', 'name',false) ; ?>
					<?php //echo VmHTML::row('select','COM_VIRTUEMART_CATEGORY_FORM_BROWSE_LAYOUT', 'category_layout', $this->categoryLayouts ,$this->category->category_layout,'','value', 'text',false) ; ?>
					<?php //echo VmHTML::row('select','COM_VIRTUEMART_CATEGORY_FORM_FLYPAGE', 'category_product_layout', $this->productLayouts ,$this->category->category_product_layout,'','value', 'text',false) ; ?>
				</table>
			</fieldset>
		</td>

	</tr>
	<tr>
		<?php if($this->showVendors() ){

		} ?>
	</tr>
</table>

<?php echo VmHTML::row('editor','COM_VIRTUEMART_DESCRIPTION','category_description',$this->category->category_description); ?>

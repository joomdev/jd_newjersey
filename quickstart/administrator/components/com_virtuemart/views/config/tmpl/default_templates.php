<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_templates.php 7073 2013-07-15 16:24:50Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$params = VmConfig::loadConfig();

?>
<table width="100%">
<tr>
<td valign="top" width="50%">

    <?php
    $type = 'checkbox';
    require (VMPATH_ADMIN .'/views/config/tmpl/template_params.php');
    ?>

    <fieldset>
        <legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_LAYOUT_SETTINGS'); ?></legend>
        <table class="admintable">
			<?php
			echo VmHTML::row('genericlist','COM_VIRTUEMART_SELECT_DEFAULT_SHOP_TEMPLATE',$this->jTemplateList, 'vmtemplate', 'size=1 width=200', 'value', 'name', VmConfig::get('vmtemplate', ''));
			echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_CATEGORY_TEMPLATE',$this->jTemplateList, 'categorytemplate', 'size=1 width=200', 'value', 'name', VmConfig::get('categorytemplate', ''));

			echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_CART_LAYOUT', $this->cartLayoutList, 'cartlayout', 'size=1', 'value', 'text', VmConfig::get('cartlayout', 'default'));
			echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_CATEGORY_LAYOUT', $this->categoryLayoutList, 'categorylayout', 'size=1', 'value', 'text', VmConfig::get('categorylayout', 'default'));
			echo VmHTML::row('genericlist','COM_VIRTUEMART_CFG_PRODUCTS_SUBLAYOUT', $this->productsFieldList, 'productsublayout', 'size=1', 'value', 'text', VmConfig::get('productsublayout', 0));
			echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_PRODUCT_LAYOUT', $this->productLayoutList, 'productlayout', 'size=1', 'value', 'text', VmConfig::get('productlayout', 'default'));
			?>
        </table>
    </fieldset>

	<fieldset>
		<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_SHOPFRONT_DEPRECATED'); ?></legend>
		<table class="admintable">
			<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_LEGACYLAYOUTS','legacylayouts', VmConfig::get('legacylayouts',1));
			echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_MAIN_LAYOUT',$this->vmLayoutList, 'vmlayout', 'size=1', 'value', 'text', VmConfig::get('vmlayout',0));
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SHOW_CATEGORIES','show_categories', VmConfig::get('show_categories',1));
			echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_CATEGORIES_PER_ROW','homepage_categories_per_row', VmConfig::get('homepage_categories_per_row',3),'','',4);
			echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_PRODUCTS_PER_ROW','homepage_products_per_row', VmConfig::get('homepage_products_per_row',3),'','',4);
			?>
		</table>
	</fieldset>

</td>
<td valign="top">

<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_TITLE'); ?></legend>
	<table class="admintable table-striped">
		<?php

		echo VmHTML::row('checkbox','COM_VIRTUEMART_CFG_ADDITIONAL_IMAGES', 'add_img_main', VmConfig::get('add_img_main'));
		if (function_exists('imagecreatefromjpeg')) {
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING', 'img_resize_enable', VmConfig::get('img_resize_enable', 1));
			echo VmHTML::row('input','COM_VM_CFG_MEDIA_WIDTH', 'img_width_full', VmConfig::get('img_width_full', ''),"","",4);
			echo VmHTML::row('input','COM_VM_CFG_MEDIA_HEIGHT', 'img_height_full', VmConfig::get('img_height_full', ''),"","",4);
			echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_WIDTH', 'img_width', VmConfig::get('img_width', ''),"","",4);
			echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_HEIGHT', 'img_height', VmConfig::get('img_height', 90),"","",4);

		} else { ?>
			<tr>
				<td colspan="2">
					<strong><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_GD_MISSING'); ?></strong>
					<input type="hidden" name="img_resize_enable" value="0"/>
				</td>
			</tr>
		<?php }

		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_NOIMAGEPAGE',$this->noimagelist, 'no_image_set', 'style="min-width:120px"', 'value', 'text', VmConfig::get('no_image_set'));
		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_NOIMAGEFOUND',$this->noimagelist, 'no_image_found', 'style="min-width:120px"', 'value', 'text', VmConfig::get('no_image_found'));

		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH','forSale_path',VmConfig::get('forSale_path',''),'class="inputbox"','',50,260);
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH_THUMB','forSale_path_thumb',VmConfig::get('forSale_path_thumb',''),'class="inputbox"','',50,260);

		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_ASSETS_GENERAL_PATH','assets_general_path',VmConfig::get('assets_general_path',''),'class="inputbox"','',50,260);
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_MEDIA_CATEGORY_PATH','media_category_path',VmConfig::get('media_category_path',''),'class="inputbox"','',50,260);
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_MEDIA_PRODUCT_PATH','media_product_path',VmConfig::get('media_product_path',''),'class="inputbox"','',50,260);
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_MEDIA_MANUFACTURER_PATH','media_manufacturer_path',VmConfig::get('media_manufacturer_path',''),'class="inputbox"','',50,260);
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_MEDIA_VENDOR_PATH','media_vendor_path',VmConfig::get('media_vendor_path',''),'class="inputbox"','',50,260);

		?>
	</table>
</fieldset>

<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PAGINATION_SEQUENCE'); ?></legend>
	<table class="admintable">
		<?php
		echo VmHTML::row('input','COM_VIRTUEMART_LIST_MEDIA','mediaLimit',VmConfig::get('mediaLimit',20));
		echo VmHTML::row('input','COM_VIRTUEMART_LLIMIT_INIT_BE','llimit_init_BE',VmConfig::get('llimit_init_BE',30));
		echo VmHTML::row('input','COM_VIRTUEMART_CFG_PAGSEQ_BE','pagseq',VmConfig::get('pagseq'));
		echo VmHTML::row('input','COM_VIRTUEMART_LLIMIT_INIT_FE','llimit_init_FE',VmConfig::get('llimit_init_FE',24));
		echo VmHTML::row('input','COM_VIRTUEMART_CFG_PAGSEQ_1','pagseq_1',VmConfig::get('pagseq_1'));
		echo VmHTML::row('input','COM_VIRTUEMART_CFG_PAGSEQ_2','pagseq_2',VmConfig::get('pagseq_2'));
		echo VmHTML::row('input','COM_VIRTUEMART_CFG_PAGSEQ_3','pagseq_3',VmConfig::get('pagseq_3'));
		echo VmHTML::row('input','COM_VIRTUEMART_CFG_PAGSEQ_4','pagseq_4',VmConfig::get('pagseq_4'));
		echo VmHTML::row('input','COM_VIRTUEMART_CFG_PAGSEQ_5','pagseq_5',VmConfig::get('pagseq_5'));
		?>
	</table>
</fieldset>

<fieldset>
	<legend class="hasTip" title="<?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_CSS_JS_SETTINGS_TIP'); ?>">
		<?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_CSS_JS_SETTINGS'); ?>
	</legend>
	<table class="admintable">
		<?php
		echo VmHTML::row('checkbox','COM_VIRTUEMART_CFG_FANCY','usefancy', VmConfig::get('usefancy',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FRONT_CSS','css', VmConfig::get('css',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FRONT_JQUERY','jquery', VmConfig::get('jquery',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FRONT_JPRICE','jprice', VmConfig::get('jprice',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FRONT_JSITE','jsite', VmConfig::get('jsite',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FRONT_JCHOSEN','jchosen', VmConfig::get('jchosen',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FRONT_JDYNUPDATE','jdynupdate', VmConfig::get('jdynupdate',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_ENABLE_GOOGLE_JQUERY','google_jquery', VmConfig::get('google_jquery',1));
		//echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_JS_CSS_MINIFIED','minified', VmConfig::get('minified',1));
		?>
	</table>
</fieldset>
</td>
</tr>
</table>

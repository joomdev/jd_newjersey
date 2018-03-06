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

$options = array();
if(vRequest::getCmd('view')!='config'){
	$options[] = JHtml::_('select.option', '',  vmText::_('JGLOBAL_USE_GLOBAL'));
}
$options[] = JHtml::_('select.option', '0',  vmText::_('JNO'));
$options[] = JHtml::_('select.option', '1', vmText::_('JYES'));

VirtuemartViewConfig::$options = $options;
//vmdebug('my options',$options);
//echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_CATEGORY_TEMPLATE',$this->jTemplateList, 'categorytemplate', 'size=1 width=200', 'value', 'name', $this->category->get('categorytemplate', 'default'));
?>

<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_SHOPFRONT_SETTINGS'); ?></legend>
	<table class="admintable">
		<?php
		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_SHOW_STORE_DESC',$options, 'show_store_desc', '', 'value', 'text', $params->get('show_store_desc',1));
		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_SHOW_CATEGORYDESC',$options, 'showcategory_desc', '', 'value', 'text', $params->get('showcategory_desc',1));
		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_SHOW_SEARCH',$options, 'showsearch', '', 'value', 'text', $params->get('showsearch',1));

		echo '</table>';
		echo '<table class="admintable">';
		echo '<tr><th style="min-width:160px;width:70%;"></th>
<th style="min-width:40px;width:8%;text-align: center;"><span class="hasTip" title="'.htmlentities(vmText::_('COM_VM_ADMIN_CFG_SHOW_TIP')).'">'.vmText::_('COM_VM_ADMIN_CFG_SHOW').'</span></th>
<th style="min-width:40px;width:8%;text-align: center;"><span class="hasTip" title="'.htmlentities(vmText::_('COM_VM_ADMIN_CFG_PER_ROW_TIP')).'">'.vmText::_('COM_VM_ADMIN_CFG_PER_ROW').'</span></th>
<th style="min-width:40px;width:8%;text-align: center;"><span class="hasTip" title="'.htmlentities(vmText::_('COM_VM_ADMIN_CFG_OMIT_TIP')).'">'.vmText::_('COM_VM_ADMIN_CFG_OMIT').'</span></th>
</tr>';
		echo VirtuemartViewConfig::rowShopFrontSet($params, 'COM_VIRTUEMART_ADMIN_CFG_SHOW_CATEGORY', 'showcategory', 'categories_per_row', 0, 3);
		echo VirtuemartViewConfig::rowShopFrontSet($params, 'COM_VIRTUEMART_ADMIN_CFG_SHOW_PRODUCTS', 'showproducts', 'products_per_row', 'omitLoaded', 3);
		if(vRequest::getCmd('view')=='config')echo VirtuemartViewConfig::rowShopFrontSet($params, 'COM_VIRTUEMART_ADMIN_CFG_SHOW_MANUFACTURERS', 'show_manufacturers','manufacturer_per_row', 0, 3);
		echo '</table>';

		echo '<table class="admintable">';
		echo '<tr><th style="min-width:160px;width:70%;"></th>
<th style="min-width:40px;width:8%;text-align: center;"><span class="hasTip" title="'.htmlentities(vmText::_('COM_VM_ADMIN_CFG_SHOW_TIP')).'">'.vmText::_('COM_VM_ADMIN_CFG_SHOW').'</span></th>
<th style="min-width:40px;width:8%;text-align: center;"><span class="hasTip" title="'.htmlentities(vmText::_('COM_VM_ADMIN_CFG_ROWS_TIP')).'">'.vmText::_('COM_VM_ADMIN_CFG_ROWS').'</span></th>
<th style="min-width:40px;width:8%;text-align: center;"><span class="hasTip" title="'.htmlentities(vmText::_('COM_VM_ADMIN_CFG_OMIT_TIP')).'">'.vmText::_('COM_VM_ADMIN_CFG_OMIT').'</span></th>
</tr>';
		echo VirtuemartViewConfig::rowShopFrontSet($params, 'COM_VIRTUEMART_ADMIN_CFG_SHOW_FEATURED', 'featured', 'featured_rows', 'omitLoaded_featured');
		echo VirtuemartViewConfig::rowShopFrontSet($params, 'COM_VIRTUEMART_ADMIN_CFG_SHOW_DISCONTINUED', 'discontinued', 'discontinued_rows', 'omitLoaded_discontinued');
		echo VirtuemartViewConfig::rowShopFrontSet($params, 'COM_VIRTUEMART_ADMIN_CFG_SHOW_TOPTEN', 'topten', 'topten_rows', 'omitLoaded_topten');
		echo VirtuemartViewConfig::rowShopFrontSet($params, 'COM_VIRTUEMART_ADMIN_CFG_SHOW_RECENT', 'recent', 'recent_rows', 'omitLoaded_recent');
		echo VirtuemartViewConfig::rowShopFrontSet($params, 'COM_VIRTUEMART_ADMIN_CFG_SHOW_LATEST', 'latest', 'latest_rows', 'omitLoaded_latest');
		?>
	</table>
</fieldset>


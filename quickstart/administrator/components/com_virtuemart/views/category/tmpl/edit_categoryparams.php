<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$params = $this->category;


?>
<table class="adminform" >
        <tr valign="top">
            <td>
				<?php
				$type = 'genericlist';
				require (VMPATH_ADMIN .'/views/config/tmpl/template_params.php');
				?>
            </td>
            <td>
            <fieldset>
                <legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_LAYOUT_SETTINGS'); ?></legend>
                <table class="admintable">
					<?php
					echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_CATEGORY_TEMPLATE',$this->jTemplateList, 'categorytemplate', 'size=1 width=200', 'value', 'name', $this->category->get('categorytemplate', ''));
					echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_CATEGORY_LAYOUT', $this->categoryLayoutList, 'categorylayout', 'size=1', 'value', 'text', $this->category->get('categorylayout', ''));
					echo VmHTML::row('genericlist','COM_VIRTUEMART_CFG_PRODUCTS_SUBLAYOUT', $this->productsFieldList, 'productsublayout', 'size=1', 'value', 'text', $this->category->get('productsublayout', ''));
					echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_PRODUCT_LAYOUT', $this->productLayoutList, 'productlayout', 'size=1', 'value', 'text', $this->category->get('productlayout', ''));
					?>
                </table>
            </fieldset>
            <td>
        </tr>
</table>

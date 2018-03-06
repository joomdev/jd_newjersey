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
 * @version $Id: default_sef.php 7004 2013-06-20 08:34:18Z alatak $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); ?>
<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_SEO_SETTINGS'); ?></legend>
	<table class="admintable">
		<?php
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SEO_DISABLE','seo_disabled', VmConfig::get('seo_disabled', 0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_CFG_SEO_SUFFIX','use_seo_suffix', VmConfig::get('use_seo_suffix', true));
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_SEO_SUFIX','seo_sufix', VmConfig::get('seo_sufix', '-detail'));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SEO_TRANSLATE','seo_translate', VmConfig::get('seo_translate', 1));
		echo VmHTML::row('checkbox','COM_VM_CFG_TRANSLITERATE_SLUGS','transliterateSlugs', VmConfig::get('transliterateSlugs', 0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SEO_USE_ID','seo_use_id', VmConfig::get('seo_use_id',0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_CFG_SEO_FULL','seo_full', VmConfig::get('seo_full',1));
		echo VmHTML::row('checkbox','COM_VM_CFG_SEO_STRICT','router_by_menu', VmConfig::get('router_by_menu',0));
		echo VmHTML::row('checkbox','COM_VM_CFG_SEF_FOR_CART_LINKS','sef_for_cart_links',VmConfig::get('sef_for_cart_links',1));
		?>
	</table>
</fieldset>
<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_feeds.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_HOME_FEED_SETTINGS'); ?></legend>
	<table class="admintable">
		<?php
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_LATEST_ENABLE','feed_latest_published', VmConfig::get('feed_latest_published',0));
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_FEED_LATEST_NB','feed_latest_nb', VmConfig::get('feed_latest_nb',5),'',10,10);
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_TOPTEN_ENABLE','feed_topten_published', VmConfig::get('feed_topten_published',0));
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_FEED_TOPTEN_NB','feed_topten_nb', VmConfig::get('feed_topten_nb',5),'',10,10);

		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_FEATURED_ENABLE','feed_featured_published', VmConfig::get('feed_featured_published',0));
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_FEED_FEATURED_NB','feed_featured_nb', VmConfig::get('feed_featured_nb',5),'',10,10);

		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWIMAGES','feed_home_show_images', VmConfig::get('feed_home_show_images',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWPRICES','feed_home_show_prices', VmConfig::get('feed_home_show_prices',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWDESC','feed_home_show_description', VmConfig::get('feed_home_show_description',0));

		$options = array();
		$options[] = JHtml::_('select.option', 'product_s_desc', vmText::_('COM_VIRTUEMART_PRODUCT_FORM_S_DESC'));
		$options[] = JHtml::_('select.option', 'product_desc', vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DESCRIPTION'));
		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_FEED_DESCRIPTION_TYPE', $options, 'feed_home_description_type', 'size=1', 'value', 'text', VmConfig::get('feed_home_description_type'));

		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_FEED_MAX_TEXT_LENGTH','feed_home_max_text_length', VmConfig::get('feed_home_max_text_length',500),'',10,10);
		?>
	</table>
</fieldset>

<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_CAT_FEED_SETTINGS'); ?></legend>
	<table class="admintable">
		<?php
		//echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_FEED_TITLE_CATEGORIES', 'feed_title_categories', VmConfig::get('feed_title_categories', 0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_ENABLE', 'feed_cat_published', VmConfig::get('feed_cat_published', 0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWIMAGES', 'feed_cat_show_images', VmConfig::get('feed_cat_show_images', 0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWPRICES', 'feed_cat_show_prices', VmConfig::get('feed_cat_show_prices', 0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWDESC', 'feed_cat_show_description', VmConfig::get('feed_cat_show_description', 0));

		$options = array();
		$options[] = JHtml::_('select.option', 'product_s_desc', vmText::_('COM_VIRTUEMART_PRODUCT_FORM_S_DESC'));
		$options[] = JHtml::_('select.option', 'product_desc', vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DESCRIPTION'));
		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_FEED_DESCRIPTION_TYPE', $options, 'feed_cat_description_type', 'size=1', 'value', 'text', VmConfig::get('feed_cat_description_type',0));
		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_FEED_MAX_TEXT_LENGTH','feed_cat_max_text_length',VmConfig::get('feed_cat_max_text_length','500'),"","",4);
		?>
	</table>
</fieldset>


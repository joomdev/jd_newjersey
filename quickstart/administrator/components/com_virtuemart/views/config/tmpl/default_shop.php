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
 * @version $Id: default_shop.php 9561 2017-05-30 17:47:16Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');?>
<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_SETTINGS'); ?></legend>
	<table class="admintable">
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SHOP_OFFLINE','shop_is_offline',VmConfig::get('shop_is_offline',0));
		?>
		<tr>
			<td class="key">
				<?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_OFFLINE_MSG'); ?>
			</td>
			<td colspan="2">
				<textarea rows="6" cols="50" name="offline_message"
				          style="text-align: left;"><?php echo VmConfig::get('offline_message', 'Our Shop is currently down for maintenance. Please check back again soon.'); ?></textarea>
			</td>
		</tr>
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_USE_ONLY_AS_CATALOGUE','use_as_catalog',VmConfig::get('use_as_catalog',0));
			echo VmHTML::row('genericlist','COM_VIRTUEMART_CFG_CURRENCY_MODULE',$this->currConverterList, 'currency_converter_module', 'size=1', 'value', 'text', VmConfig::get('currency_converter_module', 'convertECB.php'));
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_ENABLE_CONTENT_PLUGIN','enable_content_plugin',VmConfig::get('enable_content_plugin',0));

			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SSL','useSSL',VmConfig::get('useSSL',0));
			echo VmHTML::row('checkbox','COM_VIRTUEMART_REGISTRATION_CAPTCHA','reg_captcha',VmConfig::get('reg_captcha',0));
			echo VmHTML::row('checkbox','COM_VIRTUEMART_VM_ERROR_HANDLING_ENABLE','handle_404',VmConfig::get('handle_404',1));
		$host = JUri::getInstance()->getHost(); ?>
		<tr>
			<td class="key">
				<?php echo vmText::_('COM_VM_EXTSUBSCR_HOST'); ?>
        </td>
        <td>
				<?php echo $host ?>
        </td>
        </tr>
        <tr>
            <td class="key">
                <span class="hasTip" title="<?php echo htmlentities(vmText::_('COM_VM_MEMBER_ACCESSNBR_TIP'))?>'"><?php echo vmText::_('COM_VM_MEMBER_ACCESSNBR')?></span>
            </td>
            <td>
                <?php echo VmHTML::input('member_access_number',VmConfig::get('member_access_number','')); ?>
            </td>
            <td>
                <span class="hasTip" title="<?php echo htmlentities(vmText::sprintf($host,'COM_VM_MEMBER_AGREEMENT_TIP',VmConfig::$vmlangTag,vmVersion::$RELEASE))?>'"><?php echo vmText::_('COM_VM_MEMBER_AGREEMENT')?></span>
            </td>
        </tr>
         <?php
		//echo VmHTML::row('input','COM_VM_MEMBER_ACCESSNBR','member_access_number',VmConfig::get('member_access_number',''));
		?>
	</table>
</fieldset>

<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_LANGUAGES'); ?></legend>
	<table class="admintable">
	<?php echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_ENABLE_ENGLISH','enableEnglish',VmConfig::get('enableEnglish',1)); ?>
        <tr>
            <td class="key">
					<span class="hasTip" title="<?php echo vmText::_('COM_VM_CFG_SHOPLANG_TIP'); ?>">
						<?php echo vmText::sprintf('COM_VM_CFG_SHOPLANG',VmConfig::$jDefLang); ?>
					</span>
            </td>

            <td>
				<?php echo $this->activeShopLanguage; ?>
            </td>
        </tr>
	    <tr>
			<td class="key">
					<span class="hasTip" title="<?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_MULTILANGUE_TIP'); ?>">
						<?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_MULTILANGUE'); ?>
					</span>
			</td>

			<td>
				<?php echo $this->activeLanguages; ?>
			 <span>
				<?php echo vmText::sprintf('COM_VIRTUEMART_MORE_LANGUAGES','<a href="https://virtuemart.net/community/translations" target="_blank" >Translations</a>'); ?>
				</span></td>
		</tr>
		<?php
		echo VmHTML::row('checkbox','COM_VM_CFG_NO_FALLBACK','prodOnlyWLang',VmConfig::get('prodOnlyWLang',0));
		//echo VmHTML::row('checkbox','COM_VM_CFG_DUAL_FALLBACK','dualFallback',VmConfig::get('dualFallback',1));
		echo VmHTML::row('input','COM_VM_CFG_CUSTOM_FALLBACK','vm_lfbs',VmConfig::get('vm_lfbs',''));

		?>

	</table>
</fieldset>



<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_ADVANCED'); ?></legend>
	<table class="admintable">
		<?php
			$optDebug = array(
				'none' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_NONE'),
				'admin' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ADMIN'),
				'all' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ALL')
			);
			echo VmHTML::row('radiolist','COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG','debug_enable',VmConfig::get('debug_enable','none'), $optDebug);
		    echo VmHTML::row('checkbox','COM_VM_CFG_ENABLE_DEBUG_METHODS','debug_enable_methods',VmConfig::get('debug_enable_methods',0));
			echo VmHTML::row('radiolist','COM_VIRTUEMART_CFG_DEV','vmdev',VmConfig::get('vmdev',0), $optDebug);
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS','dangeroustools',VmConfig::get('dangeroustools',0));
			echo VmHTML::row('input','COM_VIRTUEMART_REV_PROXY_VAR','revproxvar',VmConfig::get('revproxvar',''));
			$optMultiX = array(
				'none' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX_NONE'),
				'admin' => vmText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX_ADMIN')

				// 				'all'	=> vmText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ALL')
			);
			echo VmHTML::row('radiolist','COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX','multix',VmConfig::get('multix','none'), $optMultiX);
		$optMultiX = array(
			'0' => vmText::_('COM_VIRTUEMART_CFG_MULTIX_CART_NONE'),
			'byproduct' => vmText::_('COM_VIRTUEMART_CFG_MULTIX_CART_BYPRODUCT'),
			'byvendor' => vmText::_('COM_VIRTUEMART_CFG_MULTIX_CART_BYVENDOR'),
			'byselection' => vmText::_('COM_VIRTUEMART_CFG_MULTIX_CART_BYSELECTION')
			// 				'all'	=> vmText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ALL')
		);
		echo VmHTML::row('radiolist','COM_VIRTUEMART_CFG_MULTIX_CART','multixcart',VmConfig::get('multixcart',0), $optMultiX);

		?>

	</table>
</fieldset>

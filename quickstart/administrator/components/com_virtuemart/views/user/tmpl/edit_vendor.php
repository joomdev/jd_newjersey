<?php
/**
*
* Modify user form view, User info
*
* @package	VirtueMart
* @subpackage User
* @author Oscar van Eijk
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit_vendor.php 9590 2017-06-27 12:46:05Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
if(!vmAccess::manager('user.editshop')){
    ?><div><?php echo vmText::_('COM_VM_PERM_MISSING_VENDOR');?></div> <?php
}
?>

<div class="col50">

				<fieldset>
					<legend>
						<?php echo vmText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL') ?>
					</legend>
					<table class="admintable">
						<?php
						echo VmHTML::row('input','COM_VIRTUEMART_STORE_FORM_COMPANY_NAME','vendor_name',$this->vendor->vendor_name);
						echo VmHTML::row('input','COM_VIRTUEMART_STORE_FORM_STORE_NAME','vendor_store_name',$this->vendor->vendor_store_name);
						echo VmHTML::row('input','COM_VIRTUEMART_PRODUCT_FORM_URL','vendor_url',$this->vendor->vendor_url);
						echo VmHTML::row('input','COM_VIRTUEMART_STORE_FORM_MPOV','vendor_min_pov',$this->vendor->vendor_min_pov);
						if(VmConfig::get('multix','none')!='none' and vmAccess::manager('managevendors')){
							echo VmHTML::row('input','COM_VIRTUEMART_MAX_CATS_PER_PRODUCT','max_cats_per_product',$this->vendor->max_cats_per_product);
						}
						?>
					</table>
				</fieldset>

				<fieldset>
					<legend>
						<?php echo vmText::_('COM_VIRTUEMART_STORE_CURRENCY_DISPLAY') ?>
					</legend>
					<table class="admintable">
						<?php
						echo VmHTML::row('genericlist','COM_VIRTUEMART_CURRENCY',$this->currencies,'vendor_currency','', 'virtuemart_currency_id', 'currency_name', $this->vendor->vendor_currency,'vendor_currency',true);
						echo VmHTML::row('genericlist','COM_VIRTUEMART_STORE_FORM_ACCEPTED_CURRENCIES',$this->currencies,'vendor_accepted_currencies[]','size=10 multiple="multiple" data-placeholder="'.vmText::_('COM_VIRTUEMART_DRDOWN_SELECT_SOME_OPTIONS').'"', 'virtuemart_currency_id', 'currency_name', $this->vendor->vendor_accepted_currencies,'vendor_accepted_currencies',true);
						?>
					</table>
				</fieldset>

		<fieldset>
			<legend>
				<?php echo vmText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL') ?>
			</legend>
			<?php
				echo $this->vendor->images[0]->displayFilesHandler($this->vendor->virtuemart_media_id,'vendor',$this->vendor->virtuemart_vendor_id);
			?>


		</fieldset>

				<fieldset>
					<legend>
						<?php echo vmText::_('COM_VIRTUEMART_STORE_FORM_DESCRIPTION');?>
					</legend>
					<?php echo $this->editor->display('vendor_store_desc', $this->vendor->vendor_store_desc, '100%', 350, 70, 15)?>
				</fieldset>

				<fieldset>
					<legend>
						<?php echo vmText::_('COM_VIRTUEMART_STORE_FORM_TOS');?>
					</legend>
					<?php echo $this->editor->display('vendor_terms_of_service', $this->vendor->vendor_terms_of_service, '100%', 350, 70, 15)?>
				</fieldset>

				<fieldset>
					<legend>
						<?php echo vmText::_('COM_VIRTUEMART_STORE_FORM_LEGAL');?>
					</legend>
					<?php echo $this->editor->display('vendor_legal_info', $this->vendor->vendor_legal_info, '100%', 100, 70, 15)?>
				</fieldset>

			<fieldset>
				<legend><?php echo vmText::_('COM_VIRTUEMART_METAINFO'); ?></legend>
				<?php echo shopFunctions::renderMetaEdit($this->vendor); ?>
			</fieldset>

</div>
<input type="hidden" name="user_is_vendor" value="1" />
<input type="hidden" name="virtuemart_vendor_id" value="<?php echo $this->vendor->virtuemart_vendor_id; ?>" />
<input type="hidden" name="last_task" value="<?php echo vRequest::getCmd('task'); ?>" />

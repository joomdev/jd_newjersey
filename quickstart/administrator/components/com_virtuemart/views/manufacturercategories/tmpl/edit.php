<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Manufacturer Category
* @author Patrick Kohl
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea($this);
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_MANUFACTURER_CATEGORY_DETAILS');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<div class="col50">
	<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_DETAILS'); ?></legend>
	<table class="admintable">
		<?php echo VmHTML::row('input','COM_VIRTUEMART_MANUFACTURER_CATEGORY_NAME','mf_category_name',$this->manufacturerCategory->mf_category_name); ?>
		<?php echo VmHTML::row('booleanlist','COM_VIRTUEMART_PUBLISHED','published',$this->manufacturerCategory->published); ?>
		<?php echo VmHTML::row('textarea','COM_VIRTUEMART_MANUFACTURER_CATEGORY_DESCRIPTION','mf_category_desc',$this->manufacturerCategory->mf_category_desc); ?>

	</table>
	</fieldset>
</div>


	<input type="hidden" name="virtuemart_manufacturercategories_id" value="<?php echo $this->manufacturerCategory->virtuemart_manufacturercategories_id; ?>" />
	<?php echo $this->addStandardHiddenToForm(); ?>
</form>

<?php
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>
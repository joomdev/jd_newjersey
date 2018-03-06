<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage State
* @author RickG, Max Milbers
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
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<div class="col50">
	<fieldset>
<?php /*	<legend><?php echo vmText::_('COM_VIRTUEMART_STATE_DETAILS'); ?></legend> */?>
	<legend><?php echo JHtml::_('link','index.php?option=com_virtuemart&view=state&virtuemart_country_id='.$this->virtuemart_country_id,vmText::sprintf('COM_VIRTUEMART_STATE_COUNTRY',$this->country_name).' '. vmText::_('COM_VIRTUEMART_DETAILS') ); ?></legend>
	<table class="admintable">
		<?php
		echo VmHTML::row('input', 'COM_VIRTUEMART_STATE_NAME', 'state_name', $this->state->state_name,'class="required" size="50"');
		echo VmHTML::row('booleanlist', 'COM_VIRTUEMART_PUBLISHED', 'published', $this->state->published);
		echo VmHTML::row('booleanlist', 'COM_VIRTUEMART_PUBLISHED', 'published', $this->state->published);
		echo VmHTML::row('genericlist', 'COM_VIRTUEMART_WORLDZONE', $this->worldZones, 'virtuemart_worldzone_id', '', 'virtuemart_worldzone_id', 'zone_name', $this->state->virtuemart_worldzone_id);
		echo VmHTML::row('input', 'COM_VIRTUEMART_STATE_3_CODE', 'state_3_code', $this->state->state_3_code,'size="10"');
		echo VmHTML::row('input', 'COM_VIRTUEMART_STATE_2_CODE', 'state_2_code', $this->state->state_2_code,'size="10"');
		?>
	</table>
	</fieldset>
</div>

	<input type="hidden" name="virtuemart_country_id" value="<?php echo $this->virtuemart_country_id; ?>" />
	<input type="hidden" name="virtuemart_state_id" value="<?php echo $this->state->virtuemart_state_id; ?>" />

	<?php echo $this->addStandardHiddenToForm(); ?>
</form>


<?php AdminUIHelper::endAdminArea(); ?>
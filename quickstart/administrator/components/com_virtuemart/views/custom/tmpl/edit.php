<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: media_edit.php 3049 2011-04-17 07:01:44Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
vmJsApi::JvalideForm();
AdminUIHelper::startAdminArea($this);
?>
<form name="adminForm" id="adminForm" method="post" action="">
    <fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_CUSTOM_FIELD'); ?></legend>
	<?php
	$this->addHidden('view', 'custom');
	$this->addHidden('task', '');
	$this->addHidden(JSession::getFormToken(), 1);
	$this->addHidden('custom_jplugin_id', $this->custom->custom_jplugin_id);
	$this->addHidden('custom_element', $this->custom->custom_element);
//if ($this->custom->custom_parent_id) $this->customfields->addHidden('custom_parent_id',$this->custom->custom_parent_id);
	$attribute_id = vRequest::getVar('attribute_id', '');
	if (!empty($attribute_id))
	    $this->customfields->addHidden('attribute_id', $attribute_id);
	?>
	<table class="admintable">
	    <?php echo $this->displayCustomFields($this->custom); ?>

	    <tr id="custom_plg">
		<td valign="top"><?php echo vmText::_('COM_VIRTUEMART_SELECT_CUSTOM_PLUGIN') ?></td>
		<td>
		    <fieldset>
			<?php if (!$this->custom->form) {
				echo $this->pluginList;
			} ?>
			<div class="clear"></div>
    			<div id="plugin-Container">
				<?php
				defined('_JEXEC') or die('Restricted access');

				if ($this->custom->form) {

					?>
					<h2 style="text-align: center;"><?php echo vmText::_($this->custom->custom_title) ?></h2>
					<div style="text-align: center;"><?php echo VmText::_('COM_VIRTUEMART_CUSTOM_CLASS_NAME').": ".$this->custom->custom_element ?></div>
					<?php
					if ($this->custom->form) {
						$form = $this->custom->form;
						include(VMPATH_ADMIN.DS.'fields'.DS.'formrenderer.php');
					}
				} else {
					echo vmText::_('COM_VIRTUEMART_SELECT_CUSTOM_PLUGIN');
				}
				?>

    			</div>
    		    </fieldset>
    		</td>
    	    </tr>
	    <?php //} ?>
	</table>
    </fieldset>
</form>
<?php


$js = "function submitbutton(pressbutton) {
	if (pressbutton=='cancel'){
        submitform(pressbutton);
        return true;
    }
	if (jQuery('#adminForm').validationEngine('validate')== true){
        submitform(pressbutton);
        return true;
    }
	else return false ;
}
jQuery(function($) {";

if (!$this->custom->form) {
	$js .= "$('#custom_plg').hide();";
}
$js .= '$(\'#field_type\').change(function () {
	var $selected = $(this).val();
	if ($selected == "E" ) $(\'#custom_plg\').show();
	else { $(\'#custom_plg\').hide();
	    $(\'#custom_jplugin_id option:eq(0)\').attr("selected", "selected");
	    $(\'#custom_jplugin_id\').change();
	}

    });
    $(\'#custom_jplugin_id\').change(function () {
	var $id = $(this).val();
	$(\'#plugin-Container\').load( \'index.php?option=com_virtuemart&view=custom&task=viewJson&format=json&custom_jplugin_id=\'+$id , function() {
	$(this).find("[title]").vm2admin(\'tips\',tip_image) });

    });
}); ';

vmJsApi::addJScript('showPlugin',$js);
AdminUIHelper::endAdminArea(); ?>
<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_update.php 3274 2011-05-17 20:43:48Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea($this);

vmInfo('COM_VM_INSTALLATION_INFO');
if(!VmConfig::get('dangeroustools', false)){
	$uri = JFactory::getURI();
	$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
	?>

	<div class="vmquote" style="text-align:left;margin-left:20px;">
		<span style="font-weight:bold;color:green;"> <?php echo vmText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_ENABLED_JS',vmText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS'),$link) ?></span>
	</div>

<?php
}

$link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installComplete&'.JSession::getFormToken().'=1&token='.JSession::getFormToken().'&install=1' ); ?>

<div id="cpanel">

    <div style="text-align: left;padding: 20px;width: 30%;float: left;"><?php echo vmText::_('COM_VM_INSTALLATION_EXPLAIN')?></div>


<div class="icon"><a onclick="javascript:confirmation('<?php echo $link; ?>');">
		<span class="vmicon48"></span>
		<br /><?php echo vmText::_('COM_VM_INSTALLATION_FRESH'); ?>

	</a></div>

<?php	$link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installCompleteSamples&'.JSession::getFormToken().'=1&token='.JSession::getFormToken().'&install=1'); ?>
	<div class="icon"><a onclick="javascript:confirmation('<?php echo $link; ?>');">
			<span class="vmicon48"></span>
			<br /><?php echo vmText::_('COM_VM_INSTALLATION_FRESH_AND_SAMPLE'); ?>

		</a></div>

<?php
AdminUIHelper::endAdminArea();
?>
<script type="text/javascript">
<!--
function confirmation(destnUrl) {
		window.location = destnUrl;
}
//-->
</script>
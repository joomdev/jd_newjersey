<?php
/**
 *
 * @version $Id$
 * @package VirtueMart
 * @author Max Milbers
 * @subpackage All In One
 * @copyright Copyright (C) 2014 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

defined('_JEXEC') or die();

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists( 'VmConfig' )) {
	$path = JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php';
	if(file_exists($path)){
		require($path);
		VmConfig::loadConfig();
	} else {
		$app = JFactory::getApplication();
		$app->enqueueMessage('VirtueMart Core is not installed, please install VirtueMart again, or uninstall the AIO component by the joomla extension manager');
		return false;
	}
}
if(!class_exists('vmText')) require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmtext.php');

$task = vRequest::getCmd('task');
if($task=='updateDatabase'){
	vRequest::vmCheckToken('Invalid Token, in ' . $task);
	$app = JFactory::getApplication();


		if(!class_exists('com_virtuemart_allinoneInstallerScript')) require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart_allinone'.DS.'script.vmallinone.php');
		$updater = new com_virtuemart_allinoneInstallerScript();
		$updater->vmInstall();
		$app->redirect('index.php?option=com_virtuemart_allinone', 'Database updated');

}

?>
<script type="text/javascript">
<!--
function confirmation(message, destnUrl) {
	var answer = confirm(message);
	if (answer) {
		window.location = destnUrl;
	}
}
//-->
</script>
<?php	JToolBarHelper::title('VirtueMart AIO'  );

	$db = JFactory::getDBO ();
	$q = 'SELECT `name`, `element`, `folder` ,`enabled`  FROM `#__extensions` WHERE  folder in ("vmpayment", "vmshipment", "vmcustom", "vmuserfield", "vmcalculation") ORDER BY folder ';
	$db->setQuery ($q);
	$plugins = $db->loadObjectList();
?>
	<table>
		<tr>
			<td align="center" colspan="4">
				<?php
				VmConfig::loadConfig();
				vmLanguage::loadJLang('com_virtuemart');

				?>
				<?php $link = JROUTE::_('index.php?option=com_virtuemart_allinone&task=updateDatabase&' . JSession::getFormToken() . '=1'); ?>
				<button
					onclick="javascript:confirmation('<?php echo addslashes(vmText::_('COM_VIRTUEMART_UPDATE_VMPLUGINTABLES')); ?>', '<?php echo $link; ?>');">

					<?php echo vmText::_('COM_VIRTUEMART_UPDATE_VMPLUGINTABLES'); ?>
				</button>
			</td>
		</tr>
		<?php if ($plugins) { ?>
			<tr>
				<th colspan="4"><?php echo JText::_('COM_VIRTUEMART_INSTALLED_PLUGINS'); ?></th>
			</tr>
			<?php
			foreach ($plugins as $plugin) {
				?>
				<tr>
					<td><?php echo $plugin->folder ?></td>
					<td><?php echo $plugin->name ?></td>
					<td><?php echo $plugin->element ?></td>
					<td><?php echo $plugin->enabled ? JText::_('COM_VIRTUEMART_PUBLISHED') : Jtext::_('COM_VIRTUEMART_UNPUBLISHED') ?></td>
				</tr>
			<?php
			}
			?>
		<?php } ?>
	</table>

<?php



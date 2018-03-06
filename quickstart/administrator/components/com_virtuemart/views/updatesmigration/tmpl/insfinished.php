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

if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
VmConfig::loadConfig();

if(!class_exists('vmLanguage')) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/vmlanguage.php');
vmLanguage::loadJLang('com_virtuemart.sys');
vmLanguage::loadJLang('com_virtuemart');

$update = vRequest::getInt('update',0);
$option = vRequest::getString('option');

if($option=='com_virtuemart'){

	if (!class_exists('AdminUIHelper')) require(VMPATH_ADMIN.DS.'helpers'.DS.'adminui.php');
	if (!class_exists('JToolBarHelper')) require(JPATH_ADMINISTRATOR.DS.'includes'.DS.'toolbar.php');
	if (!class_exists ('VirtuemartViewUpdatesMigration'))
		require(VMPATH_ADMIN . DS . 'views' . DS . 'updatesmigration' .DS. 'view.html.php');

	$view = new VirtuemartViewUpdatesMigration();
	AdminUIHelper::startAdminArea($view);
}
?>

	<table
		width="50%"
		border="0">
		<tr>
			<td colspan="2"
				valign="top"><a
					href="https://virtuemart.net"
					target="_blank"> <img
						border="0"
						align="left" style="margin-right: 20px"
						src="components/com_virtuemart/assets/images/vm_menulogo.png"
						alt="Cart" /> </a>
				<h2 style="overflow: hidden;">
					<?php echo vmText::_('COM_VIRTUEMART_INSTALLATION_WELCOME') ?>
				</h2>
			</td>
		</tr>
		<tr>
			<td>
				<strong>
					<?php
					if($update){
						echo  vmText::_('COM_VIRTUEMART_UPGRADE_SUCCESSFUL');
					} else {
						echo vmText::_('COM_VIRTUEMART_INSTALLATION_SUCCESSFUL');
					}
					?>
				</strong>

			</td>
			<td>
				<?php echo vmText::sprintf('COM_VM_INSTALLATION_SOURCE',htmlspecialchars(VMPATH_ROOT)); ?>
            </td>
		</tr>
		<?php  if (vRequest::getCmd('view','')=='install') {
			if (JVM_VERSION < 3) {
			$tag="strong";$style='style="color: #C00"';
			} else {
				$tag="span";
				$style = 'class="label label-warning"';
			} ?>
			<tr>
				<td>
					<<?php echo $tag.' '.$style ?>>
						<?php
						if ($update) {
							echo vmText::_('COM_VIRTUEMART_UPDATE_AIO');
						} else {
							echo vmText::_('COM_VIRTUEMART_INSTALL_AIO');
						}
						?>
					</<?php echo $tag ?>>
					<?php echo vmText::_('COM_VIRTUEMART_INSTALL_AIO_TIP'); ?>
				</td>
			</tr>
		<?php
		}
		$class="";
		if (vRequest::getCmd('view','')=='install') {
			if (JVM_VERSION < 3) {
				$class = "button";
			} else {
				$class = "btn";
			}
		}
		?>
		<tr>
			<td><span class="<?php echo $class ?>">
				<?php echo vmText::sprintf('COM_VIRTUEMART_MORE_LANGUAGES','https://virtuemart.net/community/translations'); ?>
				</span>
			</td>
		</tr>
		<tr>
			<td><span class="<?php echo $class ?>">
				<a href="https://docs.virtuemart.net"><?php echo vmText::_('COM_VIRTUEMART_DOCUMENTATION'); ?></a>
				</span>
			</td>
		</tr>
		<tr>
			<td><span class="<?php echo $class ?>">
				<a href="https://extensions.virtuemart.net"><?php echo  vmText::_('COM_VIRTUEMART_EXTENSIONS_MORE'); ?></a>
				</span>
			</td>
		</tr>
	</table>

<?php
if($option=='com_virtuemart'){
	AdminUIHelper::endAdminArea();
}

?>

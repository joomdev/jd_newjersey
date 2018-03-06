<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_tools.php 9633 2017-09-07 08:16:00Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!VmConfig::get('dangeroustools', false)){
	$uri = JFactory::getURI();
	$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
	?>

	<div class="vmquote" style="text-align:left;margin-left:20px;">
	<span style="font-weight:bold;color:green;"> <?php echo vmText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_ENABLED_JS',vmText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS'),$link) ?></span>
	</div>

	<?php
}

?>

<table  >
    <tr>


	<td align="left" colspan="2" >
             <h3> <?php echo vmText::_('COM_VIRTUEMART_TOOLS_SYNC_MEDIA_FILES'); ?> </h3>
	</td>


    </tr>
    <tr>
<?php /*	<td align="center">
		<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installSampleData&'.JSession::getFormToken().'=1'); ?>
	    <div class="icon"><a onclick="javascript:confirmation('<?php echo vmText::_('COM_VIRTUEMART_UPDATE_INSTALLSAMPLE_CONFIRM'); ?>', '<?php echo $link; ?>');">
		<span class="vmicon48 vm_install_48"></span>
	    <br /><?php echo vmText::_('COM_VIRTUEMART_SAMPLE_DATA'); ?>
		</a></div>
	</td>
	<td align="center">
	    <a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=userSync&'.JSession::getFormToken().'=1'); ?>">
		<span class="vmicon48 vm_shoppers_48"></span>
	    </a>
	    <br /><?php echo vmText::_('COM_VIRTUEMART_SYNC_JOOMLA_USERS'); ?>
		</a></div>
	</td>*/ ?>

 	<td align="center" width="25%">
 	    <?php echo $this->renderTaskButton('portMedia','COM_VIRTUEMART_TOOLS_SYNC_MEDIA_FILES'); ?>
	</td>

    <td align="left" >
		<?php echo vmText::sprintf('COM_VIRTUEMART_TOOLS_SYNC_MEDIAS_EXPLAIN',VmConfig::get('media_product_path') ,VmConfig::get('media_category_path') , VmConfig::get('media_manufacturer_path')); ?>
    </td>

    </tr>
  <tr>
	  <td align="center" width="25%">
            <?php echo $this->renderTaskButton('resetThumbs','COM_VIRTUEMART_TOOLS_RESTHUMB'); ?>
	  </td>

	  <td align="left" >

		  <?php echo vmText::_('COM_VIRTUEMART_TOOLS_RESTHUMB_TIP'); ?>
	  </td>
    </tr>
    <tr>
        <td align="center">
			<?php echo $this->renderTaskButton('updateDatabase','COM_VIRTUEMART_UPDATEDATABASE'); ?>
        </td>
        <td align="center">
			<?php echo $this->renderTaskButton('optimizeDatabase','COM_VIRTUEMART_OPTIMIZE_DATABASE'); ?>
        </td>
    </tr>
    <tr>

    </tr>
</table>

    <form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" >
        <input type="hidden" name="task" value="setStoreOwner" />

        <table>
            <tr>
                <td>
					<?php echo vmText::_('COM_VIRTUEMART_MIGRATION_STOREOWNERID'); ?>
                </td>
                <td>
                    <input class="inputbox" type="text" name="storeOwnerId" size="15" value="" />
                </td>
                <td>
                    <button class="default" type="submit" ><?php echo vmText::_('COM_VIRTUEMART_SETSTOREOWNER'); ?></button>
                </td>
            </tr>
        </table>

        <!-- Hidden Fields -->
        <input type="hidden" name="option" value="com_virtuemart" />
        <input type="hidden" name="view" value="updatesmigration" />
		<?php echo JHtml::_( 'form.token' ); ?>
    </form>

<table>
    <tr><td align="left" colspan="4"><?php echo vmText::_('COM_VIRTUEMART_UPDATE_MIGRATION_TOOLS_WARNING'); ?></td></tr>
<tr>
    <td align="center">
		<?php echo $this->renderTaskButton('refreshCompleteInstall','COM_VIRTUEMART_DELETES_ALL_VM_TABLES_AND_FRESH'); ?>
	</td>
	<td align="center">
		<?php echo $this->renderTaskButton('refreshCompleteInstallAndSample','COM_VIRTUEMART_DELETES_ALL_VM_TABLES_AND_SAMPLE'); ?>
	</td>


	<td align="center">

	</td>
    </tr>
    <tr>
		<td align="center">
			<?php echo $this->renderTaskButton('restoreSystemDefaults','COM_VIRTUEMART_UPDATE_RESTOREDEFAULTS'); ?>
		</td>
		<td align="center">
			<?php echo $this->renderTaskButton('deleteVmData','COM_VIRTUEMART_UPDATE_REMOVEDATA'); ?>
		</td>
		<td align="center">
			<?php echo $this->renderTaskButton('deleteVmTables','COM_VIRTUEMART_UPDATE_REMOVETABLES'); ?>
		</td>
    </tr>

		<td align="center">
			<?php echo $this->renderTaskButton('updateDatabaseJoomla','Update Joomla Database for pros, use only if you know what you do'); ?>
		</td>
	</tr>
</table>



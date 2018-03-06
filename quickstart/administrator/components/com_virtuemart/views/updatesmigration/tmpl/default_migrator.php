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
* @version $Id: default_tools.php 4007 2011-08-31 07:31:35Z alatak $
*/

$session = JFactory::getSession();

?>

<table>
    <tr>
        <td align="center">
			<?php
			echo $this->renderTaskButton('deleteInheritedCustoms','COM_VIRTUEMART_UPDATE_DELETE_INHERITEDC');
			/*$link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=deleteInheritedCustoms&'.JSession::getFormToken().'=1' ); ?>
            <div class="icon"><a onclick="javascript:confirmation('<?php echo addslashes( vmText::_('COM_VIRTUEMART_UPDATE_DELETE_INHERITEDC') ); ?>', '<?php echo $link; ?>');">
                    <span class="vmicon48"></span>
                    <br />
					<?php echo vmText::_('COM_VIRTUEMART_UPDATE_DELETE_INHERITEDC'); ?>
                </a></div>*/ ?>
        </td>
        <td align="center">
			<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=fixCustomsParams&'.JSession::getFormToken().'=1' ); ?>
            <div class="icon"><a onclick="javascript:confirmation('<?php echo addslashes( vmText::_('COM_VIRTUEMART_UPDATE_OLD_CUSTOMFORMAT') ); ?>', '<?php echo $link; ?>');">
                    <span class="vmicon48"></span>
                    <br />
					<?php echo vmText::_('COM_VIRTUEMART_UPDATE_OLD_CUSTOMFORMAT'); ?>
                </a></div>
        </td>
    </tr>
</table>

<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" >
<input type="hidden" name="task" value="" />

<table width="900px">

<tr>
	<td align="left" colspan="2" >
		<h3> <?php echo vmText::_('COM_VIRTUEMART_UPDATE_MIGRATION_TITLE'); ?> </h3>
	</td>
</tr>

<tr>
	<?php if (!class_exists('ShopFunctions')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');

	$max_execution_time = ini_get('max_execution_time');
	?>
	<td align="left" colspan="1" >max_execution_time</td>
    <td><?php echo $max_execution_time ?></td>
</tr>
<tr>
    <?php
	@ini_set( 'max_execution_time', (int)$max_execution_time+1 );
	$new_max_execution_time = ini_get('max_execution_time');
	if($max_execution_time===$new_max_execution_time){
		echo '<td colspan="2">Server settings do not allow changes of your max_execution_time in the php.ini file, you may get problems migrating a big shop</td>';
	} else {
		echo '<td>'.vmText::_('COM_VIRTUEMART_UPDATE_MIGRATION_CHANGE_MAX_EXECUTION_TIME').'</td><td><input class="inputbox" type="text" name="max_execution_time" size="15" value="'.$max_execution_time.'" />'.'</td>';
	}
	@ini_set( 'max_execution_time', $max_execution_time );
    ?>
</tr>
<tr>
    <?php
	$memory_limit = VmConfig::getMemoryLimit();
    ?>
    <td align="left" colspan="1" >
        memory_limit</td>
    <td><?php echo $memory_limit.' MB' ?></td>
</tr>
<tr>
    <td><?php echo vmText::_('COM_VIRTUEMART_UPDATE_MIGRATION_CHANGE_MEMORY_LIMIT').'</td><td><input class="inputbox" type="text" name="memory_limit" size="15" value="'.$memory_limit.'" />' ?></td>

</tr>

<tr>
    <td align="center">
		<button class="default" type="submit" ><?php echo vmText::_('COM_VIRTUEMART_MIGRATE'); ?></button>
    </td>
</tr>

<tr>
	<td>
		<?php echo vmText::_('COM_VIRTUEMART_UPDATE_MIGRATION_STRING'); ?>
	</td>
	<td>
   <?php
		$options = array(
			'migrateGeneralFromVmOne'	=>	vmText::_('COM_VIRTUEMART_UPDATE_GENERAL'),
			'migrateUsersFromVmOne'	=>	vmText::_('COM_VIRTUEMART_UPDATE_USERS'),
			'migrateProductsFromVmOne'	=> vmText::_('COM_VIRTUEMART_UPDATE_PRODUCTS'),
			'migrateOrdersFromVmOne'	=> vmText::_('COM_VIRTUEMART_UPDATE_ORDERS'),
			'migrateAllInOne'	=> vmText::_('COM_VIRTUEMART_UPDATE_ALL'),
			'portVmAttributes'	=> vmText::_('COM_VIRTUEMART_UPDATE_ATTR').'<br />'.vmText::_('COM_VIRTUEMART_UPDATE_ATTR_2'),
			'portVmRelatedProducts'	=> vmText::_('COM_VIRTUEMART_UPDATE_REL'),
		//	'setStoreOwner'	=> vmText::_('COM_VIRTUEMART_SETSTOREOWNER')
		);
		echo VmHTML::radioList('task', $session->get('migration_task', 'migrateAllInOne', 'vm'), $options);
	?>
	</td>
</tr>

<?php

echo VmHTML::row('checkbox','COM_VIRTUEMART_MIGRATION_REWRITE_ORDER_NUMBER','reWriteOrderNumber',$session->get('reWriteOrderNumber', 1, 'vm'));
echo VmHTML::row('checkbox','COM_VIRTUEMART_MIGRATION_USER_ORDER_ID','userOrderId',$session->get('userOrderId', 0, 'vm'));
echo VmHTML::row('checkbox','COM_VIRTUEMART_MIGRA_SGRP_PRICES','userSgrpPrices',$session->get('userSgrpPrices', 0, 'vm'));
echo VmHTML::row('checkbox','COM_VIRTUEMART_MIGRA_PORTFLY','portFlypages',$session->get('portFlypages', 0, 'vm'));
echo VmHTML::row('input','COM_VIRTUEMART_MIGRATION_DCAT_BROWSE','migration_default_category_browse',$session->get('migration_default_category_browse', 0, 'vm'));
echo VmHTML::row('input','COM_VIRTUEMART_MIGRATION_DCAT_FLY','migration_default_category_fly',$session->get('migration_default_category_fly', 0, 'vm'));


?>

</table>
    <!-- Hidden Fields -->
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="updatesmigration" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>


<?php ?>
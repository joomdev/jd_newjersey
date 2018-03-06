<?php
/**
*
* Configuration Wizard for Safepath
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2017 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_tools.php 4007 2011-08-31 07:31:35Z alatak $
*/



$session = JFactory::getSession();
$current = VmConfig::get('forSale_path','');

$used = 'COM_VM_ACTIVE';
if(!empty($current)){
	$cur = str_replace('/',DS,$current);
	$vm_path_adm = str_replace('/',DS,VMPATH_ADMIN);
	$vm_path_adm = rtrim($vm_path_adm,DS);
	//vmdebug('$current $vm_path_adm',$cur,$vm_path_adm);
	if(strpos($cur,$vm_path_adm)===0){
		//vmdebug('$current VMPATH_ADMIN === 0',$cur,$vm_path_adm);
		$cur = rtrim($cur,DS);
		if(DS!='/'){
			$cur = rtrim($cur,'/');
		}

		$rdspos = max( strrpos($cur, '/'), strrpos($cur, '\\') );
		if($rdspos!==false) {
			$token = substr( $cur, $rdspos + 1 );
		}
		$session->set('safepathtoken',$token);
		$used = 'COM_VM_INACTIVE';
	}
}

$uPath = shopFunctions::getUpperJoomlaPath();
$usafePath = $uPath.DS.'vmfiles';

$invoice = shopFunctions::getInvoicePath($usafePath.DS);
$encryptSafePath = $usafePath .DS. vmCrypt::ENCRYPT_SAFEPATH;


//$suggestedPath = shopFunctions::getSuggestedSafePath();

if(empty($token)) {
	$token = vRequest::getVar( 'safepathtoken', $session->get( 'safepathtoken', '' ));
}

if(empty($token)){
	if(!class_exists('vmCrypt'))
		require(VMPATH_ADMIN.'/helpers/vmcrypt.php');
	$token = vmCrypt::getToken(21);
	$session->set('safepathtoken',$token);
}

$safePath = str_replace('/',DS, VMPATH_ADMIN.'/'.$token);


//$suggestedPath2 = VMPATH_ADMIN.DS.vmCrypt::getToken(21).DS;
echo '<div>'.vmText::sprintf('COM_VM_SAFEPATH_EXPLAIN', htmlspecialchars($usafePath), htmlspecialchars($safePath),'').'</div>';


if(!class_exists('JFolder')){
	require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
}

$extra = '';
if(vRequest::getBool('show_spwizard',false)){
	$extra = '&show_spwizard=1';
}

if(empty($current)){
	$empty = vmText::sprintf('COM_VM_SAFEPATH_WARN_EMPTY', vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH'), '');
	echo '<div style="font-size:18px">'.vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH').': '.$empty.'</div>';
} else {
	echo '<div style="font-size:18px">'.vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH').': '.$current.'</div>';
	echo '<fieldset><legend>'.vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH').' '.vmText::_('COM_VM_ACTIVE').'</legend>';

	$foldersToTest = array('safe_path'=>$current,'invoice_path' => shopFunctions::getInvoicePath($current), 'keys_path' => $current.DS. vmCrypt::ENCRYPT_SAFEPATH);
	echo $this->writePathLines($foldersToTest);
	echo '</fieldset>';
}

if( ($usafePath.DS) != $current) {

	$used = 'COM_VM_INACTIVE';

	echo '<fieldset><legend>'.vmText::_( 'COM_VM_FOLDERS_SAFEPATH_UPPER' ).' '.vmText::_( $used ).'</legend>';
	if(JFolder::exists( $uPath ) and is_writable( $uPath )) {

		echo $this->renderTaskButton( 'setsafepathupper', 'COM_VM_INSTALL_SAFEPATH_UPPER', $extra );
		echo '<div class="clear"></div>';
	}

	$foldersToTest = array('upper_path' => $uPath, 'safe_path' => $usafePath, 'invoice_path' => $invoice, 'keys_path' => $encryptSafePath);
	echo $this->writePathLines( $foldersToTest );
	echo '</fieldset>';
}

$invoice = shopFunctions::getInvoicePath($safePath.'/');
$encryptSafePath = $safePath .'/'. vmCrypt::ENCRYPT_SAFEPATH;


if( ($safePath.DS) == $current){
	$used = 'COM_VM_ACTIVE';
} else {
	$used = 'COM_VM_INACTIVE';
}

echo '<fieldset><legend>'.vmText::_('COM_VM_FOLDERS_SAFEPATH_COM').' '.vmText::_($used).'</legend>';
echo '<div style="font-size:16px">Token: '.$token.'</div>';
echo $this->renderTaskButton('setsafepathcom','COM_VM_INSTALL_SAFEPATH_COM','&safepathToken='.$token.$extra);
echo '<div class="clear"></div>';
$foldersToTest = array('com_virtuemart' => VMPATH_ADMIN,'safe_path'=>$safePath,'invoice_path' => $invoice, 'keys_path' => $encryptSafePath);
echo $this->writePathLines($foldersToTest);
echo '</fieldset>';
?>
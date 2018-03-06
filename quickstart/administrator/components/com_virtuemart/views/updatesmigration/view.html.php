<?php
/**
 *
 * UpdatesMigration View
 *
 * @package	VirtueMart
 * @subpackage UpdatesMigration
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9659 2017-10-26 22:06:20Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for maintaining the Installation. Updating of the files and imports of the database should be done here
 *
 * @package	VirtueMart
 * @subpackage UpdatesMigration
 * @author Max Milbers
 */
class VirtuemartViewUpdatesMigration extends VmViewAdmin {

	function display($tpl = null) {


		$latestVersion = vRequest::getVar('latestverison', '');

		JToolBarHelper::title(vmText::_('COM_VIRTUEMART_UPDATE_MIGRATION'), 'head vm_config_48');

		if (!class_exists('VmImage'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');
		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');
		if(!class_exists('vmCrypt'))
			require(VMPATH_ADMIN.'/helpers/vmcrypt.php');

		$this->assignRef('latestVersion', $latestVersion);

		$freshInstall = vRequest::getInt('redirected',0);
		if($freshInstall){
			$this->setLayout('install');
		}

		//For uncached file permissions
		clearstatcache();
		vmLanguage::loadJLang('com_virtuemart_config');
		parent::display($tpl);
	}

	public function renderTaskButton($task, $descr, $extra=''){

		$link= JRoute::_('index.php?option=com_virtuemart&view=updatesmigration&task='.$task.'&'.JSession::getFormToken().'=1'.$extra );
		$html = '<div class="icon">
<a onclick="javascript:confirmation(\''.addslashes( vmText::_($descr.'_CONFIRM_JS') ).'\', \''.$link.'\');">';
		$html .= '<span class="vmicon48"></span>';
		$html .= '<br />'.vmText::_($descr).'</a></div>';
		return $html;
	}

	function writePathLines($folders){
		$style = 'text-align:left;margin-left:20px;';
		$result = '<div class="vmquote" style="'.$style.'">';
		$result .='<table>';
		foreach( $folders as $dir ) {
			$result .= '<tr>';
			$result .= '<td>'.$dir . '</td>';
			$result .= '<td>';
			//$result .= JFolder::exists( $dir )

			if($ex = JFolder::exists( $dir )){
				$c = 'green';
				$t = 'COM_VM_FEXISTS';
				$p = substr(decoct(fileperms($dir)),2);
			} else {
				$c = 'red';
				$t = 'COM_VM_FNOTEXISTS';
				$p = '';
			}
			$result .= '<span style="font-weight:bold;color:'.$c.';">'.vmText::_($t).'</span>';
			//? '<span style="font-weight:bold;color:green;">'.vmText::_('COM_VM_FEXISTS').'</span>'
			//: '<span style="font-weight:bold;color:red;">'.vmText::_('COM_VM_FNOTEXISTS').'</span>';
			$result .= '</td><td>';
			if(is_writable( $dir )){
				$c = 'green';
				$t = 'COM_VIRTUEMART_WRITABLE';

			} else {
				$c = 'red';
				$t = 'COM_VIRTUEMART_UNWRITABLE';

			}
			$result .= '<span style="font-weight:bold;color:'.$c.';">'.vmText::_($t).'</span>';
			/*$result .= is_writable( $dir )
			? '<span style="font-weight:bold;color:green;">'.vmText::_('COM_VIRTUEMART_WRITABLE').'</span>'
			: '<span style="font-weight:bold;color:red;">'.vmText::_('COM_VIRTUEMART_UNWRITABLE').'</span>';*/
			$result .= '<td>'.$p . '</td>';
			$result .= '</td></tr>';
		}
		$result .= '</table></div>';
		return $result;
	}
}
// pure php no closing tag

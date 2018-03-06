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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

$p = JPATH_ROOT .'/administrator/components/com_tcpdf/tcpdf.xml';

if(JFile::exists($p)) {
	$content = simplexml_load_file($p);
	if(!empty($content)){
		echo 'Version: '.$content->version.' of '.$content->creationDate .'<br>';
		echo $content->description .'<br>';
		echo 'Authors: '.$content->author .' '.$content->authorUrl.'<br>';
		echo $content->copyright .'<br>';
		echo 'License: '.$content->license .'<br>';

	}
}

if (!class_exists( 'VmConfig' )) {
	$path = JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php';
	if(!file_exists($path)){
		$app = JFactory::getApplication();
		$app->enqueueMessage('VirtueMart Core is not installed, please install VirtueMart again, or uninstall the AIO component by the joomla extension manager');
		return false;
	}
}

JToolBarHelper::title('TCPDF');
$app = JFactory::getApplication();
$app->enqueueMessage('TCPDF installed and available');





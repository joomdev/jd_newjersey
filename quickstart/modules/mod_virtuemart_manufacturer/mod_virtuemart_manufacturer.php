<?php
defined('_JEXEC') or  die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* manufacturer Module
*
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2012-2014 The VirtueMart Team
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* @link https://virtuemart.net
*/

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

VmConfig::loadConfig();
vmLanguage::loadJLang('mod_virtuemart_manufacturer', true);

$display_style = 	$params->get( 'display_style', "div" ); // Display Style
$manufacturers_per_row = $params->get( 'manufacturers_per_row', 1 ); // Display X manufacturers per Row
$headerText = 		$params->get( 'headerText', '' ); // Display a Header Text
$footerText = 		$params->get( 'footerText', ''); // Display a footerText
$show = 			$params->get( 'show', 'all'); // Display a footerText

$model = VmModel::getModel('Manufacturer');
$manufacturers = $model->getManufacturers(true, true,true);
$model->addImages($manufacturers);
if(empty($manufacturers)) return false;

// load the template
require JModuleHelper::getLayoutPath('mod_virtuemart_manufacturer', $params->get('layout', 'default'));
?>
<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: jump.php 8699 2015-02-12 16:51:08Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */


$_GET["option"] = "com_virtuemart";
$_GET["element"] = "realex_hpp_api";

$_REQUEST["option"] = "com_virtuemart";
$_REQUEST["element"] = "realex_hpp_api";

$_GET["view"] = "pluginresponse";
$_GET["task"] = "pluginnotification";
$_GET["format"] = "raw";
$_GET["notificationTask"] = "jumpRedirect";

$_REQUEST["view"] = "pluginresponse";
$_REQUEST["task"] = "pluginnotification";
$_REQUEST["format"] = "raw";
$_REQUEST["notificationTask"] = "jumpRedirect";
$vmpayment_realex_path = dirname(__FILE__);
if( file_exists($vmpayment_realex_path."/../../../index.php")) {
	include($vmpayment_realex_path."/../../../index.php");
}
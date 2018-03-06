<?php
defined('JPATH_BASE') or die();
/**
 *
 * a special type of Klarna
 * @author Val√©rie Isaksen
 * @version $Id:
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2012 iStraxx - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 * http://virtuemart.net
 */

    if (!defined('JPATH_VMKLARNAPLUGIN'))
	define('JPATH_VMKLARNAPLUGIN', VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna');
    if (!defined('VMKLARNAPLUGINWEBROOT'))
	define('VMKLARNAPLUGINWEBROOT', 'plugins/vmpayment/klarna');
    if (!defined('VMKLARNAPLUGINWEBASSETS'))
	define('VMKLARNAPLUGINWEBASSETS', JURI::root() . VMKLARNAPLUGINWEBROOT . '/klarna/assets');

if (!defined('VMKLARNA_PC_TYPE'))
    define('VMKLARNA_PC_TYPE', 'json');
if (!defined('VMKLARNA_CONFIG_FILE'))
  define('VMKLARNA_CONFIG_FILE',JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna.cfg');

if (!defined('VMPAYMENT_KLARNA_MERCHANT_ID_VM'))
	define('VMPAYMENT_KLARNA_MERCHANT_ID_VM', '1926');
if (!defined('VMPAYMENT_KLARNA_MERCHANT_ID_DEMO'))
	define('VMPAYMENT_KLARNA_MERCHANT_ID_DEMO', '2236');
if (!defined('VMPAYMENT_KLARNA_CONF_PC_TYPE'))
	define('VMPAYMENT_KLARNA_CONF_PC_TYPE', 'json');

if (!defined('VMPAYMENT_KLARNACHECKOUT_MERCHANT_ID_VM'))
	define('VMPAYMENT_KLARNACHECKOUT_MERCHANT_ID_VM', '709');
// No closing tag
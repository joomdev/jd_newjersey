<?php
defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * @version $Id: klarna_virtuemart.php 7953 2014-05-18 14:06:25Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (C) 2012 iStraxx - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

define('KLARNA_MODULE_VERSION', '5.0.3');
if (!class_exists('Klarna'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
class Klarna_virtuemart extends Klarna {

    public function __construct() {
        $this->VERSION = 'PHP'.phpversion().':3.2.6';
        Klarna::$debug =  false;
    }
}


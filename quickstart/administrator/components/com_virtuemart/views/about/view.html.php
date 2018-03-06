<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die('Restricted access');

// Load the view framework
if(!class_exists( 'VmViewAdmin' )) require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package        VirtueMart
 * @author
 */
class VirtuemartViewAbout extends VmViewAdmin {

	function display ($tpl = null) {

		JToolBarHelper::title( vmText::_( 'COM_VIRTUEMART_ABOUT' )."::".vmText::_( 'COM_VIRTUEMART_CONTROL_PANEL' ), 'vm_store_48' );

		parent::display( $tpl );
	}


}

//pure php no tag
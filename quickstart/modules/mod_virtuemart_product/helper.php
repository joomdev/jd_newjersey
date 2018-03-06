<?php
	defined ('_JEXEC') or  die('Direct Access to ' . basename (__FILE__) . ' is not allowed.');
/*
 * Module Helper
 * just for legacy, will be removed
 * @package VirtueMart
 * @copyright (C) 2011 - 2014 The VirtueMart Team
 * @Email: max@virtuemart.net
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * @link https://virtuemart.net
 */

class mod_virtuemart_product {

	/*
	 * @deprecated
	 */
	static function addtocart ($product) {

		echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product));
	}
}

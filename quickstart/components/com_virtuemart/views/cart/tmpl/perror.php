<?php
/**
*
* Error Layout for the add to cart popup
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
*
* @link https://virtuemart.net
* @copyright Copyright (c) 2013 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @version $Id: cart.php 2551 2010-09-30 18:52:40Z milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

echo '<a class="continue" href="' . $this->continue_link . '" >' . vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';

$messageQueue = JFactory::getApplication()->getMessageQueue();
foreach ($messageQueue as $message) {
	echo '<div>'.$message['message'].'</div>';
}

if(!empty($this->product_name)) {
	echo '<br><h4>'.$this->product_name.'</h4>';
}

?>
<br style="clear:both">

<?php
/**
*
* Set the descriptions for a product
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: product_edit_description.php 9499 2017-04-11 13:42:24Z Milbo $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');?>
<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_S_DESC'); echo $this->origLang ?></legend>
	<textarea class="inputbox" name="product_s_desc" id="product_s_desc" cols="65" rows="3" ><?php echo $this->product->product_s_desc; ?></textarea>

</fieldset>
			
<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DESCRIPTION'); echo $this->origLang ?></legend>
	<?php echo $this->editor->display('product_desc',  $this->product->product_desc, '90%;', '450', '55', '10', array('pagebreak', 'readmore') ) ; ?>
</fieldset>

<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_METAINFO'); echo $this->origLang ?></legend>
	<?php echo shopFunctions::renderMetaEdit($this->product); ?>
</fieldset>


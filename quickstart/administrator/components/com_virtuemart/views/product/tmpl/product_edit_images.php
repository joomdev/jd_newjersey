<?php
/**
*
* The main product images
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
* @version $Id: product_edit_images.php 9461 2017-02-28 15:37:01Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


?>
<div class="col50">
	<div class="selectimage">
	<?php
		if(empty($this->product->images[0]->virtuemart_media_id)) $this->product->images[0]->addHidden('file_is_product_image','1');
		if (!empty($this->product->virtuemart_media_id)) echo $this->product->images[0]->displayFilesHandler($this->product->virtuemart_media_id,'product');
		else echo $this->product->images[0]->displayFilesHandler(null,'product');
	?>
	</div>
	<div>
		<?php
			//echo '<div width="100px">'.vmText::_('COM_VIRTUEMART_RTB_AD').'</div>';
			$jlang =JFactory::getLanguage();
			$tag = $jlang->getTag();
			$imgUrl = 'https://www.pixelz.com/images/gmail.png';
			if(strpos($tag,'de')!==FALSE){
				$url = 'https://de.pixelz.com/virtuemart/';
			} else if(strpos($tag,'fr')!==FALSE){
				$url = 'https://fr.pixelz.com/virtuemart/';
			} else {
				$url = 'https://uk.pixelz.com/virtuemart/';
			}
			echo '<a href="'.$url.'" target="_blank" alt="'.vmText::_('COM_VIRTUEMART_RTB_AD').'"><img  style="width: 150px;" src="'.$imgUrl.'" title="'.vmText::_('COM_VIRTUEMART_RTB_AD').'"></a>';
		?>
	</div>
</div>

<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_images.php 9586 2017-06-22 13:19:17Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

vmJsApi::loadPopUpLib();
if(VmConfig::get('usefancy',1)){
	if(VmConfig::get('add_thumb_use_descr', false)){
		$u = 'descr';
	} else {
		$u = 'this.alt';
	}

$imageJS = '
jQuery(document).ready(function() {
	Virtuemart.updateImageEventListeners()
});
Virtuemart.updateImageEventListeners = function() {
	jQuery("a[rel=vm-additional-images]").fancybox({
		"titlePosition" 	: "inside",
		"transitionIn"	:	"elastic",
		"transitionOut"	:	"elastic"
	});
	jQuery(".additional-images a.product-image.image-0").removeAttr("rel");
	jQuery(".additional-images img.product-image").click(function() {
		jQuery(".additional-images a.product-image").attr("rel","vm-additional-images" );
		jQuery(this).parent().children("a.product-image").removeAttr("rel");
		var src = jQuery(this).parent().children("a.product-image").attr("href");
		jQuery(".main-image img").attr("src",src);
		jQuery(".main-image img").attr("alt",this.alt );
		jQuery(".main-image a").attr("href",src );
		jQuery(".main-image a").attr("title",this.alt );
		jQuery(".main-image .vm-img-desc").html('.$u.');
		}); 
	}
	';
} else {
	$imageJS = '
	jQuery(document).ready(function() {
		Virtuemart.updateImageEventListeners()
	});
	Virtuemart.updateImageEventListeners = function() {
		jQuery("a[rel=vm-additional-images]").facebox();
		var imgtitle = jQuery("span.vm-img-desc").text();
		jQuery("#facebox span").html(imgtitle);
	}
	';
}

vmJsApi::addJScript('imagepopup',$imageJS);

if (!empty($this->product->images)) {
	$image = $this->product->images[0];
	?>
	<div class="main-image">
		<?php
		$width = VmConfig::get('img_width_full', 0);
		$height = VmConfig::get('img_height_full', 0);
		if(!empty($width) or !empty($height)){
			echo $image->displayMediaThumb("",true,"rel='vm-additional-images'", true, true, false, $width, $height);
		} else {
			echo $image->displayMediaFull("",true,"rel='vm-additional-images'");
		}
		 ?>
		<div class="clear"></div>
	</div>
	<?php
}
?>

<?php
/**
 * @package		Skillset
 * @subpackage	Skillset
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2014 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later
 * @version		1.0.0
 */

// no direct access
defined('_JEXEC') or die;

$skills = rtrim($params->get('skills'), "|");
$skills = explode('|', $skills);

// The loop
foreach ( $skills as $skill ) { 
		
	$skill = explode(',', $skill); ?>
			
	<div class="zen-skillbar zen-skillbar-<?php echo $module->id;?> clearfix " data-percent="<?php echo $skill[1];?>%">
		<div class="zen-skillbar-title" style="background: <?php echo $skill[3];?>"><span><?php echo $skill[0];?></span></div>
		<div class="zen-skillbar-bar" style="background: <?php echo $skill[2];?>"></div>
		<div class="zen-skill-bar-percent"><?php echo $skill[1];?>%</div>
	</div> 

<?php } ?>
		
<script>
jQuery( document ).ready(function() {
	// If Visible
	jQuery('.zen-skillbar-<?php echo $module->id;?>').each(function(i, el){
	
		var el = jQuery(el);
		
		if (el.visible(true)) {
		  jQuery(this).find('.zen-skillbar-bar').animate({
		  	width:jQuery(this).attr('data-percent')
		  },3000); 
	
		}
		 
	 });
	 
// Only trigger the effect if the item is visible
jQuery(window).scroll(function(event) {
  
  jQuery('.zen-skillbar-<?php echo $module->id;?>').each(function(i, el){
  
  	var el = jQuery(el);
  	
  	if (el.visible(true)) {
  	  jQuery(this).find('.zen-skillbar-bar').animate({
  	  	width:jQuery(this).attr('data-percent')
  	  },3000); 
  
  	}
  	 
   });
   
});
});

(function($) {
	
	  /**
	   * Copyright 2012, Digital Fusion
	   * Licensed under the MIT license.
	   * http://teamdf.com/jquery-plugins/license/
	   *
	   * @author Sam Sehnert
	   * @desc A small plugin that checks whether elements are within
	   *     the user visible viewport of a web browser.
	   *     only accounts for vertical position, not horizontal.
	   */
	
	  $.fn.visible = function(partial) {
	    
	      var $t            = $(this),
	          $w            = $(window),
	          viewTop       = $w.scrollTop(),
	          viewBottom    = viewTop + $w.height(),
	          _top          = $t.offset().top,
	          _bottom       = _top + $t.height(),
	          compareTop    = partial === true ? _bottom : _top,
	          compareBottom = partial === true ? _top : _bottom;
	    
	    return ((compareBottom <= viewBottom) && (compareTop >= viewTop));
	
	  };
	    
	})(jQuery);
</script>
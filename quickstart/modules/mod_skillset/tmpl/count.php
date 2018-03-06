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
$totalitems = count($skills);

// The Loop
foreach ( $skills as $key => $skill ) { 
	$skill = explode(',', $skill); 
	$count = str_replace('%', '', $skill[1]);
?>
		
	<div id="count-<?php echo $key;?>-<?php echo $module->id;?>" class="skill-count-item skill-count-item-<?php echo $module->id;?> skill-count-items-<?php echo $totalitems;?> skill-count-item<?php echo $key;?>">
			<h2></h2>
			<p><strong><?php echo $skill[0];?></strong></p>
	</div>
	
	<script>
	
	jQuery( document ).ready(function() {
		// If visible
		jQuery('.skill-count-item-<?php echo $module->id;?>').each(function(i, el){
			var el = jQuery(el);
			if (el.visible(true)) {
				if(jQuery('#count-<?php echo $key;?>-<?php echo $module->id;?> h2').text() == '') {
					countup('#count-<?php echo $key;?>-<?php echo $module->id;?> h2', '<?php echo $count;?>');
			  	}
			}
		});
				
		// Only trigger the effect if the item is visible
		jQuery(window).scroll(function(event) {
			jQuery('.skill-count-item-<?php echo $module->id;?>').each(function(i, el){
				var el = jQuery(el);
				if (el.visible(true)) {
					if(jQuery('#count-<?php echo $key;?>-<?php echo $module->id;?> h2').text() == '') {
						countup('#count-<?php echo $key;?>-<?php echo $module->id;?> h2', '<?php echo $count;?>');
				  	}
				}
			});
		});
	});
	</script>
	<?php } ?>
	
	<script>				
	function countup(element, total) {
	   
	    var current = 0;
	    var finish = total;
	    var miliseconds = 1000;
	    var rate = 1;
		
		if(current == 0) {
		    var counter = setInterval(function(){
		         if(current >= finish) clearInterval(counter);
		         jQuery(element).text(current);
		         current = parseInt(current) + parseInt(rate);
		    }, miliseconds / (finish / rate));
		}
	};
		
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
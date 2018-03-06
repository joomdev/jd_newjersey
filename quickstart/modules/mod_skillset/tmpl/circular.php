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
$totalitems = count($skills); ?>

<div class="zen-skills-container">
		<?php foreach ( $skills as $key=>$skill ) { 

			$skill = explode(',', $skill);
			
		?>
		
		<div id="skill-<?php echo $key;?>-<?php echo $module->id;?>" class="skill-circle-item skill-circle-item-<?php echo $module->id;?> skill-count-item<?php echo $key;?>" data-dimension="250" data-text="<?php echo $skill[1];?>%" data-info="" data-width="6" data-fontsize="38" data-percent="<?php echo $skill[1];?>" data-fgcolor="<?php echo $skill[2];?>" data-bgcolor="#fafafa" data-fill="#fff"></div>
		
		
			
			<script>
			jQuery( document ).ready(function() {
			
				// If visible
				jQuery('.skill-circle-item-<?php echo $module->id;?>').each(function(i, el){
					var el = jQuery(el);
					if (el.visible(true)) {
						if(jQuery('#skill-<?php echo $key;?>-<?php echo $module->id;?>').text() == '') {
							 jQuery('#skill-<?php echo $key;?>-<?php echo $module->id;?>').circliful();
							 jQuery('#skill-<?php echo $key;?>-<?php echo $module->id;?>').append('<p><?php echo $skill[0];?></p>');
							 
					  	}
					}
				});
			
				// Only trigger the effect if the item is visible
				jQuery(window).scroll(function(event) {
					jQuery('.skill-circle-item-<?php echo $module->id;?>').each(function(i, el){
						var el = jQuery(el);
						if (el.visible(true)) {
							if(jQuery('#skill-<?php echo $key;?>-<?php echo $module->id;?>').text() == '') {
								 jQuery('#skill-<?php echo $key;?>-<?php echo $module->id;?>').circliful();
								 jQuery('#skill-<?php echo $key;?>-<?php echo $module->id;?>').append('<p><?php echo $skill[0];?></p>');
								 
						  	}
						}
					});
				});
				
				
				
			       
			    });
			</script>
		
		<?php } ?>
</div>
		<script src="modules/mod_skillset/js/jquery.circliful.min.js"></script>
				
<script>


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
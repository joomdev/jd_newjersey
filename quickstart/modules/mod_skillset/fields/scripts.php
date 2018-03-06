<?php
/**
* @package Author
* @author Joomla Bamboo
* @website www.joomlabamboo.com
* @email design@joomlabamboo.com
* @copyright Copyright (c) 2013 Joomla Bamboo. All rights reserved.
* @license GNU General Public License version 2 or later
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Mod_zensocial
 * @subpackage	Form
 * @since		1.6
 */

class JFormFieldScripts extends JFormField
{
	protected $type = 'scripts';

	protected function getInput()
	{
		$document = JFactory::getDocument();
		$root = JURI::root();

		if (version_compare(JVERSION, '3.0', '<'))
		{
			$document->addScript(''.$root.'modules/mod_zensocial/js/admin/jquery-1.8.3.min.js');
			$document->addScript(''.$root.'modules/mod_zensocial/js/admin/jquery.noconflict.js');	
		}
		else
		{
		
		}
		
		
		$document->addScript(''.$root.'modules/mod_skillset/js/jquery.circliful.min.js');

		ob_start();?>
		
		
		<!-- Container for preview in admin -->
		<div id="skills-demo" style="width:90%"></div>
		<div style="clear:both"></div>
		<!-- Colour inspiration -->
		<div id="help-panel" class="panel"
			<h3>You can click on an item to change it's color.</h3>
			<p class="pull-right" style="margin-top: -4px">Need some colour inspiration?  <a class="btn btn-success" style="margin:0 0 8px 20px" href="https://kuler.adobe.com/explore/most-popular/?time=month">Visit Kuler</a>
			</p>
		</div>
			
		<!-- Add a new skill button -->	
		<a class="new-skill btn btn-success" style="margin: 30px 0" >Add a new skill</a>
		
		<!-- Container for the skills -->
		<div id="skills-field"></div>
			<div id="holder"></div>
			<script type="text/javascript">
			
				jQuery(document).ready(function() {
				
					// Set values and load demo
					setvalues();
					
					if(jQuery("label[for='jform_params_display0']").hasClass('active')) {
						triggerdemo();
					} 
					
					if(jQuery("label[for='jform_params_display1']").hasClass('active')) {
						countupdemo();
					}
					if(jQuery("label[for='jform_params_display2']").hasClass('active')) {
						circulardemo();
					}
					
					// Toggle demos on click
					jQuery('#jform_params_display').click(function() {
					
						if(jQuery("label[for='jform_params_display0']").hasClass('active')) {
							triggerdemo();
						} 
						
						if(jQuery("label[for='jform_params_display1']").hasClass('active')) {
							countupdemo();
						}
						if(jQuery("label[for='jform_params_display2']").hasClass('active')) {
							circulardemo();
						}
					
					});
					
					
					
					// Make sure values are set when user hits save
					jQuery("button").on('click', function() {
						getvalues();
					});
					
					// The template for creatinmg the inputs
					var skillform = '<fieldset><input type="text" class="skill input-large" placeholder="Type your skill…"><br /><br /><input class="skill-level input-large" type="text" placeholder="Type your skill level"><input class="color" type="text" value="#e67e22,#d35400"><br /><br /><a class="remove-skill btn btn-danger">Remove skill</a><br /><br /><br /><br /><div style="border-bottom:1px solid #eee;margin-bottom: 30px;"></div></fieldset>';
					
					
					// A a new skillset
					jQuery(".new-skill").on('click', function() {
						jQuery("#skills-field").prepend(skillform);
						return false;
					});
					
					
					// Remove a skillset
					jQuery(".remove-skill").live('click', function() {
						jQuery(this).parent().remove();	
						getvalues();
						
						
						if(jQuery("label[for='jform_params_display0']").hasClass('active')) {
							triggerdemo();
						} 
						
						if(jQuery("label[for='jform_params_display1']").hasClass('active')) {
							countupdemo();
						}
						if(jQuery("label[for='jform_params_display2']").hasClass('active')) {
							circulardemo();
						}

						return false;
					});
					
					
					// Get values on blur
					jQuery( "#skills-field input" ).live('blur', function() {
						getvalues();
						
						if(jQuery("label[for='jform_params_display0']").hasClass('active')) {
							triggerdemo();
						} 
						
						if(jQuery("label[for='jform_params_display1']").hasClass('active')) {
							countupdemo();
						}
						if(jQuery("label[for='jform_params_display2']").hasClass('active')) {
							circulardemo();
						}					
					});
					
					
					// Fade in colour wheel
					// Get the skill and corresponding skill info we just activated
					jQuery('#skills-demo .zen-skillbar,#skills-demo .skill-circle').live('click', function() {
						jQuery('#skills-demo .zen-skillbar,#skills-demo .skill-circle').removeClass('active').addClass('inactive');
						jQuery(this).removeClass('inactive').addClass('active');
						jQuery('.minicolors').fadeIn();
					  	var index = jQuery( "#skills-demo .zen-skillbar,#skills-demo .skill-circle" ).index( this ) + 1;
					  	jQuery('#skills-field fieldset').removeClass('active');
					  	jQuery('#skills-field fieldset:nth-child('+index+')').addClass('active');
					});
					
					
					// On bind - live is deprecated in J1.9 +
					jQuery('#jform_params_color').live('bind', function() {
						getcolors() 
						getvalues();
						
						if(jQuery("label[for='jform_params_display2']").hasClass('active')) {
							circulardemo();
						}
					});
					
					// On Blur
					jQuery('#jform_params_color').live('blur', function() {
						getcolors() 
						getvalues();
						
						if(jQuery("label[for='jform_params_display2']").hasClass('active')) {
							circulardemo();
						}
					});
					
					// On click
					jQuery('.minicolors span').live('click', function() {
						getcolors() 
						getvalues();
						
						if(jQuery("label[for='jform_params_display2']").hasClass('active')) {
							circulardemo();
						}
					});
					
					
					function setvalues() {
						
						jQuery('#skills-demo').empty();
						var skills = jQuery('#jform_params_skills').val();
						skills = skills.split('|');
						
						jQuery(skills).each(function( key, value) {
							if(value !=="") {
							
								var items = value.split(',');
								
								jQuery("#skills-field").append(
									'<fieldset><input type="text" class="skill input-large" placeholder="Type your skill…" value="'+ items[0] + '"><br /><br /><input class="skill-level input-large" type="text" placeholder="Type your skill level" value="' + items[1] + '"><input class="color" type="text" value="' + items[2] + ',' + items[3] + '"><br /><br /><a class="remove-skill btn btn-danger">Remove skill</a><br /><br /><br /><br /><div style="border-bottom:1px solid #eee;margin-bottom: 30px;"></div></fieldset>');
							}
						});

					}
					
					function getcolors() {
						var currentcolor = jQuery('#jform_params_color').val();
						var darker = ColorLuminance(currentcolor, -0.1)
						jQuery('#skills-demo .active .zen-skillbar-bar').css({'background-color':currentcolor});
						jQuery('#skills-demo .active .zen-skillbar-title').css({'background-color':darker});
						jQuery('#skills-demo .zen-skillbar').removeClass('active').removeClass('inactive');
						jQuery('fieldset.active .color').val(currentcolor + ','+ darker);
						
						// Circular
						jQuery('#skills-demo .skill-circle.active').attr('data-fgcolor', currentcolor);
						jQuery('.minicolors').fadeOut();
					}
					
					
					function triggerdemo() {
						
						if(jQuery('.zen-skillbar').length) {
							jQuery('#help-panel').fadeIn();
						}
						
						jQuery('#skills-demo').empty();
						var skills = jQuery('#jform_params_skills').val();
						
						if (skills.indexOf("%") >= 0) {
							alert('Please just add a whole number here without a % sign.');
							return;
						}
						
						
						skills = skills.split('|');
						
						jQuery(skills).each(function( key, value) {
							if(value !=="") {
								var items = value.split(',');		
		
								jQuery("#skills-demo").append('<div class="zen-skillbar clearfix " data-percent="'+ items[1] + '%"><div class="zen-skillbar-title" style="background: '+ items[3]+'"><span>'+ items[0] + '</span></div><div class="zen-skillbar-bar" style="background:'+ items[2]+'"></div><div class="zen-skill-bar-percent">'+ items[1] + '%</div></div> <!-- End Skill Bar -->');
								
							}
						});
						
						// Animate the bar
						jQuery('.zen-skillbar').each(function(){
								jQuery(this).find('.zen-skillbar-bar').animate({
									width:jQuery(this).attr('data-percent')
								},3000);
						});
					}
					
				
					
					function countupdemo() {
						
						jQuery('#help-panel').fadeOut();
						
						jQuery('#skills-demo').empty();
						
						var skills = jQuery('#jform_params_skills').val();
						
						if (skills.indexOf("%") >= 0) {
							
							alert('Count up values need to be in whole numbers and not percentages.');
							return;
						}
						skills = skills.split('|');
						
											
						jQuery(skills).each(function( key, value) {
							
							if(value !=="") {
								var items = value.split(',');	
								total = items[1];
								jQuery('#skills-demo').append('<div id="count-' + key + '" class="skill-count-item skill-count-item' + key +'"><h2></h2><p><strong>' + items[0] + '</strong></p></div>');
							
								countup('#count-' + key + ' h2', total);
							}
						});
						
					}
					
					function circulardemo() {
									
						
						
						jQuery('#skills-demo').empty();
						var skills = jQuery('#jform_params_skills').val();
						
						if (skills.indexOf("%") >= 0) {
							alert('Please just add a whole number here without a % sign.');
							return;
						}
						
						
						skills = skills.split('|');
						
						jQuery(skills).each(function( key, value) {
							if(value !=="") {
								var items = value.split(',');		
		
								jQuery("#skills-demo").append('<div id="skill-' + key + '" class="zen-skillcircle skill-count-item skill-count-item' + key +' skill-circle" data-dimension="250" data-text="' + items[1] +'%" data-info="" data-width="6" data-fontsize="38" data-percent="' + items[1] +'" data-fgcolor="'+items[2]+'" data-bgcolor="#fafafa" data-fill="#fff"></div>');
								
								// Animate the bar
								jQuery("#skill-" + key).circliful();
								jQuery("#skill-" + key).append('<p>' + items[0] +'</p>');
							}
						});
						
						if(jQuery('.zen-skillcircle').length) {
							jQuery('#help-panel').fadeIn();
						}
						
						
					}
					
					function getvalues() {
						jQuery('#holder').html('');
						
						jQuery( "#skills-field input" ).each(function( index ) {
							
							var input = jQuery(this).val();
							
							jQuery('#holder').append(input);
							
							if(jQuery(this).hasClass('skill')) {
								jQuery('#holder').append(',');
							}
							
							if(jQuery(this).hasClass('skill-level')) {
								jQuery('#holder').append(',');
							}
							
							if(jQuery(this).hasClass('color')) {
								jQuery('#holder').append('|');
							}
						});
						
						jQuery('#jform_params_skills').val(jQuery('#holder').html());
					}
					
					
					function ColorLuminance(hex, lum) {
					
						// validate hex string
						hex = String(hex).replace(/[^0-9a-f]/gi, '');
						if (hex.length < 6) {
							hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
						}
						lum = lum || 0;
					
						// convert to decimal and change luminosity
						var rgb = "#", c, i;
						for (i = 0; i < 3; i++) {
							c = parseInt(hex.substr(i*2,2), 16);
							c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
							rgb += ("00"+c).substr(c.length);
						}
					
						return rgb;
					}
					
					
					function countup(element, total) {
					   
					    var current = 0;
					    var finish = total;
					    var miliseconds = 1000;
					    var rate = 1;
						
						total = total.replace('');
						
						if(current == 0) {
						    var counter = setInterval(function(){
						         if(current >= finish) clearInterval(counter);
						         jQuery(element).text(current);
						         current = parseInt(current) + parseInt(rate);
						    }, miliseconds / (finish / rate));
						}
					};

				});
			</script>
			
			<!-- I know this isnt valid but prob wont matter too much -->
			<style>
			
			.panel {
				display: none;
				padding: 15px;
				background: #fafafa;
				color: #666;
				font-weight: 300;
				border: 1px solid #e5e5e5;
				border-radius: 4px;
			}
			.minicolors,
			input.color,
			#jform_params_skills,
			#jform_params_skills-lbl,
			#holder {
				display: none;
			}
			
			.zen-skillbar {
				position:relative;
				display:block;
				margin-bottom:15px;
				width:100%;
				background:#eee;
				height:35px;
				border-radius:3px;
				-moz-border-radius:3px;
				-webkit-border-radius:3px;
				-webkit-transition:0.4s linear;
				-moz-transition:0.4s linear;
				-ms-transition:0.4s linear;
				-o-transition:0.4s linear;
				transition:0.4s linear;
				-webkit-transition-property:width, background-color;
				-moz-transition-property:width, background-color;
				-ms-transition-property:width, background-color;
				-o-transition-property:width, background-color;
				transition-property:width, background-color;
				cursor: pointer;
			}
			
			.zen-skillbar.active {
				opacity: 1;
			}
			
			.zen-skillbar.inactive {
				opacity: 0.6;
			}
			
			.zen-skillbar-title {
				position:absolute;
				top:0;
				left:0;
				opacity: 1.0;
			width:110px;
				font-weight:bold;
				font-size:13px;
				color:#ffffff;
				background:#6adcfa;
				-webkit-border-top-left-radius:3px;
				-webkit-border-bottom-left-radius:4px;
				-moz-border-radius-topleft:3px;
				-moz-border-radius-bottomleft:3px;
				border-top-left-radius:3px;
				border-bottom-left-radius:3px;
			}
			
			.zen-skillbar-title span {
				display:block;
				background:rgba(0, 0, 0, 0.1);
				padding:0 20px;
				height:35px;
				line-height:35px;
				-webkit-border-top-left-radius:3px;
				-webkit-border-bottom-left-radius:3px;
				-moz-border-radius-topleft:3px;
				-moz-border-radius-bottomleft:3px;
				border-top-left-radius:3px;
				border-bottom-left-radius:3px;
			}
			
			.zen-skillbar-bar {
				height:35px;
				width:0px;
				background:#6adcfa;
				border-radius:3px;
				-moz-border-radius:3px;
				-webkit-border-radius:3px;
			}
			
			.zen-skill-bar-percent {
				position:absolute;
				right:10px;
				top:0;
				font-size:11px;
				height:35px;
				line-height:35px;
				color:#ffffff;
				color:rgba(0, 0, 0, 0.4);}
				
				
				
				/* --- Skill Count --*/
					
				.skill-count-item {
					float: left;
					margin-left: 2%;
					text-align: center;
				}
				
				.skill-count-item0 {
					margin-left: 0;
				}
				
				.skill-count-items-1 {
					width: 100%;
					margin: 0;
				}
				
				.skill-count-items-2 {
					width: 48%;	
				}
				
				.skill-count-items-3 {
					width: 31%;	
				}
				
				.skill-count-items-4 {
					width: 23%;	
				}
				
				@media screen and (max-width:620px) {
					.skill-count-item {
						width: 100%;
						margin: 0;
					}
				}
				
				
				/* -- Circle --*/
				
				.circliful {
				    position: relative; 
				}
				
				.circle-text, .circle-info, .circle-text-half, .circle-info-half {
				    width: 100%;
				    position: absolute;
				    text-align: center;
				    display: inline-block;
				    left: 0;
				    cursor: pointer;
				}
				
				.circle-info, .circle-info-half {
					color: #999;
				}
				
				.circliful .fa {
					margin: -10px 3px 0 3px;
					position: relative;
					bottom: 4px;
				}
				</style>
			<?php

		return ob_get_clean();
	}
}
<?php
    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2014 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */

    // no direct access
    defined('_JEXEC') or die;
	
    $helper->addJQuery($document);
	
    $document->addScriptDeclaration("
		jQuery(function($){
			$(window).load(function() {
				$('#sp-smart-slider{$module->id} .bloggani-slider').blogganiSlider({
					autoplay : {$option['autoplay']},
					interval : {$option['interval']}
				});
			});
		
		});
	");
	
	$total=count($data)-1;
?>

<div id="sp-smart-slider<?php echo $module->id; ?>" class="sp-smart-slider layout-bloggani <?php echo $params->get('moduleclass_sfx') ?> ">
	
	<!-- Carousel items -->
	<div class="bloggani-slider">
		<?php foreach($data as $index=>$value):?>
			<div class="slider-item">
				<div class="sp-slider-inner">
				
					<?php //slider introtext ?>
					<div class="slider-introtext hidden-phone">
						<div>
							<?php
								// is strip html
								if( isset($value['striphtml']) and  $value['striphtml']=='yes')
								{ 
									if( isset($value['textlimit']) and $value['textlimit']!='no' )
									{
										if( isset($value['introtext']) ) echo $helper->textLimit(strip_tags($value['introtext'], $value['allowabletag']),$value['limitcount'], $value['textlimit']) ;
									} else {
										if( isset($value['introtext']) ) echo strip_tags($value['introtext'], $value['allowabletag']); 
									}

									// strip intro text html  
								} else {

									// add intro text
									if( isset($value['textlimit']) and $value['textlimit']!='no' )
									{
										if( isset($value['introtext']) ) echo $helper->textLimit($value['introtext'], $value['limitcount'], $value['textlimit']);
									} else {
										if( isset($value['introtext']) ) echo $value['introtext'];
									}
								}
							?>
						</div>
					</div>
					
					<div class="slider-title">
						<div>
							<?php
								// adding pretitle
								if( isset($value['pretitle']) and !empty($value['pretitle']) ) echo '<p class="sp-smart-pretitle">' . $value['pretitle'] . '</p>';
								
								//Linked title
								if(isset($value['showlink']) and $value['showlink']=='yes' ) echo '<a href="' . $value['link'] . '">';
								
									// check title type
									if( isset($value['titletype']) and $value['titletype']=='custom' )
									{
										// add custom title
										if( isset($value['customtitle']) and !empty($value['customtitle']) ) echo '<h1 class="sp-smart-title">' . $value['customtitle'] . '</h1>';
									} else {
										// add title
										if( isset($value['title']) and !empty($value['title']) ) echo '<h1 class="sp-smart-title">' . $value['title'] . '</h1>';  
									}
								
								//Linked title	
								if(isset($value['showlink']) and $value['showlink']=='yes' ) echo '</a>';

								// add post title
								if( isset($value['posttitle']) and !empty($value['posttitle']) ) echo '<p class="sp-smart-posttitle">' . $value['posttitle'] . '</p>';
							?>								
						</div>
					</div>
					
					<div class="slider-image-wrapper">
						<?php if( isset($value['showlink']) and $value['showlink']=='yes' ): ?>
							<a href="<?php echo $value['link'] ?>">
						<?php endif; ?>
						<img class="sp-smart-slider-img" src="<?php echo JURI::root().$value['image'] ?>" alt="<?php  echo $value['title']?>" title="<?php  echo $value['title']?>" width="100%" />
						<?php if(isset($value['showlink']) and $value['showlink']=='yes' ): ?>
							</a>
						<?php endif; ?>
						
						<div class="slider-date-thumb hidden-phone">
							<span><?php echo JHtml::_('date',$value['date'], 'M/d/Y'); ?></span>
							<?php $key = ($index >= $total) ? 0 : $index+1; ?>
							<img src="<?php echo JURI::root().$data[$key]['thumb'] ?>" alt="<?php  echo $data[$key]['title']?>" title="<?php  echo $data[$key]['title']?>" />
						</div>
						
					</div>
				</div>
			</div>
		<?php $i++; endforeach; ?>
		
		<div class="slider-controllers">
			<a class="controller-prev"><span>&lsaquo;</span></a>
			<a class="controller-next"><span>&rsaquo;</span></a>
		</div>	
	</div>
</div>
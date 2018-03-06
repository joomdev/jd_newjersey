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
        jQuery.noConflict();
        jQuery(document).ready(function() {
        jQuery('#sp-smart-slider-{$module_id} .nivoSlider').nivoSlider({
        effect: '{$option['effect']}', // Specify sets like: 'fold,fade,sliceDown'
        slices: {$option['slices']}, // For slice animations
        boxCols: {$option['boxCols']}, // For box animations
        boxRows: {$option['boxRows']}, // For box animations
        animSpeed: {$option['animSpeed']}, // Slide transition speed
        pauseTime: {$option['pauseTime']}, // How long each slide will show
        startSlide: {$option['startSlide']}, // Set starting Slide (0 index)
        pauseOnHover: {$option['pauseOnHover']}, // Stop animation while hovering
        manualAdvance: {$option['manualAdvance']}, // Force manual transitions
        randomStart: {$option['randomStart']}, // Start on a random slide
        afterLoad: function(){
        var controllers = jQuery('#sp-smart-slider-{$module_id} .sp-extreme-controllers .sp-smart-slider-item');
        jQuery('.nivo-controlNav a.nivo-control').each(function(i,val){
        jQuery(this).html(controllers[i]);
        });
        jQuery('#sp-smart-slider-{$module_id} .sp-extreme-controllers').remove();
        }

        });
        });
        ");
?>
<!--SP Smart Slider by JoomShaper.com-->
<div id="sp-smart-slider-<?php echo $module_id ?>" class="sp-smart-slider-extreme <?php echo $params->get('moduleclass_sfx') ?>">
    <!--Slider Item-->
    <div class="nivoSlider">
        <?php foreach($data as $key=>$value): ?>
            <?php if( isset($value['showlink']) and $value['showlink']=='yes' ): ?>
                <a href="<?php echo $value['link'] ?>">
                    <?php endif; ?>
                <img src="<?php echo JURI::root().$value['image'] ?>" data-thumb="<?php echo JURI::root().$value['thumb'] ?>" alt="" title="#sp-smart-caption-<?php echo $key ?>" />
                <?php if( isset($value['showlink']) and $value['showlink']=='yes' ): ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
    </div>

    <!--Captions-->
    <?php foreach($data as $key=>$value): ?>
        <div style="display:none" id="sp-smart-caption-<?php echo $key ?>">
            <a href="<?php echo $value['link'] ?>" class="sp-extreme-readon"><?php echo $value['readmore'] ?><span class="sp-extreme-arrow">&nbsp;</span></a>
        </div>
        <?php endforeach; ?>

    <!--Items controller-->
    <div style="display:none" class="sp-extreme-controllers">
        <?php foreach($data as $key=>$value):

         ?>
            <div class="sp-smart-slider-item sp-anim-<?php echo $key ?>">
                <h2>
                    <?php 
                    if( $value['titletype']=='custom' ) echo $value['customtitle'];
                    else echo $value['title'] ?>
                </h2>
                <p>
                    <?php echo $value['posttitle'] ?></p>
            </div>
            <?php endforeach; ?>	
    </div>	
</div>
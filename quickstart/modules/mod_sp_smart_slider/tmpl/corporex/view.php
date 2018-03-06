<?php
/**
* @author    JoomShaper http://www.joomshaper.com
* @copyright Copyright (C) 2010 - 2014 JoomShaper
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
*/
// no direct access
defined('_JEXEC') or die;

$helper->addJQuery($document);
$images = array();

ob_start();
?>
jQuery(function($){

$(window).load(function(){
var spCorporexLayout = $('#sp-smart-slider.sp-corporex-layout').find('.sp-slider-items');
$('#sp-smart-slider.sp-corporex-layout').find('.preloader').remove();

spCorporexLayout.spSmartslider({
autoplay  : <?php echo $option['autoplay']?>,
interval  : <?php echo $option['interval']?>,
delay     : 0, 
fullWidth : false
});

$('#sp-smart-slider .controller-prev').on('click', function(){
spCorporexLayout.spSmartslider('prev');
});

$('#sp-smart-slider .controller-next').on('click', function(){
spCorporexLayout.spSmartslider('next');
});
});

});
<?php
$script = ob_get_clean();
$document->addScriptDeclaration($script);

$css = "#sp-smart-slider.sp-corporex-layout,
.sp-corporex-layout .sp-slider-item{
    height: " . (int) trim($option['height']) . "px;
}

@media (max-width:767px) {
#sp-smart-slider.sp-corporex-layout,
    .sp-corporex-layout .sp-slider-item{
        height: " . (int) trim($option['tablet_height']) . "px;
    }
}

@media (max-width:480px) {
#sp-smart-slider.sp-corporex-layout,
    .sp-corporex-layout .sp-slider-item{
        height: " . (int) trim($option['mobile_height']) . "px;
    }
}";

$document->addStyleDeclaration($css);
?>
<div id="sp-smart-slider" class="sp-smart-slider sp-corporex-layout <?php echo $params->get('moduleclass_sfx') ?> ">
    <div class="sp-slider-items">

        <?php $anim = 1; ?>
        <?php foreach($data as $index=>$value) { ?>

        <div class="sp-slider-item <?php echo ($index%2)?'even':'odd'?> <?php echo ($index<=0)?'animate-in':''?> ">
            <div class="sp-slider-image" style="background-image: url('<?php echo JURI::root().$value['image'] ?>');"></div>
            <div class="clearfix"></div>

            <div class="sp-slider-content">
                <?php if( isset($value['pretitle']) and !empty($value['pretitle']) ) { ?>
                <h1 class="sp-pretitle sp-animation-<?php echo $anim; ?>">
                    <?php echo $value['pretitle']; $anim++; ?>
                </h1>
                <?php } ?>

                <?php if(isset($value['showlink']) and $value['showlink']=='yes' ) { ?>
                <a href="<?php echo $value['link']; ?>">
                    <?php } ?>

                    <?php if( isset($value['titletype']) and $value['titletype']=='custom' ) { ?>
                    <?php if( isset($value['customtitle']) and !empty($value['customtitle']) ) { ?>
                    <h3 class="sp-title sp-animation-<?php echo $anim; ?>">
                        <?php echo $value['customtitle']; $anim++; ?>
                    </h3>
                    <?php } ?>
                    <?php } else { ?>
                    <?php if( isset($value['title']) and !empty($value['title']) ) { ?>
                    <h3 class="sp-title sp-animation-<?php echo $anim; ?>">
                        <?php echo $value['title']; $anim++; ?>
                    </h3>
                    <?php } ?>
                    <?php } ?>
                    <?php if(isset($value['showlink']) and $value['showlink']=='yes' ) { ?>
                </a>
                <?php } ?>

                <?php if( isset($value['posttitle']) and !empty($value['posttitle']) ) { ?>
                <h2 class="sp-posttitle sp-animation-<?php echo $anim; ?>">
                    <?php echo $value['posttitle']; $anim++; ?>
                </h2>
                <?php } ?>                          

                <?php if( isset($value['introtext']) and !empty($value['introtext']) ) { ?>
                <p class="sp-introtext sp-animation-<?php echo $anim; ?>">
                    <?php if( isset($value['striphtml']) and  $value['striphtml']=='yes') { ?> 

                    <?php if( isset($value['textlimit']) and $value['textlimit']!='no' ) { ?>
                    <?php echo $helper->textLimit(strip_tags($value['introtext'], $value['allowabletag']),$value['limitcount'], $value['textlimit']); ?>
                    <?php } else { ?>
                    <?php echo strip_tags($value['introtext'], $value['allowabletag']); ?>
                    <?php } ?>

                    <?php } else { ?>

                    <?php if( isset($value['textlimit']) and $value['textlimit']!='no' ) { ?>
                    <?php echo $helper->textLimit($value['introtext'], $value['limitcount'], $value['textlimit']); ?>
                    <?php } else { ?>
                    <?php echo $value['introtext'];?>
                    <?php } ?>
                    <?php } ?>
                </p>
                <?php $anim++; ?>
                <?php } //introtext ?>


                <?php if( isset($value['content']) and !empty($value['content']) ) { ?>
                <div class="sp-full-text sp-animation-<?php echo $anim; ?>">
                    <?php echo $value['content'];  ?>
                </div>
                <?php } $anim++; // full-text  ?>  

                <?php if( isset($value['thumb']) and !empty($value['thumb']) ) { ?>
                <div class="sp-slider-thumb sp-animation-<?php echo $anim; ?>">
                    <img src="<?php echo $value['thumb'];  ?>" alt="" >
                </div>
                <?php } // slider thumb ?>  
                <div class="clearfix"></div>

                
                <?php if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                <div class="btn-more-wrapper">
                    <a href="<?php echo $value['link'] ?>" class="btn-more sp-animation-<?php echo $anim; ?>">
                        <?php echo $value['readmore']; ?>
                    </a>
                    <?php $anim++; ?>
                </div>
                <?php } ?>

                <?php if( isset($value['thumb']) and !empty($value['thumb']) ) { ?>
                <div class="sp-slider-thumb sp-animation-<?php echo $anim; ?>">
                    <img src="<?php echo $value['thumb'];  ?>" alt="" >
                </div>
                <?php } // slider thumb ?>  
                <div class="clearfix"></div>

            </div><!--/.sp-slider-content-->
            <div class="clearfix"></div>
        </div><!--/.slider-item-->
        <?php $anim = 1; ?>
        <?php } ?>
    </div><!--/.slider-items-->

    <!-- Slider navigation -->
    <div class="slider-arrow-nav">
        <a class="controller-prev" href="javascript:;"><span><i class="icon-angle-left"></i></span></a>
        <a class="controller-next" href="javascript:;"><span><i class="icon-angle-right"></i></span></a>
    </div>

    <div class="preloader"></div>
</div>
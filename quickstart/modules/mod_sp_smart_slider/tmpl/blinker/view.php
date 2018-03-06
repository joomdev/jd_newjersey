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


var spBlinkerLayout = $('#sp-smart-slider.sp-blinker-layout').find('.sp-slider-items');

spBlinkerLayout.spBlinkerslider({
    autoplay  : <?php echo $option['autoplay']?>,
    interval  : <?php echo $option['interval']?>,
    delay     : 0, 
    fullWidth : false
});

$('#sp-smart-slider .controller-prev').on('click', function(){
    spBlinkerLayout.spBlinkerslider('prev');
});


$('#sp-smart-slider .controller-next').on('click', function(){
    spBlinkerLayout.spBlinkerslider('next');
});

$(window).load(function(){
    $('.sp-blinker-layout').removeClass('loading');

    $( '.sp-vertical-middle' ).each(function( e ) {
        $(this).css('margin-top',  ($(this).closest('.sp-blinker-layout').height() - $(this).height())/2);
    });
});

});
<?php
$script = ob_get_clean();
$document->addScriptDeclaration($script);


$css = "#sp-smart-slider.sp-blinker-layout,
#sp-smart-slider.sp-blinker-layout .sp-slider-items,
.sp-blinker-layout .sp-slider-item{
height: " . (int) trim($option['height']) . "px;
}

.sp-blinker-layout .sp-slider-items{
    width:" . (int) trim($option['width']) . "px;
}

@media (max-width:767px) {
#sp-smart-slider.sp-blinker-layout,
    #sp-smart-slider.sp-blinker-layout .sp-slider-items,
.sp-blinker-layout .sp-slider-item{
    height: " . (int) trim($option['tablet_height']) . "px;
}

.sp-blinker-layout .sp-slider-items{
    width:" . (int) trim($option['tablet_width']) . "px;
}

}

@media (max-width:480px) {
#sp-smart-slider.sp-blinker-layout,
#sp-smart-slider.sp-blinker-layout .sp-slider-items,
.sp-blinker-layout .sp-slider-item{
    height: " . (int) trim($option['mobile_height']) . "px;
}
}";

$document->addStyleDeclaration($css);
?>
<div id="sp-smart-slider" class="sp-smart-slider sp-blinker-layout loading <?php echo $params->get('moduleclass_sfx') ?> ">

    <div class="sp-slider-items">

        <?php $anim = 1; ?>
        <?php foreach($data as $index=>$value) { ?>

        <div class="sp-slider-item <?php echo ($index%2)?'even':'odd'?> <?php echo ($index<=0)?'animate-in':''?>">
            <div class="sp-slider-content clearfix">

                <div class="sp-stripe-1"></div>
                <div class="sp-stripe-2"></div>
                <div class="sp-stripe-3"></div>

                <img class="sp-slider-image sp-vertical-middle sp-fadeIn" src="<?php echo $value['thumb'];  ?>" alt=" " />

                <div class="sp-vertical-middle">



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
                        <h1 class="sp-title sp-animation-<?php echo $anim; ?>">

                            <?php if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                                <a href="<?php echo $value['link']; ?>">
                            <?php } ?>

                            <?php echo $value['customtitle']; $anim++; ?>

                            <?php if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                                </a>
                            <?php } ?>

                        </h1>
                    <?php } ?>
                <?php } else { ?>
                    <?php if( isset($value['title']) and !empty($value['title']) ) { ?>
                        <h1 class="sp-title sp-animation-<?php echo $anim; ?>">

                            <?php if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                                <a href="<?php echo $value['link']; ?>">
                            <?php } ?>

                            <?php echo $value['title']; $anim++; ?>

                            <?php if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                                </a>
                            <?php } ?>

                        </h1>
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
                <?php $anim++; } // full-text  ?>  
           
             
                <?php if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                    <a href="<?php echo $value['link'] ?>" class="btn-more sp-animation-<?php echo $anim; ?>">
                        <?php echo $value['readmore']; ?>
                    </a>
                    <?php $anim++; ?>
                <?php } ?>

                </div><!--.sp-vertical-middle-->
				
            </div><!--/.sp-slider-content-->
        </div><!--/.slider-item-->
        <?php $anim = 1; ?>
        <?php } ?>
    </div><!--/.slider-items-->

    <!-- Slider navigation -->
    <div class="slider-arrow-nav">
        <a class="controller-prev" href="javascript:;"><span><i class="icon-angle-left"></i></span></a>
        <a class="controller-next" href="javascript:;"><span><i class="icon-angle-right"></i></span></a>
    </div>

</div>
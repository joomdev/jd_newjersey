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

        var sporganiclife = $('#sp-smart-slider.sp-organic-life-layout').find('.sp-slider-items');

        sporganiclife.spSmartslider({
            autoplay  : <?php echo $option['autoplay']?>,
            interval  : <?php echo $option['interval']?>,
            delay     : 0, 
            fullWidth : false
        });

        $('#sp-smart-slider .controller-prev').on('click', function(){
            sporganiclife.spSmartslider('prev');
        });


        $('#sp-smart-slider .controller-next').on('click', function(){
            sporganiclife.spSmartslider('next');
        });

        $('.sp-organic-life-layout').removeClass('loading');
    });

});

<?php
$script = ob_get_clean();
$document->addScriptDeclaration($script);


$css = "#sp-smart-slider.sp-organic-life-layout,
#sp-smart-slider.sp-organic-life-layout.loading:before{
    background:" . trim($option['bgcolor']) . "
}

#sp-smart-slider.sp-organic-life-layout,
.sp-organic-life-layout .sp-slider-item{
height: " . (int) trim($option['height']) . "px;

}

.sp-organic-life-layout .sp-slider-items{
    /*width:" . (int) trim($option['width']) . "px;*/
}

@media (max-width:767px) {
#sp-smart-slider.sp-organic-life-layout,
.sp-organic-life-layout .sp-slider-item{
    height: " . (int) trim($option['tablet_height']) . "px;
}

.sp-organic-life-layout .sp-slider-items{
    /*width:" . (int) trim($option['tablet_width']) . "px;*/
}

}

@media (max-width:480px) {
#sp-smart-slider.sp-organic-life-layout,
.sp-organic-life-layout .sp-slider-item{
    height: " . (int) trim($option['mobile_height']) . "px;
}
}";

$document->addStyleDeclaration($css);
?>

</style>
<div id="sp-smart-slider" class="sp-smart-slider sp-organic-life-layout loading <?php echo $params->get('moduleclass_sfx') ?> ">
    <div class="organic-life-slider">
        <div class="sp-slider-items">
            <?php $anim = 1; ?>
            <?php foreach($data as $index=>$value) { ?>

            <div class="sp-slider-item <?php echo ($index%2)?'even':'odd'?> <?php echo ($index<=0)?'animate-in':''?>" style="background: url(<?php echo $value['image'] ?>) no-repeat center; background-size:cover;">
                <div class="container">
                    <div class="sp-slider-content clearfix">
                        <?php if ($value['thumb']) {?>
                        <img class="sp-slider-image sp-vertical-middle sp-fadeInLeft" src="<?php echo $value['thumb'];  ?>" alt=" " />
                        <?php } ?>
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
                                <h1 class="sp-title animated fadeInUp sp-animation-<?php echo $anim; ?>">
                                    <?php echo $value['customtitle']; $anim++; ?>
                                </h1>
                            <?php } ?>
                        <?php } else { ?>
                            <?php if( isset($value['title']) and !empty($value['title']) ) { ?>
                                <?php if ($value['link']){?>
                                    <a target="_blank" href="<?php echo $value['link']; ?>">
                                    <h1 class="sp-title animated fadeInUp sp-animation-<?php echo $anim; ?>">
                                        <?php echo $value['title']; $anim++; ?>
                                    </h1>
                                    </a>
                                <?php } else{?>
                                    <h1 class="sp-title animated fadeInUp sp-animation-<?php echo $anim; ?>">
                                        <?php echo $value['title']; $anim++; ?>
                                    </h1>
                                <?php } ?>                                
                            <?php } ?>
                        <?php } ?>
                        <?php if(isset($value['showlink']) and $value['showlink']=='yes' ) { ?>
                            </a>
                        <?php } ?>

                        <?php if( isset($value['posttitle']) and !empty($value['posttitle']) ) { ?>
                            <h2 class="sp-posttitle animated-two fadeInUp sp-animation-<?php echo $anim; ?>">
                                <?php echo $value['posttitle']; $anim++; ?>
                            </h2>
                        <?php } ?>                          

                        <?php if( isset($value['introtext']) and !empty($value['introtext']) ) { ?>
                            <p class="sp-introtext animated-three fadeInUp sp-animation-<?php echo $anim; ?>">
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
                   
                     
                        <?php
                            //if button have not link
                            $value['link'] = $value['link'] ?: 'javascript:void(0);';

                        if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                        <div class="read-more-wrapper animated-four fadeInUp">
                            <a href="<?php echo $value['link']; ?>" class="btn btn-primary sp-animation-<?php echo $anim; ?>">
                                <?php echo $value['readmore']; ?>
                            </a>
                        </div>
                        <?php $anim++; ?>
                        <?php } ?>

                        </div><!--.sp-vertical-middle-->
        				
                    </div><!--/.sp-slider-content-->
                </div><!--/.slider-item-->
            </div> <!-- /.container -->
            <?php $anim = 1; ?>
            <?php } ?>
        </div><!--/.slider-items-->
    </div> <!-- /.organic-life-slider -->
    <!-- Slider navigation -->
    <div class="slider-arrow-nav">
        <a class="controller-prev" href="javascript:;"><span><i class="icon-angle-left"></i></span></a>
        <a class="controller-next" href="javascript:;"><span><i class="icon-angle-right"></i></span></a>
    </div>

</div>
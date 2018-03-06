<?php
/**
* @author    JoomShaper http://www.joomshaper.com
* @copyright Copyright (C) 2010 - 2013 JoomShaper
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
*/
// no direct access
defined('_JEXEC') or die;

$helper->addJQuery($document);
$images = array();

ob_start();
?> 
jQuery(function($){


$(window).on('scroll', function(){
    

});

    var spUniversityLayout  = $('#sp-smart-slider.sp-university-layout');

    spUniversityLayout.find('.sp-slider-items').spSmartslider({
        autoplay  : <?php echo $option['autoplay']?>,
        interval  : <?php echo $option['interval']?>,
        delay     : 0, 
        fullWidth : false,
        preloadImages:[<?php 
        foreach($data as $index=>$value) $images[] = "'".JURI::root().$value['image']."'";
            echo implode(',',$images);
        ?>]
    });

    $('.sp-slider-controllers > .controller-prev').on('click', function(){
        spUniversityLayout.find('.sp-slider-items').spSmartslider('prev');
        return false;
    });

    $('.sp-slider-controllers > .controller-next').on('click', function(){
        spUniversityLayout.find('.sp-slider-items').spSmartslider('next');
        return false;
    });

    $( '.sp-slider-content' ).each(function( e ) {
        $(this).css('margin-top',  (spUniversityLayout.height() - $(this).height())/2);
    });

    $(window).resize(function(){
        $( '.sp-slider-content' ).each(function( e ) {
            $(this).css('margin-top',  (spUniversityLayout.height() - $(this).height())/2);
        });
    });

});
<?php
$script = ob_get_clean();
$document->addScriptDeclaration($script);

$css = "#sp-smart-slider.sp-university-layout{
height: " . (int) trim($option['height']) . "px;
}

@media (max-width:767px) {
#sp-smart-slider.sp-university-layout{
    height: " . (int) trim($option['tablet_height']) . "px;
}
}

@media (max-width:480px) {
#sp-smart-slider.sp-university-layout{
    height: " . (int) trim($option['mobile_height']) . "px;
}
}";

$document->addStyleDeclaration($css);
?>
<div id="sp-smart-slider" class="sp-smart-slider sp-university-layout <?php echo $params->get('moduleclass_sfx') ?> ">

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
                        <h1 class="sp-title sp-animation-<?php echo $anim; ?>">
                            <?php echo $value['customtitle']; $anim++; ?>
                        </h1>
                    <?php } ?>
                <?php } else { ?>
                    <?php if( isset($value['title']) and !empty($value['title']) ) { ?>
                        <h1 class="sp-title sp-animation-<?php echo $anim; ?>">
                            <?php echo $value['title']; $anim++; ?>
                        </h1>
                    <?php } ?>
                <?php } ?>
                <?php if(isset($value['showlink']) and $value['showlink']=='yes' ) { ?>
                    </a>
                <?php } ?>

                <?php if( isset($value['posttitle']) and !empty($value['posttitle']) ) { ?>
                    <h3 class="sp-posttitle sp-animation-<?php echo $anim; ?>">
                        <?php echo $value['posttitle']; $anim++; ?>
                    </h3>
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

                <?php if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                    <a href="<?php echo $value['link'] ?>" class="btn-more sp-animation-<?php echo $anim; ?>">
                        <?php echo $value['readmore']; ?>
                    </a>
                    <?php $anim++; ?>
                <?php } ?>  
            </div><!--/.sp-slider-content-->
            <div class="clearfix"></div>
        </div><!--/.slider-item-->
        <?php $anim = 1; ?>
        <?php } ?>
    </div><!--/.slider-items-->

    <div class="sp-preloader">
        <i class="icon-spinner icon-spin"></i>
    </div><!--/.sp-preloader-->

    <div class="sp-slider-controllers">
        <a href="#" class="controller-prev"><span>&lsaquo;</span></a>
        <a  href="#" class="controller-next"><span>&rsaquo;</span></a>
    </div><!--/.sp-slider-controllers-->
</div>
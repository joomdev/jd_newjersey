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


var spiStoreiiLayout = $('#sp-smart-slider.sp-iStoreii-layout').find('.sp-slider-items');

spiStoreiiLayout.spSmartslider({
    autoplay  : <?php echo $option['autoplay']?>,
    interval  : <?php echo $option['interval']?>,
    delay     : 0, 
    fullWidth : false
});

$('#sp-smart-slider .controller-prev').on('click', function(){
    event.stopPropagation();
    spiStoreiiLayout.spSmartslider('prev');
});


$('#sp-smart-slider .controller-next').on('click', function(){
    event.stopPropagation();
    spiStoreiiLayout.spSmartslider('next');
});

var OldTime;
$('.sp-iStoreii-layout .slider-list > ul li').on('click',function(event){
    event.stopPropagation();
    if( OldTime ){
        var DiffTime  = event.timeStamp-OldTime;
        if( DiffTime < 2000){
            return false;
        }
    } 

    OldTime=event.timeStamp;

    spiStoreiiLayout.spSmartslider('goTo', $(this).index() );
        $(this).parent().find('>li').removeClass('active');
        $(this).addClass('active');
    });

    spiStoreiiLayout.spSmartslider('onSlide', function(index){
        $('.sp-iStoreii-layout .slider-list > ul li').removeClass('active');
        $('.sp-iStoreii-layout .slider-list > ul li').get(index).addClass('active');
    });

    $(window).load(function(){
        $('.sp-iStoreii-layout').removeClass('loading');

        $( '.sp-vertical-middle' ).each(function( e ) {
            $(this).css('margin-top',  ($(this).closest('.sp-iStoreii-layout').height() - $(this).height())/2);
        });
    });

});
<?php
$script = ob_get_clean();
$document->addScriptDeclaration($script);


$css = "#sp-smart-slider.sp-iStoreii-layout,
#sp-smart-slider.sp-iStoreii-layout.loading:before{
    background:" . trim($option['bgcolor']) . "
}

#sp-smart-slider.sp-iStoreii-layout,
.sp-iStoreii-layout .sp-slider-item{
height: " . (int) trim($option['height']) . "px;

}

.sp-iStoreii-layout .sp-slider-items{
    /*width:" . (int) trim($option['width']) . "px;*/
}

@media (max-width:767px) {
#sp-smart-slider.sp-iStoreii-layout,
.sp-iStoreii-layout .sp-slider-item{
    height: " . (int) trim($option['tablet_height']) . "px;
}

.sp-iStoreii-layout .sp-slider-items{
    /*width:" . (int) trim($option['tablet_width']) . "px;*/
}

}

@media (max-width:480px) {
#sp-smart-slider.sp-iStoreii-layout,
.sp-iStoreii-layout .sp-slider-item{
    height: " . (int) trim($option['mobile_height']) . "px;
}
}";

$document->addStyleDeclaration($css);
?>

</style>
<div id="sp-smart-slider" class="sp-smart-slider sp-iStoreii-layout loading <?php echo $params->get('moduleclass_sfx') ?> ">
    <div class="istoreii-slider">
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
                                <h1 class="sp-title sp-animation-<?php echo $anim; ?>">
                                    <?php echo $value['customtitle']; $anim++; ?>
                                </h1>
                            <?php } ?>
                        <?php } else { ?>
                            <?php if( isset($value['title']) and !empty($value['title']) ) { ?>
                                <?php if ($value['link']){?>
                                    <a target="_blank" href="<?php echo $value['link']; ?>">
                                    <h1 class="sp-title sp-animation-<?php echo $anim; ?>">
                                        <?php echo $value['title']; $anim++; ?>
                                    </h1>
                                    </a>
                                <?php } else{?>
                                    <h1 class="sp-title sp-animation-<?php echo $anim; ?>">
                                        <?php echo $value['title']; $anim++; ?>
                                    </h1>
                                <?php } ?>                                
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
                   
                     
                        <?php
                            //if button have not link
                            $value['link'] = $value['link'] ?: 'javascript:void(0);';

                        if( isset($value['readmore']) and !empty($value['readmore']) ) { ?>
                        <div class="read-more-wrapper">
                            <a href="<?php echo $value['link']; ?>" class=" sp-animation-<?php echo $anim; ?>">
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
    </div> <!-- /.istoreii-slider -->
    <!-- Slider navigation -->
    <div class="slider-arrow-nav">
        <a class="controller-prev" href="javascript:;"><span><i class="icon-long-arrow-left"></i></span></a>
        <a class="controller-next" href="javascript:;"><span><i class="icon-long-arrow-right"></i></span></a>
    </div>

     <!-- item list -->
    <div class="slider-list">
        <?php $count = count($data); ?>
        <ul class="row-fluid container" style="margin: 0 auto;">
            <?php foreach($data as $i=>$v){ ?>
            <li class="<?php echo ($i==0)?'active':''; ?> <?php echo 'span'.round(12/$count);?>">
                <div class="slider-list-content-wrapper">
                    <a href="javascript:;">
                        <?php
                        if( isset($value['pretitle']) and !empty($v['pretitle']) ) echo '<span>' . $v['pretitle'] . '</span>';
                        if( isset($value['title']) and !empty($v['title']) ) echo '<span>' . $v['title'] . '</span>';
                        ?>
                    </a>
                </div> <!-- /.slider-list-content-wrapper -->
            </li>
            <?php } ?>
        <ul>
    </div>


</div>
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
    $('#sp-smart-slider<?php echo $module->id?> .appico-slider').appicoSlider({
    autoplay  : <?php echo $option['autoplay']?>,
    interval  : <?php echo $option['interval']?>,
    fullWidth : false,
    rWidth : 135,
    rHeight : 58,
    preloadImages:[<?php
    foreach($data as $index=>$value) $images[] = "'".JURI::root().$value['image']."'";
    echo implode(',',$images);
    ?>],
});


var OldTime;
$('.layout-appico .slider-controllers > ul li').on('click',function(event){
event.stopPropagation();
if( OldTime ){
var DiffTime  = event.timeStamp-OldTime;
if( DiffTime < 500){
return false;
}
} 

OldTime=event.timeStamp;

$('#sp-smart-slider<?php echo $module->id?> .appico-slider').appicoSlider('goTo', $(this).index() );
$(this).parent().find('>li').removeClass('active');
$(this).addClass('active');

});

$('#sp-smart-slider<?php echo $module->id?> .appico-slider').appicoSlider('onSlide', function(index){
$('.layout-appico .slider-controllers > ul li').removeClass('active');
$('.layout-appico .slider-controllers > ul li').get(index).addClass('active');
});

$('#sp-smart-slider<?php echo $module->id?> .controller-prev').on('click', function(){
    
$('#sp-smart-slider<?php echo $module->id?> .appico-slider').appicoSlider('prev');
    
});


$('#sp-smart-slider<?php echo $module->id?> .controller-next').on('click', function(){
    
$('#sp-smart-slider<?php echo $module->id?> .appico-slider').appicoSlider('next');
    
});


});
<?php
$script = ob_get_clean();
$document->addScriptDeclaration($script);

$css = "#sp-smart-slider{$module->id} .appico-slider, #sp-smart-slider{$module->id} .appico-slider .slider-item{
    background-color: " .$option['background'] . ";
}
#sp-smart-slider{$module->id} .appico-slider{
    height: " . (int) trim($option['height']) . "px;
}

@media (max-width:767px) {
    #sp-smart-slider{$module->id} .appico-slider{
        height: " . (int) trim($option['tablet_height']) . "px;
    }
}

@media (max-width:480px) {
    #sp-smart-slider{$module->id} .appico-slider{
        height: " . (int) trim($option['mobile_height']) . "px;
    }
}

";

$document->addStyleDeclaration($css);

?>
<div id="sp-smart-slider<?php echo $module->id; ?>" class="sp-smart-slider layout-appico <?php echo $params->get('moduleclass_sfx') ?> ">
    

    <div class="slider-controllers">
        <?php $count = count($data); ?>
        <ul>
            <?php foreach($data as $index=>$val){ ?>
            <li class="<?php echo ($i==0)?'active':'' ?>">
                <a href="javascript:;">
                    <span>
                        <img class="hasTip" src="<?php echo JURI::root().$val['thumb'] ?>" alt="<?php  echo $val['title']?>" title="<?php  echo $val['pretitle']?>" />
                    </span>
                </a>
            </li>
            <?php } ?>
        <ul>
    </div>
    <!-- Slider navigation -->
    <div class="sslider-arrow-nav">
        <a class="controller-prev" href="javascript:;"><span><i class="icon-angle-up"></i></span></a>
        <a class="controller-next" href="javascript:;"><span><i class="icon-angle-down"></i></span></a>
    </div>



    <!-- Carousel items -->
    <div class="appico-slider">
        <?php foreach($data as $index=>$value):?>

        <div class="slider-item <?php echo ($index%2)?'even':'odd'?> <?php echo ($index<=0)?'animate-in':''?> ">
            <div class="slider-item-inner">
                <div class="slider-content">
                    <div class="slider-title">
                        <?php
                                // adding pretitle
                        //if( isset($value['pretitle']) and !empty($value['pretitle']) ) echo '<p class="sp-smart-pretitle">' . $value['pretitle'] . '</p>';

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

                        ?>                                
                    </div>

                    <div class="slider-text hidden-phone">
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
                                if( isset($value['introtext']) and !empty($value['introtext']) ) echo $value['introtext'];
                            }
                        }
                        ?>
                    </div>

                    <?php
                    if( isset($value['readmore']) and !empty($value['readmore']) ){
                        ?>
                        <div class="slider-button">

                            <a href="<?php echo $value['link'] ?>" class="btn btn-primary btn-large appico-more">
                                <?php echo $value['readmore'] ?>
                            </a>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div  class="slider-image">
                    <img src="<?php echo JURI::root().$value['image'] ?>" alt="<?php  echo $value['title']?>" title="<?php  echo strip_tags($value['title'])?>" />
                </div>

                <div class="clearfix"></div>
            </div>
        </div>

    <?php endforeach; ?>
</div>
<div class="sp-preloader">
    <i></i>
    <i></i>
</div>

</div>

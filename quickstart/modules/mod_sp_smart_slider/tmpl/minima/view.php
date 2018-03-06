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
$('#sp-smart-slider<?php echo $module->id?> .minima-slider').minimaSlider({
autoplay  : <?php echo $option['autoplay']?>,
duration  : <?php echo $option['interval']?>,
fullWidth : false,
rWidth : 135,
rHeight : 58,
preloadImages:[<?php
    foreach($data as $index=>$value) $images[] = "'".JURI::root().$value['image']."'";
    echo implode(',',$images);
?>],
});


$('.layout-minima .slider-controllers > ul li').on('click',function(event){
event.stopPropagation();
$('#sp-smart-slider<?php echo $module->id?> .minima-slider').minimaSlider('goTo', $(this).index() );
$(this).parent().find('>li').removeClass('active');
$(this).addClass('active');

});

$('#sp-smart-slider<?php echo $module->id?> .minima-slider').minimaSlider('onSlide', function(index){
$('.layout-minima .slider-controllers > ul li').removeClass('active');
$('.layout-minima .slider-controllers > ul li').get(index).addClass('active');
});



});
<?php
    $script = ob_get_clean();
    $document->addScriptDeclaration($script);
?>
<div id="sp-smart-slider<?php echo $module->id; ?>" class="sp-smart-slider layout-minima <?php echo $params->get('moduleclass_sfx') ?> ">
    <!-- Carousel items -->
    <div class="minima-slider">
        <?php foreach($data as $index=>$value):?>

            <div class="slider-item">
                <div class="container">
                    <div class="slider-content">
                        <div class="slider-title">
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
                                <a href="<?php echo $value['link'] ?>" class="btn btn-primary btn-large minima-more"><?php echo $value['readmore'] ?></a>
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
        <i class="icon-cog icon-spin"></i>
        <i class="icon-cog icon-spin"></i>
        <i class="icon-cog icon-spin"></i>
    </div>
    <div class="slider-controllers">
        <ul>
        <?php foreach($data as $i=>$v){ ?>
            <li class="<?php echo ($i==0)?'active':'' ?>">
                <a href="javascript:;"></a>
            </li>
            <?php } ?>
        <ul>
    </div>
</div>

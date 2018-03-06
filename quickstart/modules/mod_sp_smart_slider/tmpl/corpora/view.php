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
$('#sp-smart-slider<?php echo $module->id?> .corpora-slider').corporaSlider({
autoplay  : <?php echo $option['autoplay']?>,
interval  : <?php echo $option['interval']?>,
fullWidth : true,
rWidth : 135,
rHeight : 58,
preloadImages:[<?php
    foreach($data as $index=>$value) $images[] = "'".JURI::root().$value['image']."'";
    echo implode(',',$images);
?>],
});
$('.layout-corpora .controller-next').on('click',function(event){
event.stopPropagation();
$('#sp-smart-slider<?php echo $module->id?> .corpora-slider').corporaSlider('next');
});
$('.layout-corpora .controller-prev').on('click',function(event){
event.stopPropagation();
$('#sp-smart-slider<?php echo $module->id?> .corpora-slider').corporaSlider('prev');
});


$(window).on('resize',function(event){
$('#sp-smart-slider<?php echo $module->id?> .corpora-slider').corporaSlider('resize');

});

});
<?php
    $script = ob_get_clean();
    $document->addScriptDeclaration($script);



?>
<div id="sp-smart-slider<?php echo $module->id; ?>" class="sp-smart-slider layout-corpora <?php echo $params->get('moduleclass_sfx') ?> ">
    <!-- Carousel items -->
    <div class="corpora-slider">
        <?php foreach($data as $index=>$value): 
		
		
		?>
            <div class="slider-item" style="background: url(<?php echo JURI::root().$value['image'] ?>) no-repeat center; background-size:cover;">
<?php 
//Linked title
                                if(isset($value['showlink']) and $value['showlink']=='yes' ) echo '<a href="' . $value['link'] . '">';

                                if($value['source']=='text' and !empty($value['link'])) echo '<a href="' . $value['link'] . '">';
?>
                <div class="slider-content-wrapper">
                    <div class="container">
                        <div class="slider-title">
                            <?php
                                // adding pretitle
                                if( isset($value['pretitle']) and !empty($value['pretitle']) ) echo '<p class="sp-smart-pretitle">' . $value['pretitle'] . '</p>';

                                

                                // check title type
                                if( isset($value['titletype']) and $value['titletype']=='custom' )
                                {
                                    // add custom title
                                    if( isset($value['customtitle']) and !empty($value['customtitle']) ) echo '<h1 class="sp-smart-title">' . $value['customtitle'] . '</h1>';
                                } else {
                                    // add title
                                    if( isset($value['title']) and !empty($value['title']) ) echo '<h1 class="sp-smart-title">' . $value['title'] . '</h1>';
                                }

                                

                                // add post title
                                if( isset($value['posttitle']) and !empty($value['posttitle']) ) echo '<p class="sp-smart-posttitle">' . $value['posttitle'] . '</p>';
                            ?>                                
                        </div>

                        <div class="slider-introtext">
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
                    </div>
                </div>
			<?php
			//Linked title    
			if(isset($value['showlink']) and $value['showlink']=='yes' ) echo '</a>';

			if($value['source']=='text' and !empty($value['link'])) echo '</a>';
			
			?>
            </div>
            <?php 
			
		
			endforeach; ?>
    </div>
    <div class="sp-preloader"><i class="icon-cog icon-spin"></i> <i class="icon-cog icon-spin"></i> <i class="icon-cog icon-spin"></i></div>
    <div class="slider-controllers">
        <a class="controller-prev"><span>&lsaquo;</span></a>
        <a class="controller-next"><span>&rsaquo;</span></a>
    </div>
</div>
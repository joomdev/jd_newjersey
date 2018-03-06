<?php
/**
* @author    JoomShaper http://www.joomshaper.com
* @copyright Copyright (C) 2010 - 2013 JoomShaper
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
*/
// no direct access
defined('_JEXEC') or die;

$helper->addJQuery($document);
$document->addScriptDeclaration("
    jQuery(document).ready(function($) {
        $('#sp-slider-{$module_id}').nivoSlider({
            directionNav: true,
            controlNav: false,
            controlNavThumbs: false,
            pauseTime: {$option['pauseTime']}
        });
        $('#previousButton').live('click', function (e) {
           $('.nivo-directionNav').find('.nivo-prevNav').click();
        });
        $('#nextButton').live('click', function (e) {
           $('.nivo-directionNav').find('.nivo-nextNav').click();
        });
});
");
?>

<div class="<?php echo $params->get('moduleclass_sfx') ?>  slider-wrapper">
    <div id="sp-slider-<?php echo $module_id ?>" class="nivoSlider">
        <?php
        foreach($data as $index=>$value) { ?>
        <?php if( isset($value['showlink']) and $value['showlink']=='yes' ) { ?>
        <a href="<?php echo $value['link'] ?>">
            <?php } ?>
            <img src="<?php echo JURI::root().$value['image'] ?>" data-thumb="<?php echo JURI::root().$value['thumb'] ?>" alt="<?php  echo $value['title']?>" title="#sp-smart-caption-<?php echo $index ?>" />
            <?php if(isset($value['showlink']) and $value['showlink']=='yes' ) { ?>
        </a>
        <?php } ?>
        <?php } ?>
    </div><!--/.nivoSlider-->
</div><!--/.slider-wrapper-->

<?php foreach( $data as $key=>$val ) { ?>
    <div id="sp-smart-caption-<?php echo $key ?>" class="nivo-html-caption">
        <div class="container">
        <div class="caption-inner">

            <div class="slider-nav">
                <a id="previousButton" href="#"><i class="icon-long-arrow-left"></i></a>
                <span class="slide-counter"><?php echo sprintf("%02s", $key+1); ?> / <sup><?php echo sprintf("%02s", count($data)); ?></sup></span>
                <a id="nextButton" href="#"><i class="icon-long-arrow-right"></i></a>
            </div>

        <?php
        // adding pretitle
        if( isset($val['pretitle']) and !empty($val['pretitle']) ) echo '<p class="nivo-pretitle">' . $val['pretitle'] . '</p>';

        //Linked title
        if(isset($value['showlink']) and $value['showlink']=='yes' ) echo '<a href="' . $value['link'] . '">';

        // check title type
        if( isset($val['titletype']) and $val['titletype']=='custom' )
        {
        // add custom title
            if( isset($val['customtitle']) and !empty($val['customtitle']) ) echo '<h1 class="nivo-title">' . $val['customtitle'] . '</h1>';
        } else {
        // add title
            if( isset($val['title']) and !empty($val['title']) ) echo '<h1 class="nivo-title">' . $val['title'] . '</h1>';  
        }

        //Linked title	
        if(isset($value['showlink']) and $value['showlink']=='yes' ) echo '</a>';

        // add post title
        if( isset($val['posttitle']) and !empty($val['posttitle']) ) echo '<p class="nivo-posttitle">' . $val['posttitle'] . '</p>';

        echo '<div class="nivo-introtext">';

        // is strip html
        if( isset($val['striphtml']) and  $val['striphtml']=='yes')
        { 
            if( isset($val['textlimit']) and $val['textlimit']!='no' )
            {
                if( isset($val['introtext']) ) echo $helper->textLimit(strip_tags($val['introtext'], $val['allowabletag']),$val['limitcount'], $val['textlimit']) ;
            } else {
                if( isset($val['introtext']) ) echo strip_tags($val['introtext'], $val['allowabletag']); 
            }

        // strip intro text html  
        } else {

            // add intro text
            if( isset($val['textlimit']) and $val['textlimit']!='no' )
            {
                if( isset($val['introtext']) ) echo $helper->textLimit($val['introtext'], $val['limitcount'], $val['textlimit']);
            } else {
                if( isset($val['introtext']) ) echo $val['introtext'];
            }
        }

        echo '</div>'; ?>

        <?php if( isset($val['readmore']) and !empty($val['readmore']) ): ?>
        <a href="<?php echo $val['link'] ?>" class="nivo-readmore"><?php echo $val['readmore'] ?></a>
    <?php endif; ?>
</div>
</div>
</div><!--/.nivo-caption-->
<?php }
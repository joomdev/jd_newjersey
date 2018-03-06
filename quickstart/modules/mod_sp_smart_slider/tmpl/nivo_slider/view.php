<?php
    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2013 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */

    // no direct access
    defined('_JEXEC') or die;
	
    $helper->addJQuery($document);

    $document->addStylesheet(JURI::base(true) . '/modules/'. $module_name.'/tmpl/'.$style.'/themes/'.$option['theme'].'/'.$option['theme'].'.css');

    $document->addScriptDeclaration("
        
        jQuery(document).ready(function($) {
        $('#sp-slider-{$module_id}').nivoSlider({
        effect: '{$option['effect']}', // Specify sets like: 'fold,fade,sliceDown'
        slices: {$option['slices']}, // For slice animations
        boxCols: {$option['boxCols']}, // For box animations
        boxRows: {$option['boxRows']}, // For box animations
        animSpeed: {$option['animSpeed']}, // Slide transition speed
        pauseTime: {$option['pauseTime']}, // How long each slide will show
        startSlide: {$option['startSlide']}, // Set starting Slide (0 index)
        directionNav: {$option['directionNav']}, // Next & Prev navigation
        controlNav: {$option['controlNav']}, // 1,2,3... navigation
        controlNavThumbs: {$option['controlNavThumbs']}, // Use thumbnails for Control Nav
        pauseOnHover: {$option['pauseOnHover']}, // Stop animation while hovering
        manualAdvance: {$option['manualAdvance']}, // Force manual transitions
        prevText: '{$option['prevText']}', // Prev directionNav text
        nextText: '{$option['nextText']}', // Next directionNav text
        randomStart: {$option['randomStart']}, // Start on a random slide
        });
        });

        ");
?>

<div class="<?php echo $params->get('moduleclass_sfx') ?>  slider-wrapper theme-<?php echo $option['theme'] ?>">
    <div id="sp-slider-<?php echo $module_id ?>" class="nivoSlider">
        <?php
            foreach($data as $index=>$value)
            {
                if( isset($value['showlink']) and $value['showlink']=='yes' ): ?>
                <a href="<?php echo $value['link'] ?>">
                    <?php endif; ?>
                <img src="<?php echo JURI::root().$value['image'] ?>" data-thumb="<?php echo JURI::root().$value['thumb'] ?>" alt="<?php  echo $value['title']?>" title="#sp-smart-caption-<?php echo $index ?>" />
                <?php if(isset($value['showlink']) and $value['showlink']=='yes' ): ?>
                </a>
                <?php endif; ?>
            <?php
            }
        ?>
    </div>
</div>
<?php
    foreach($data as $key=>$val)
    {
    ?>
    <div id="sp-smart-caption-<?php echo $key ?>" class="nivo-html-caption">
        <?php
            // adding pretitle
            if( isset($val['pretitle']) and !empty($val['pretitle']) ) echo '<p class="nivo-pretitle">' . $val['pretitle'] . '</p>';
            
			//Linked title
			if(isset($val['showlink']) and $val['showlink']=='yes' ) echo '<a href="' . $val['link'] . '">';
			
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
			if(isset($val['showlink']) and $val['showlink']=='yes' ) echo '</a>';


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
			
			echo '</div>';	

            // add readmore text
            if( isset($val['readmore']) and !empty($val['readmore']) ):
            ?>
            <a href="<?php echo $val['link'] ?>" class="nivo-readmore"><?php echo $val['readmore'] ?></a>
            <?php
                endif;
        ?>
    </div>
    <?php
    }

<?php
    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2014 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */
    // no direct access
    defined('_JEXEC') or die;

    $helper->addJQuery($document);




    if( isset($option[ 'load_carousel' ]) and $option[ 'load_carousel' ]=='1' ){


    }



    //print_r($option);

    ?>

    <div id="sp-smart-slider<?php echo $module->id; ?>" class="sp-smart-slider carousel slide <?php echo $params->get('moduleclass_sfx') ?> ">
        <!-- Carousel items -->

        <div class="carousel-inner">
            <?php foreach($data as $index=>$value):?>

            <div class="item<?php echo ($index==0) ? ' active': ''; ?>">
                <div class="container">
                    <div class="row-fluid">

                        <?php $content_animation = 'fadeInLeft'; ?>

                        <?php
                        if( isset($option[ 'image_position' ]) and $option[ 'image_position' ]=='left' ){
                            ?>

                            <div class="span6">

                                <div  class="slider-image animation fadeInLeft">
                                    <img src="<?php echo JURI::root().$value['image'] ?>" alt="<?php  echo $value['title']?>" title="<?php  echo strip_tags($value['title'])?>" />
                                </div>

                            </div>
                            <?php $content_animation = 'fadeInRight'; ?>
                            <?php }  ?>


                            <div class="span6">
                                <div class="slider-content animation <?php echo $content_animation; ?>">
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

                                    <div class="slider-text">
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
                                            <a href="<?php echo $value['link'] ?>" class="btn btn-primary btn-large appico-content-more"><?php echo $value['readmore'] ?></a>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>

                            <?php
                            if( isset($option[ 'image_position' ]) and $option[ 'image_position' ]=='right' ){
                                ?>

                                <div class="span6">

                                    <div  class="slider-image animation fadeInRight">
                                        <img src="<?php echo JURI::root().$value['image'] ?>" alt="<?php  echo $value['title']?>" title="<?php  echo strip_tags($value['title'])?>" />
                                    </div>

                                </div>
                                <?php }  ?>


                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

            <?php if( isset($option['show_controllers']) and $option['show_controllers']=='1') { ?>
            <!-- Carousel Indicator -->
            <ol class="carousel-indicators">
                <?php foreach($data as $index=>$value):?>
                <li data-target="#sp-smart-slider<?php echo $module->id; ?>" data-slide-to="<?php echo $index; ?>" class="<?php echo ($index==0) ? 'active': ''; ?>"></li>
            <?php endforeach; ?>
        </ol>
        <?php } ?>

        <!-- Carousel nav -->
        <?php if( isset($option['arrow']) and $option['arrow']=='1') { ?>
        <a class="carousel-control left" href="#sp-smart-slider<?php echo $module->id; ?>" data-slide="prev">&lsaquo;</a>
        <a class="carousel-control right" href="#sp-smart-slider<?php echo $module->id; ?>" data-slide="next">&rsaquo;</a>
        <?php } ?>
    </div>
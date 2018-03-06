<?php
	/*
	# mod_sp_tweet - Twitter Module by JoomShaper.com
	# -----------------------------------------------
	# Author    JoomShaper http://www.joomshaper.com
	# Copyright (C) 2010 - 2014 JoomShaper.com. All Rights Reserved.
	# license - GNU/GPL V2 or Later
	# Websites: http://www.joomshaper.com
	*/

    // no direct access
    defined('_JEXEC') or die('Restricted access');
    
	//Parameters
    $style					= ($params->get('animation', 'none')=='none' ) ? $params->get ('layout_style','default') : 'default';
    $follow_us				= $params->get ('follow_us',1);
    $tweets					= $params->get ('tweets',4);
    $avatar					= $params->get ('avatar',1);
    $avatar_width			= $params->get ('avatar_width',48);
    $linked_avatar			= $params->get ('linked_avatar',1);
    $show_user				= $params->get ('show_user',1);
    $linked_search			= $params->get ('linked_search',1);
    $linked_mention			= $params->get ('linked_mention',1);
    $tweet_time				= $params->get ('tweet_time',1);
    $tweet_time_linked		= $params->get ('tweet_time_linked',1);
    $tweet_src				= $params->get ('tweet_src',1);
    $target                 = $params->get ('target','_blank');
    $moduleName             = basename(dirname(__FILE__));
    $moduleID               = $module->id;
    $document               = JFactory::getDocument();
    $cssFile                = JPATH_THEMES. '/'.$document->template.'/css/'.$moduleName.'.css';

    // Include helper.php
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');
    $helper = new modSPTwitter($params, $moduleID);
    $data= $helper->tweets();

    if(JVERSION<3)
    {
        JHtml::_('behavior.framework');
    } 
    else
    {
        JHtml::_('jquery.framework');
    }

    if (is_array($data)) {
        if(file_exists($cssFile)) {
            $document->addStylesheet(JURI::base(true) . '/templates/'.$document->template.'/css/'. $moduleName.'.'. $style . '.css');
        } else {
            $document->addStylesheet(JURI::base(true) . '/modules/'.$moduleName.'/assets/css/' . $moduleName.'.'. $style . '.css');
        }

        if($params->get('animation')!=='none') {

            if(JVERSION<3)
            {
                $document->addScript(JURI::base(true) . '/modules/'.$moduleName.'/assets/js/mod_sp_tweet.js');
            } 
            else
            {
                $document->addScript(JURI::base(true) . '/modules/'.$moduleName.'/assets/js/mod_sp_tweet_jquery.js');  
            }


            $css = '.sp-tweet div.sp-tweet-item {'
            . 'position: absolute;'
            . 'visibility: hidden;'
            . '}';
            if(JVERSION<3)
            {
                $document->addStyleDeclaration( $css );
            }  
        }
        echo '<div id="sp-tweet-id'. $moduleID.'">';
        require(JModuleHelper::getLayoutPath($moduleName,$style)); 
        echo '</div>';
    }
    
    if ( $params->get('animation', 'none')!=='none' ) { ?>
    <?php
    if(JVERSION<3)
    {
        ?>
        <script type="text/javascript">
        window.addEvent('domready', function() {
            new sptweetSlide('#sp-tweet-id<?php echo $moduleID; ?>', {
                'morphDuration':<?php  echo $params->get('morph_duration','500');?>,
                'animationPeriodicalTime':<?php  echo $params->get ('animation_periodical_time','8000');?>
            });
        });
        </script> 
        <?php
    } 
    else
    {
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            $(document).ready(function(){
                $('#sp-tweet-id<?php echo $moduleID; ?>').sptweetSlide({
                    'morphDuration':<?php  echo $params->get('morph_duration','500');?>,
                    'animationPeriodicalTime':<?php  echo $params->get ('animation_periodical_time','8000');?>
                });
            }); 
        });
        </script> 
        <?php 
    }
    ?>
    <?php }
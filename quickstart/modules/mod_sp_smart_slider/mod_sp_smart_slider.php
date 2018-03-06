<?php
/**
* @author    JoomShaper http://www.joomshaper.com
* @copyright Copyright (C) 2010 - 2014 JoomShaper
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$module_name             = basename(dirname(__FILE__));
$module_dir              = dirname(__FILE__);
$module_id               = $module->id;
$document                = JFactory::getDocument();
$css_path                = JPATH_THEMES. '/'.$document->template.'/css/'.$module_name;
$style                   = $params->get('sp_style');

if( empty($style) )
{
    JFactory::getApplication()->enqueueMessage( 'Slider style no declared. Check sp smart slider configuration and save again from admin panel' , 'error');
    return;
}

$layoutoverwritepath     = JURI::base(true) . '/templates/'.$document->template.'/html/'. $module_name. '/tmpl/'.$style;
$document                = JFactory::getDocument();
require_once $module_dir.'/helper.php';
$helper = new mod_SPSmartSlider($params, $module_id);
$data = (array) $helper->display();
$option = (array) $params->get('animation')->$style;
if(  is_array( $helper->error() )  )
{
    JFactory::getApplication()->enqueueMessage( implode('<br /><br />', $helper->error()) , 'error');
} 
else
{
    if( file_exists($layoutoverwritepath.'/view.php') )
    {
        require(JModuleHelper::getLayoutPath($module_name, $layoutoverwritepath.'/view.php') );   
    } else {
        require(JModuleHelper::getLayoutPath($module_name, $style.'/view') );   
    }

    $helper->setAssets($document, $style);

    if(file_exists($css_path.'/tmpl/'.$style.'.css'))
    {
        $document->addStylesheet(JURI::base(true) . '/templates/'.$document->template.'/css/'. $module_name.'/tmpl/'.$style.'.css');
    }
}
<?php

    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2013 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */

    defined('JPATH_BASE') or die;

    jimport('joomla.form.formfield');
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');

    class JFormFieldTmpl extends JFormField {

        protected $type = 'tmpl';

        protected function getInput()
        {
            $tmpl = JPATH_SITE.'/modules/mod_sp_smart_slider/tmpl';
            $folders = JFolder::folders($tmpl);
            $options = array();
            if( !defined('SP_SLIDER_DEFAULT') ) define('SP_SLIDER_DEFAULT', $this->element['default']);
            if(empty($this->value)) $this->value = SP_SLIDER_DEFAULT;
            
            if( empty($folders) )  return 'No Style template found';
            
            foreach($folders as $folder)
            {
                if( !file_exists($tmpl.'/'.$folder.'/'.'config.xml') ) continue;
                $xml = simplexml_load_file($tmpl.'/'.$folder.'/'.'config.xml');
                $options[] = JHTML::_( 'select.option', $folder, $xml->name );
            }
            
            return JHTML::_('select.genericlist', $options, 'jform[params]['.$this->fieldname.']', '', 'value', 'text', $this->value, 'jform_params_sp_style');
        }
    }

<?php

    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2013 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */

    defined('JPATH_BASE') or die;

    jimport('joomla.form.formfield');

    class JFormFieldAssets extends JFormField {
        protected $type = 'Assets';
        protected function getInput()
        {
            $doc = JFactory::getDocument();

            /** Adding Bootstrap */
            if (JVERSION < 3)
            {
                $doc->addStyleSheet(JURI::root(true).$this->element['url'].'/bootstrap/css/bootstrap.min.css'); 
                $doc->addStyleSheet(JURI::root(true).$this->element['url'].'/style.css');           
                $doc->addScript(JURI::root(true).$this->element['url'].'/jquery.min.js');           
                $doc->addScript(JURI::root(true).$this->element['url'].'/jquery-noconflict.js');            
                $doc->addScript(JURI::root(true).$this->element['url'].'/bootstrap/js/bootstrap.min.js');   
                $doc->addScriptDeclaration( file_get_contents(dirname(dirname(__FILE__)).'/assets/script.js') );
            } else {
                JHtml::_('jquery.ui', array('core', 'sortable'));
                $doc->addScriptDeclaration( file_get_contents(dirname(dirname(__FILE__)).'/assets/script-3.js') );
                $doc->addStyleSheet(JURI::root(true).$this->element['url'].'/style-3.css');
            }

            JHtml::_('behavior.modal');

            $script = array();

            $script[] = '
            jQuery(document).ready(function(){
            SqueezeBox.initialize({});
            jQuery(document).on("click", "a.model", function(event, element) {
            event.preventDefault();
            SqueezeBox.assign(element, {
            parse: \'rel\'
            });
            });
            });';

            $script[] = ' function jInsertFieldValue(value, id) {';
            $script[] = '   var old_id = document.id(id).value;';
            $script[] = '   if (old_id != id) {';
            $script[] = '    var elem = document.id(id)';
            $script[] = '    elem.value = value;';
            $script[] = '    elem.fireEvent("change");';
            $script[] = ' }';
            $script[] = ' }';

            // Add the script to the document head.
            $doc->addScriptDeclaration(implode("\n", $script));
            return null;
        }
}
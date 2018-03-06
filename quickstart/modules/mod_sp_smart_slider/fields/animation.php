<?php

    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2013 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */

    defined('JPATH_BASE') or die;

    jimport('joomla.form.formfield');
    jimport( 'joomla.form.form' );
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');

    class JFormFieldAnimation extends JFormField {

        protected $type = 'animation';

        private $params;


        protected function getLabel()
        {
            return '';
        }


        public function makeInput($item, $name, $value=array())
        {


            $html='<div id="sp-animation-setting-panel">';

            if( isset($item->fieldset) )
            {
                $value = (isset($value[$name])) ? $value[$name]: array();
                $inc = 0;
                if( JVERSION<3 )
                {

                    foreach($item->fieldset as $fieldset)
                    {
                        $html .= '<fieldset>';
                        $html .= '<legend> '.$fieldset['name'].' : </legend>';
                        $html .= '<ul>';
                        foreach($fieldset->field as $field)
                        {




                            $fieldvalue = (string) (isset($value[(string)$field['name']])) ? $value[(string)$field['name']] : (string)$field['default'];
                            $fieldname = 'jform[params]['.$this->fieldname.']['.$name.']['.$field['name'].']';
                            switch((string)$field['type'])
                            {
                                case 'text':
                                    $html .= '<li><label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> '.$field['label'].' : </label>';
                                    $html .= '<input type="text"
                                    id="'.$field['id'].'" 
                                    class="'.$field['class'].'" 
                                    name="'.$fieldname.'"
                                    style="'.$field['style'].'"
                                    value="'.$fieldvalue.'">  <div class="sp-animation-unit">'.$field['unit'].'</div>  </li>';
                                    break;

                                case 'checkbox':




                                    $html .= '<li>';
                                    $html .= '<label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> <input type="checkbox"
                                    id="'.$field['id'].'" 
                                    class="'.$field['class'].'" 
                                    name="'.$fieldname.'"
                                    '.(($fieldvalue==$field['value'])?' checked="checked" ':'').'
                                    style="'.$field['style'].'"
                                    value="'.$field['value'].'">  '.$field['label'].'  </label>   </li>';
                                    break;


                                case 'list':




                                    $html .= '<li>';
                                    $html .= '<label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> '.$field['label'].' </label>
                                    <select
                                    id="'.$field['id'].'" 
                                    class="'.$field['class'].'" 
                                    name="'.$fieldname.'"
                                    '.(($field['multiple']=='1')?' multiple="multiple" ':'').'
                                    '.(($field['size']>1)?' size="'.$field['size'].'" ':'').'
                                    style="'.$field['style'].'">';

                                    foreach($field->option as $option)
                                    {
                                        $html .= '<option ';
                                        $html .= (($fieldvalue==$option['value'])?' selected="selected" ':'');
                                        $html .='value="'.$option['value'].'">'.$option.'</option>'; 
                                    }
                                    $html .= '</select></li>';
                                    break;




                                case 'radio':



                                    $html .= '<li>';
                                    $html .= '<label class="hasTip" for="'.$field['name'].'" title="'.$field['label'].'::'.$field['description'].'"> '.$field['label'].' </label>



                                    <fieldset id="jform_'.$field['name'].'" class="radio">';



                                    $i=0;
                                    foreach($field->option as $key=>$option)
                                    {
                                        $i++;

                                        $option['value'] = (string) $option['value'];


                                        $html .= '<input type="radio" id="jform_'.$field['name'].$i.'" 
                                        name="'.$fieldname.'" value="'.$option['value'].'"';
                                        $html .= (($fieldvalue==$option['value'])?' checked="checked" ':'');
                                        $html .='>';

                                        $html .= '<label for="'.$field['name'].$i.'">'.$option.'</label>';


                                    }
                                    $html .= ' </fieldset></li>';
                                    break;   



                                case 'textarea':
                                    $html .= '<li><label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> '.$field['label'].' : </label>';
                                    $html .= '<textarea
                                    id="'.$field['id'].'" 
                                    class="'.$field['class'].'" 
                                    name="'.$fieldname.'"
                                    style="'.$field['style'].'">'.$fieldvalue.'</textarea> </li>';
                                    break;
                            }
                        }

                        $html .= '</ul>';
                        $html .= '</fieldset>';
                    }

                    ///
                } else {

                    ///   joomla 3
                    foreach($item->fieldset as $fieldset)
                    {
                        $html .= '<div class="accordion-group">';
                        $html .= '<div class="accordion-heading">

                        <strong> <a href="#collapse'.$inc.'" data-toggle="collapse" class="accordion-toggle collapsed">'.$fieldset['name'].'</a> </strong></div>';
                        $html .= '<div class="accordion-body collapse" id="collapse'.$inc.'" style="height: 0px;"><div class="accordion-inner">';
                        foreach($fieldset->field as $field)
                        {


                            $fieldvalue = (string) (isset($value[(string)$field['name']])) ? $value[(string)$field['name']] : (string)$field['default'];
                            $fieldname = 'jform[params]['.$this->fieldname.']['.$name.']['.$field['name'].']';
                            switch((string)$field['type'])
                            {
                                case 'text':
                                    $html .= '<div class="control-group">
                                    <div class="control-label">
                                    <label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> '.$field['label'].' : 
                                    </label>
                                    </div>

                                    <div class="controls">';

                                    $field['unit'] = (string) $field['unit'];

                                    if( !empty($field['unit']) )
                                    {


                                        $html .= '<div class="input-append">
                                        <input type="text"
                                        id="'.$field['id'].'" 
                                        class="'.$field['class'].'" 
                                        name="'.$fieldname.'"
                                        style="'.$field['style'].'"
                                        value="'.$fieldvalue.'">  <span class="add-on">'.$field['unit'].'</span>
                                        </div>';

                                    } else {

                                        $html .= '<input type="text"
                                        id="'.$field['id'].'" 
                                        class="'.$field['class'].'" 
                                        name="'.$fieldname.'"
                                        style="'.$field['style'].'"
                                        value="'.$fieldvalue.'">';

                                    }

                                    $html .= '
                                    </div>
                                    </div>';
                                    break;

                                case 'checkbox':


                                    $html .= '<li>';
                                    $html .= '<label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> <input type="checkbox"
                                    id="'.$field['id'].'" 
                                    class="'.$field['class'].'" 
                                    name="'.$fieldname.'"
                                    '.(($fieldvalue==$field['value'])?' checked="checked" ':'').'
                                    style="'.$field['style'].'"
                                    value="'.$field['value'].'">  '.$field['label'].'  </label>   </li>';
                                    break;


                                case 'list':



                                    $html .= '<div class="control-group">
                                    <div class="control-label">
                                    <label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> '.$field['label'].' : 
                                    </label>
                                    </div>

                                    <div class="controls">';

                                    $html .= '<select
                                    id="'.$field['id'].'" 
                                    class="'.$field['class'].'" 
                                    name="'.$fieldname.'"
                                    '.(($field['multiple']=='1')?' multiple="multiple" ':'').'
                                    '.(($field['size']>1)?' size="'.$field['size'].'" ':'').'
                                    style="'.$field['style'].'">';

                                    foreach($field->option as $option)
                                    {
                                        $html .= '<option ';
                                        $html .= (($fieldvalue==$option['value'])?' selected="selected" ':'');
                                        $html .='value="'.$option['value'].'">'.$option.'</option>'; 
                                    }
                                    $html .= '</select>';

                                    $html .= '
                                    </div>
                                    </div>';

                                    break;

                                case 'radio':

                                    $html .= '<div class="control-group">
                                    <div class="control-label">
                                    <label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> '.$field['label'].' : 
                                    </label>
                                    </div>

                                    <div class="controls">';

                                    $html .= '<fieldset id="jform_'.$field['name'].'" class="radio btn-group">';

                                    $i=0;
                                    foreach($field->option as $key=>$option)
                                    {
                                        $i++;

                                        $option['value'] = (string) $option['value'];


                                        $html .= '<input type="radio" id="jform_'.$field['name'].$i.'" 
                                        name="'.$fieldname.'" value="'.$option['value'].'"';
                                        $html .= (($fieldvalue==$option['value'])?' checked="checked" ':'');
                                        $html .='>';

                                        $html .= '<label class="';
                                        //$html .= (($fieldvalue==$option['value'])?' active ':'');

                                        $html .= '" for="'.$field['name'].$i.'">'.$option.'</label>';


                                    }
                                    $html .= ' </fieldset>';
                                    $html .= '</div>
                                    </div>';
                                    break;   



                                case 'textarea':


                                    $html .= '<div class="control-group">
                                    <div class="control-label">
                                    <label class="hasTip" for="'.$field['id'].'" title="'.$field['label'].'::'.$field['description'].'"> '.$field['label'].' : 
                                    </label>
                                    </div>

                                    <div class="controls">';


                                    $html .= '<textarea
                                    id="'.$field['id'].'" 
                                    class="'.$field['class'].'" 
                                    name="'.$fieldname.'"
                                    style="'.$field['style'].'">'.$fieldvalue.'</textarea> ';

                                    $html .= '</div>
                                    </div>';

                                    break;
                            }
                        }

                        $html .= '</div></div>';
                        $html .= '</div>';
                        $inc++;
                    }


                }

            }    

            $html.='</div>';
            return $html;
        }


        protected function getInput()
        {



            $doc = JFactory::getDocument();
            $this->params =  (array) $this->form->getValue('params');
            if( !isset($this->params['sp_style'] ))
            {
                // SP_SLIDER_DEFAULT  difined at fields/tmpl.php file :) 
                $this->params['sp_style'] = SP_SLIDER_DEFAULT;
            }



            $tmpl = JPATH_SITE.'/modules/mod_sp_smart_slider/tmpl';



            $defaultStyle = simplexml_load_file( $tmpl.'/'.$this->params['sp_style'].'/config.xml' );
            $folders = JFolder::folders($tmpl);
            if( empty($folders) )  return 'No Style template found';
            $script = array();
            foreach($folders as $folder)
            {
                if( !file_exists($tmpl.'/'.$folder.'/'.'config.xml') ) continue;

                $configfiledir = $tmpl.'/'.$folder;
                $configfile = $configfiledir.'/'.'config.xml';
                $assets = (dirname(dirname(__FILE__)).'/tmpl/'.$folder); // 
                $xml = simplexml_load_file($configfile);
                $admincss = $adminjs = array();
                if( isset($xml->files->admin->filename) )
                {
                    foreach( $xml->files->admin->filename as $file)
                    {
                        if( isset($file['type']) and $file['type']=='javascript' )
                        {
                            if( isset($file['source']) and $file['source']=='external')
                            {
                                $doc->addScript($file);
                            } else {
                                $doc->addScript(    JURI::root(true).'/modules/mod_sp_smart_slider/tmpl/'.$folder.'/'.$file );
                            }
                        }  

                        if( isset($file['type']) and $file['type']=='stylesheet' ){

                            if( isset($file['source']) and $file['source']=='external')
                            {
                                $doc->addStyleSheet($file);
                            } else {
                                $doc->addStyleSheet( JURI::root(true).'/modules/mod_sp_smart_slider/tmpl/'.$folder.'/'.$file );
                            }
                        }
                    }
                }



                if( !isset($this->params['animation']) ) $this->params['animation']=array();

                $script[] = 'var anim'.$folder.'=\''. str_ireplace("\n",' ', $this->makeInput($xml->config, $folder, $this->params['animation']))."';" ;


            }

            $doc->addScriptDeclaration(implode("\n", $script));
            if( !isset($this->params['animation']) ) $this->params['animation']=array();


            return $this->makeInput($defaultStyle->config, $this->params['sp_style'], $this->params['animation']);
        }
}
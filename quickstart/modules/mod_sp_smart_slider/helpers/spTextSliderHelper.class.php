<?php

    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2013 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */
	
    // no direct access
    defined('_JEXEC') or die('Restricted access');  
	
    class spTextSliderHelper
    {

        public $name = 'Text';
        public $uniqid   = 'text';
        public $fieldname;
        public $params;
        public function setOptions()
        {
            $html = array();
            $html[] = array(
                'title'=>'Pre Title',
                'tip'=>'Pre Title',
                'tipdesc'=>'Text to display before title',
                'class'=>$this->uniqid.'-slider-pretitle-li',
                'attrs'=>'',
                'fieldname'=>'pretitle',
                'html'=>'<input type="text"  value="'.$this->params['pretitle'].'"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][pretitle][]">'
            );

            $html[] = array(
                'title'=>'Title',
                'tip'=>'Slide title',
                'tipdesc'=>'Set slide title text',
                'class'=>$this->uniqid.'-slider-title-li',
                'attrs'=>'',
                'fieldname'=>'title',
                'html'=>'<input ref="title" type="text"  value="'.$this->params['title'].'"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][title][]">'
            );

            $html[] = array(
                'title'=>'Post Title',
                'tip'=>'Post title',
                'tipdesc'=>'Text to display after title',
                'class'=>$this->uniqid.'-slider-posttitle-li',
                'attrs'=>'',
                'fieldname'=>'posttitle',
                'html'=>'<input type="text"  value="'.$this->params['posttitle'].'"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][posttitle][]">'
            );

            $html[] = array(
                'title'=>'Link',
                'tip'=>'Custom link',
                'tipdesc'=>'Custom link url',
                'class'=>$this->uniqid.'-slider-link-li',
                'attrs'=>'',
                'fieldname'=>'link',
                'html'=>'<input type="text"  value="'.$this->params['link'].'"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][link][]">'
            );

            $html[] = array(
                'title'=>'Readmore text',
                'tip'=>'Readmore text',
                'tipdesc'=>'Write readmore text',
                'class'=>$this->uniqid.'-slider-readmore-li',
                'attrs'=>'',
                'fieldname'=>'readmore',
                'html'=>'<input type="text"  value="'.$this->params['readmore'].'"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][readmore][]">'
            );

            $html[] = array(
                'title'=>'Image',
                'tip'=>'Slide image',
                'tipdesc'=>'Choose slide image',
                'class'=>''.$this->uniqid.'-slider-item-li',
                'attrs'=>'',
                'fieldname'=>'image',
                'html'=>'
                <input style="width:110px" type="text" id="'.$this->uniqid.'-slider-item-%index%" 
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][image][]" class="'.$this->uniqid.'-slider-image" 
                value="'.$this->params['image'].'">
                <a class="model  btn" class="'.$this->uniqid.'-slide-image-select" title="Select" href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=&amp;author=&amp;fieldid='.$this->uniqid.'-slider-item-%index%&amp;folder=" rel="{handler: \\\'iframe\\\', size: {x: 800, y: 500}}">Select</a>
                <a title="Clear" class="btn" href="javascript:;" onclick="javascript:document.getElementById(\\\''.$this->uniqid.'-slider-item-%index%\\\').value=\\\'\\\';">Clear</a>'
            );

            $html[] = array(
                'title'=>'Image Thumb',
                'tip'=>'Choose thumb image',
                'tipdesc'=>'Choose thumb image',
                'class'=>''.$this->uniqid.'-slider-itemthumb-li',
                'attrs'=>'',
                'fieldname'=>'thumb',
                'html'=>'
                <input style="width:110px" type="text" id="'.$this->uniqid.'-sliderthumb-item-%index%" 
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][thumb][]" class="'.$this->uniqid.'-slider-image" 
                value="'.$this->params['thumb'].'">
                <a class="model  btn" class="'.$this->uniqid.'-slide-image-select" title="Select" href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=&amp;author=&amp;fieldid='.$this->uniqid.'-sliderthumb-item-%index%&amp;folder=" rel="{handler: \\\'iframe\\\', size: {x: 800, y: 500}}">Select</a>
                <a title="Clear" class="btn" href="javascript:;" onclick="javascript:document.getElementById(\\\''.$this->uniqid.'-sliderthumb-item-%index%\\\').value=\\\'\\\';">Clear</a>'
            );

            $html[] = array(
                'title'=>'Intro Text',
                'tip'=>'Intro text',
                'tipdesc'=>'Slider intro text',
                'class'=>''.$this->uniqid.'-slider-item-li',
                'attrs'=>'',
                'fieldname'=>'introtext',
                'html'=>'<textarea  name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][introtext][]">'.$this->params['introtext'].'</textarea>');

            $html[] = array(
                'title'=>'Full Text',
                'tip'=>'Full text',
                'tipdesc'=>'Slider full text',
                'class'=>''.$this->uniqid.'-slider-item-li',
                'attrs'=>'',
                'fieldname'=>'content',
                'html'=>'<textarea  name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][content][]">'.$this->params['content'].'</textarea>');

            $html[] = array(
                'title'=>'State',
                'tip'=>'Set State',
                'tipdesc'=>'Published or unpublished slide item',
                'class'=>''.$this->uniqid.'-slider-item-li',
                'attrs'=>'',
                'fieldname'=>'text',
                'html'=>'
                <select class="sp-state" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][state][]">
                <option value="published" '.(($this->params['state']=='published')?'selected':'').' >Published</option>
                <option value="unpublished"  '.(($this->params['state']=='unpublished')?'selected':'').'>Un Published</option>
                </select>'
            );

            return $html;
        }


        public function styleSheet()
        {

            return '';

        }


        public function JavaScript()
        {

            return '';

        }


        public function display($helper)
        {
            return $this->params;
        }
}
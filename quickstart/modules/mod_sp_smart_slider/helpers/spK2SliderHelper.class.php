<?php

    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2013 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */
	
    // no direct access
    defined('_JEXEC') or die('Restricted access');
	
    class spK2SliderHelper
    {
        public $name = 'K2';
        public $uniqid   = 'k2';
        public $fieldname;
        public $params;
        public function setOptions()
        {
            $html = array();
            $html[] = array(
                'title'=>'Article',
                'tip'=>'Select an article',
                'tipdesc'=>'Choose an article from source',
                'class'=>'select-'.$this->uniqid,
                'attrs'=>'',
                'html'=>'
                <input readonly="readonly" type="text" value="'.$this->params['title'].'" ref="title" id="'.$this->uniqid.'-slider-article-item-%index%" 
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][title][]" class="'.$this->uniqid.'-slider-item">

                <input type="hidden"  value="'.$this->params['id'].'" id="'.$this->uniqid.'-slider-articleid-item-%index%" 
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][id][]" class="'.$this->uniqid.'-slider-item">

                <a class="model btn" ref="{article: \\\''.$this->uniqid.'-slider-article-item-%index%\\\', id: \\\''.$this->uniqid.'-slider-articleid-item-%index%\\\'}" class="'.$this->uniqid.'-slide-item-select" title="Select" href="index.php?option=com_k2&view=items&task=element&tmpl=component&object=jform[request][id]" rel="{handler: \\\'iframe\\\', size: {x: 800, y: 500}}">Select</a>'
                //<a class="model btn" ref="{article: \\\''.$this->uniqid.'-slider-article-item-%index%\\\', id: \\\''.$this->uniqid.'-slider-articleid-item-%index%\\\'}" class="'.$this->uniqid.'-slide-item-select" title="Select" href="index.php?option=com_content&view=articles&layout=modal&tmpl=component&function=spSelectArticle" rel="{handler: \\\'iframe\\\', size: {x: 800, y: 500}}">Select</a>'
            );

            $html[] = array(
                'title'=>'Pre Title',
                'tip'=>'Pre Title',
                'tipdesc'=>'Text to display before title',
                'class'=>''.$this->uniqid.'-slider-title-li',
                'attrs'=>'',
                'html'=>'
                <input type="text" value="'.$this->params['pretitle'].'"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][pretitle][]">'
            );

            $html[] = array(
                'title'=>'Title type',
                'tip'=>'Title type',
                'tipdesc'=>'Select type of title from list',
                'class'=>$this->uniqid.'-slider-title-type-li',
                'attrs'=>'',
                'html'=>'
                <select class="'.$this->uniqid.'-slider-title-custom" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][titletype][]">
                <option value="default" '.(($this->params['titletype']=='default')?'selected':'').'>Default</option>
                <option value="custom" '.(($this->params['titletype']=='custom')?'selected':'').'>Custom</option>
                </select>'
            );

            $html[] = array(
                'title'=>'Title',
                'tip'=>'Custom title',
                'tipdesc'=>'Set custom title text',
                'class'=>''.$this->uniqid.'-slider-title-li',
                'attrs'=>(($this->params['titletype']=='custom')?' style="display: block;"':'  style="display: none;"'),
                'html'=>'
                <input type="text" value="'.$this->params['customtitle'].'"  ref="title" id="'.$this->uniqid.'-slider-title-%index%"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][customtitle][]">'
            );

            $html[] = array(
                'title'=>'Post Title',
                'tip'=>'Post title',
                'tipdesc'=>'Text to display after title',
                'class'=>''.$this->uniqid.'-slider-title-li',
                'attrs'=>'',
                'html'=>'
                <input type="text" value="'.$this->params['posttitle'].'"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][posttitle][]">'
            );

            $html[] = array(
                'title'=>'Image source',
                'tip'=>'Image source',
                'tipdesc'=>'Set image source from the list',
                'class'=>''.$this->uniqid.'-slider-image-type-li',
                'attrs'=>'',
                'html'=>'
                <select class="'.$this->uniqid.'-slider-image-type-custom" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][customimage][]">
                <option value="no" '.(($this->params['customimage']=='no')?'selected':'').'>Default</option>
                <option value="yes"  '.(($this->params['customimage']=='yes')?'selected':'').'>Custom</option>
                </select>'
            );

            $html[] = array(
                'title'=>'Image',
                'tip'=>'Custom image',
                'tipdesc'=>'Choose custom image',
                'class'=>''.$this->uniqid.'-slider-image-li',
                'attrs'=>(($this->params['customimage']=='yes')?' style="display: block;"':'  style="display: none;"'),
                'html'=>'
                <input style="width:110px" type="text" value="'.$this->params['image'].'" id="'.$this->uniqid.'-slider-image-%index%" 
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][image][]" class="'.$this->uniqid.'-slider-image">
                <a class="model  btn" class="'.$this->uniqid.'-slide-image-select" title="Select" href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=&amp;author=&amp;fieldid='.$this->uniqid.'-slider-image-%index%&amp;folder=" rel="{handler: \\\'iframe\\\', size: {x: 800, y: 500}}">Select</a>
                <a title="Clear" class="btn" href="javascript:;" onclick="javascript:document.getElementById(\\\''.$this->uniqid.'-slider-image-%index%\\\').value=\\\'\\\';">Clear</a>'
            );

            $html[] = array(
                'title'=>'Thumb',
                'tip'=>'Custom thumb image',
                'tipdesc'=>'Choose custom thumb image',
                'class'=>''.$this->uniqid.'-slider-image-li',
                'attrs'=>(($this->params['customimage']=='yes')?' style="display: block;"':'  style="display: none;"'),
                'html'=>'
                <input type="text" style="width:110px" value="'.$this->params['thumb'].'" id="'.$this->uniqid.'-thumbslider-image-%index%" 
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][thumb][]" class="'.$this->uniqid.'-slider-image">
                <a class="model  btn" class="'.$this->uniqid.'-slide-image-select" title="Select" href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=&amp;author=&amp;fieldid='.$this->uniqid.'-thumbslider-image-%index%&amp;folder=" rel="{handler: \\\'iframe\\\', size: {x: 800, y: 500}}">Select</a>
                <a title="Clear" class="btn" href="javascript:;" onclick="javascript:document.getElementById(\\\''.$this->uniqid.'-thumbslider-image-%index%\\\').value=\\\'\\\';">Clear</a>'
            );

            $html[] = array(
                'title'=>'Show link',
                'tip'=>'Show article link',
                'tipdesc'=>'Display article link or set custom link',
                'class'=>$this->uniqid.'-slider-title-type-li',
                'attrs'=>'',
                'html'=>'
                <select class="'.$this->uniqid.'-slider-showlink" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][showlink][]">
                <option value="yes" '.(($this->params['showlink']=='yes')?'selected':'').'>Yes</option>
                <option value="no" '.(($this->params['showlink']=='no')?'selected':'').'>No</option>
                <option value="custom" '.(($this->params['showlink']=='custom')?'selected':'').'>Custom</option>
                </select>'
            );

            $html[] = array(
                'title'=>'Custom link',
                'tip'=>'Custom link',
                'tipdesc'=>'Custom link url',
                'class'=>''.$this->uniqid.'-slider-title-li',
                'attrs'=>(($this->params['showlink']=='custom')?' style="display: block;"':'  style="display: none;"'),
                'html'=>'<input type="text" value="'.$this->params['link'].'" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][link][]">'
            );

            $html[] = array(
                'title'=>'Text Limit',
                'tip'=>'Text limit type',
                'tipdesc'=>'Choose text limit type',
                'class'=>$this->uniqid.'-slider-title-type-li',
                'attrs'=>'',
                'html'=>'
                <select class="'.$this->uniqid.'-slider-textlimit" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][textlimit][]">
                <option value="no" '.(($this->params['textlimit']=='no')?'selected':'').'>No limit</option>
                <option value="word" '.(($this->params['textlimit']=='word')?'selected':'').'>Word</option>
                <option value="char" '.(($this->params['textlimit']=='char')?'selected':'').'>Character</option>
                </select>'
            );


            $html[] = array(
                'title'=>'Limit Count',
                'tip'=>'Text limit count',
                'tipdesc'=>'Text limit count',
                'class'=>''.$this->uniqid.'-slider-title-li',
                'attrs'=>(($this->params['textlimit']=='no' or !isset($this->params['textlimit']))?' style="display: none;"':'  style="display: block;"'),
                'html'=>'
                <input type="text" value="'.$this->params['limitcount'].'" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][limitcount][]">'
            );

            $html[] = array(
                'title'=>'Strip HTML',
                'tip'=>'Remove html',
                'tipdesc'=>'Remove html tags',
                'class'=>$this->uniqid.'-slider-title-type-li',
                'attrs'=>'',
                'html'=>'
                <select class="'.$this->uniqid.'-slider-striphtml" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][striphtml][]">
                <option value="no" '.(($this->params['striphtml']=='no')?'selected':'').'>No</option>
                <option value="yes" '.(($this->params['striphtml']=='yes')?'selected':'').'>Yes</option>
                </select>'
            );

            $html[] = array(
                'title'=>'Allowable tags',
                'tip'=>'Allowable html tags',
                'tipdesc'=>'Allowable html tags when html removed',
                'class'=>''.$this->uniqid.'-slider-title-li',
                'attrs'=>(($this->params['striphtml']=='no'  or !isset($this->params['striphtml']))?' style="display: none;"':'  style="display: block;"'),
                'html'=>'<input type="text" value="'.$this->params['allowabletag'].'" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][allowabletag][]"><small>&lt;img&gt;,&lt;a&gt; </small>'
            );

            $html[] = array(
                'title'=>'Readmore Text',
                'tip'=>'Readmore text',
                'tipdesc'=>'Write readmore text',
                'class'=>''.$this->uniqid.'-slider-title-li',
                'attrs'=>'',
                'html'=>'
                <input type="text" value="'.$this->params['readmore'].'"   
                name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][readmore][]">'
            );

            $html[] = array(
                'title'=>'State',
                'tip'=>'Set State',
                'tipdesc'=>'Published or unpublished slide item',
                'class'=>''.$this->uniqid.'-slider-item-li',
                'attrs'=>'',
                'fieldname'=>'text',
                'html'=>'
                <select class="sp-state" name="jform[params]['.$this->fieldname.']['.$this->uniqid.'][state][]">
                <option value="published" '.(($this->params['state']=='unpublished')?'selected':'').'>Published</option>
                <option value="unpublished" '.(($this->params['state']=='unpublished')?'selected':'').'>Un Published</option>
                </select>'
            );
            return $html;
        }


        public function styleSheet()
        {
            return '';
        }




        private function JS3()
        {

            return 'var sp_item_opened;



            function jSelectItem(id, title, cid, $null, url)
            {


            var data = jQuery("body").data("article");


            jQuery("#"+data.id).val(id);
            jQuery("#"+data.article).val(title).focus();
            SqueezeBox.close();
            }



            jQuery(document).ready(function(){


            jQuery("#moduleOptions").delegate("a.model", "mouseenter", function(event)
            {
            eval( "var $callerData=(" + jQuery(this).attr("ref") + ")" );
            jQuery("body").data("article", $callerData );
            });


            });


            window.addEvent("domready",function()
            {





            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-title-custom)", function(event, element) {
            if( this.get("value")=="custom" )
            {
            this.getParent().getPrevious().getChildren("[ref=\'title\']").set("readonly","readonly");
            this.getParent().getNext().setStyle("display","block");
            } else {
            this.getParent().getPrevious().getChildren("[ref=\'title\']").set("readonly","");
            this.getParent().getNext().setStyle("display","none");
            }
            });





            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-image-type-custom)", function(event, element) {

            if( this.get("value")=="yes" )
            {
            this.getParent().getNext().setStyle("display","block");
            this.getParent().getNext().getNext().setStyle("display","block");
            } else {
            this.getParent().getNext().setStyle("display","none");
            this.getParent().getNext().getNext().setStyle("display","none");
            }


            });

            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-textlimit)", function(event, element) {

            if( this.get("value")=="no" )
            {
            this.getParent().getNext().setStyle("display","none");
            } else {
            this.getParent().getNext().setStyle("display","block");
            }
            });


            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-striphtml)", function(event, element)
            {

            if( this.get("value")=="no" )
            {
            this.getParent().getNext().setStyle("display","none");
            } else {
            this.getParent().getNext().setStyle("display","block");
            }
            });

            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-showlink)", function(event, element)
            {
            if( this.get("value")=="custom" )
            {
            this.getParent().getNext().setStyle("display","block");
            } else {
            this.getParent().getNext().setStyle("display","none");
            }
            });

            });';

        }


        private function JS2()
        {
            return 'var sp_item_opened;

            function jSelectItem(id, title, cid, $null, url)
            {

            var data = jQuery("body").data("article");

            $(data.article).set("value", title);
            $(data.id).set("value", id);
            $(data.article).focus();
            SqueezeBox.close();
            }



            jQuery(function($){


            $("ul.adminformlist").delegate("a.model", "mouseenter", function()
            {


            eval( "var $callerData=(" + $(this).attr("ref") + ")" );
            $("body").data("article", $callerData );


            });


            });


            window.addEvent("domready",function() {

            /*$(document.body).addEvent("click:relay(a.model)", function(event, element)
            {


            event.stop();
            this.addEvent("click", function(){
            if( this.get("ref") )
            {
            sp_item_opened = JSON.encode(this.get("ref"));
            }
            })

            });*/



            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-title-custom)", function(event, element) {
            if( this.get("value")=="custom" )
            {
            this.getParent().getPrevious().getChildren("[ref=\'title\']").set("readonly","readonly");
            this.getParent().getNext().setStyle("display","block");
            } else {
            this.getParent().getPrevious().getChildren("[ref=\'title\']").set("readonly","");
            this.getParent().getNext().setStyle("display","none");
            }
            });





            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-image-type-custom)", function(event, element) {

            if( this.get("value")=="yes" )
            {
            this.getParent().getNext().setStyle("display","block");
            this.getParent().getNext().getNext().setStyle("display","block");
            } else {
            this.getParent().getNext().setStyle("display","none");
            this.getParent().getNext().getNext().setStyle("display","none");
            }


            });

            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-textlimit)", function(event, element) {

            if( this.get("value")=="no" )
            {
            this.getParent().getNext().setStyle("display","none");
            } else {
            this.getParent().getNext().setStyle("display","block");
            }
            });


            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-striphtml)", function(event, element)
            {

            if( this.get("value")=="no" )
            {
            this.getParent().getNext().setStyle("display","none");
            } else {
            this.getParent().getNext().setStyle("display","block");
            }


            });



            $(document.body).addEvent("change:relay(.'.$this->uniqid.'-slider-showlink)", function(event, element)
            {
            if( this.get("value")=="custom" )
            {
            this.getParent().getNext().setStyle("display","block");
            } else {
            this.getParent().getNext().setStyle("display","none");
            }
            });

            });


            ';




        }

        public function JavaScript()
        {
            return ( JVERSION < 3 ) ? $this->JS2() : $this->JS3() ;
        }

        /**
        * Get article by id
        * 
        * @param int $id
        * @return array
        */

        private function getK2Item($id)
        {
            require_once (JPATH_SITE.'/components/com_k2/helpers/route.php');
            $database = JFactory::getDBO();
            // SQL query for slider
            $query = "
            SELECT 
            `i`.`id` AS `id`,
            `i`.`alias` AS `alias`,
            `i`.`catid` AS `cid`,
            `i`.`title` AS `title`,
            `i`.`introtext` AS `introtext`,
            `i`.`fulltext` AS `content`,
            `i`.`created` AS `date`,
			`c`.`id` AS `categoryid`,
			`c`.`alias` AS `categoryalias`
            FROM 
            #__k2_items AS `i` 
			LEFT JOIN #__k2_categories `c` ON `c`.`id` = `i`.`catid`
            WHERE 
            `i`.`id`='{$id}'
            ;";
            // running query
            $database->setQuery($query);
            $data = (array) $database->loadAssoc();
            $data['title'] = stripslashes($data['title']);
            //Read more link
			$data['link'] = urldecode(JRoute::_(K2HelperRoute::getItemRoute($data['id'].':'.urlencode($data['alias']), $data['cid'].':'.urlencode($data['categoryalias']))));
			
			return $data;
        }

		//retrive k2 image
		private function getImage($id) {
			$images = array();
			$images['image']= 'media/k2/items/cache/' . md5("Image" . $id) . '_XL.jpg';
			$images['thumb']= 'media/k2/items/cache/' . md5("Image" . $id) . '_S.jpg';
			return $images;
		}		

        public function display($helper)
        {

            $article = $this->getK2Item($this->params['id']);
            $article['title'] = ($this->params['titletype']=='yes')?$this->params['customtitle']:$article['title'];
			
			$images = $this->getImage($article['id']);

            if($this->params['showlink'] =='custom'){
                $article['link'] = $this->params['link'];
            }

            $this->params['image'] = ($this->params['customimage']=='yes')?$this->params['image']:$images['image'];
            $this->params['thumb'] = ($this->params['customimage']=='yes')?$this->params['thumb']:$images['thumb'];
            return $article+$this->params;
        }
}
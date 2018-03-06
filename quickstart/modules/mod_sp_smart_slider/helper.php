<?php
    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2014 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */

    // no direct access
    defined('_JEXEC') or die('Restricted access');

    class mod_SPSmartSlider
    {    

        protected $params;
        protected $module_id;
        protected $module_name;
        protected $module_dir;

        //Initiate configurations

        public function __construct($params, $id)
        {
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
            $this->params = $params;
            $this->module_id = $id;
            $this->module_name        = basename(dirname(__FILE__));
            $this->module_dir         = dirname(__FILE__);
            return $this;
        }

        /**
        * Format and beutify slider array
        * 
        * @param object $params
        * @return array
        */
        public function getParams($params)
        {
            $source =  (isset($params->source))?$params->source:array();
            $data=array();
            $i=0;
            foreach($source as $type)
            {
                $data[$i]['source'] = $type;

                if( !isset($$type) ) $$type = 0;

                foreach($params->$type as $item=>$value)
                {
                    $data[$i][$item] = $value[$$type];
                }  

                $$type++;
                $i++;
            }

            return $data;
        }

        /**
        * Grab slider data, format it and return display
        * @return array
        */
        public function generate()
        {

            $data = array();
            $params = $this->getParams($this->params->get('sliders'));
            foreach((array) $params as $index=>$class)
            {
                $className = 'sp'.ucfirst($class['source']).'SliderHelper';
                include_once 'helpers/'.$className.'.class.php';
                $$class['source'] = new $className();
                $$class['source']->params = $params[$index];

                if( isset($params[$index]['state']) and $params[$index]['state']!='published' ) continue;

                $data[]= $$class['source']->display($this);
            }

            return $data;
        }




        public function display()
        {

            if( $this->params->get('module_cache')==='1' )
            {
                $data = $this->Cache(
                    'sp_smart_slider.json',
                    array($this,'generate'),
                    array(),
                    (int) $this->params->get('cache_time'),
                    array($this,'onDataError'),
                    true
                );
            } else {
                $data = $this->generate();
            }
            return $data;
        }




        /**
        * Get article by id
        * 
        * @param int $id
        * @return array
        */

        public function getArticle($id)
        {

            require_once (JPATH_SITE.'/components/com_content/helpers/route.php');
            $database = JFactory::getDBO();
            // SQL query for slider
            $query = "
            SELECT 
            `c`.`id` AS `id`,
            `c`.`catid` AS `cid`,
            `c`.`title` AS `title`,
            `c`.`introtext` AS `introtext`,
            `c`.`fulltext` AS `content`,
            `c`.`created_by_alias` AS `author`,
            `c`.`created` AS `date`,
            `c`.`images` AS `images`
            FROM 
            #__content AS `c` 
            WHERE 
            `c`.`id`='{$id}'
            ;";
            // running query
            $database->setQuery($query);
            $data = (array) $database->loadAssoc();
            $data['title'] = stripslashes($data['title']);
            //  set link
            $data['link'] = JRoute::_(ContentHelperRoute::getArticleRoute($data['id'], $data['cid']));
            return $data;
        }

        /**
        * String show limitation
        * 
        * @param string $text
        * @param int $limit
        * @param string $type,  default: no   others: no, char, word
        * @return string
        */
        public function textLimit($text, $limit, $type='no') {  //function to cut text
            $text      = preg_replace('/<img[^>]+\>/i', "", $text);
            if ($limit=='no') {//no limit
                $allowed_tags   = '<b><i><a><small><h1><h2><h3><h4><h5><h6><sup><sub><em><strong><u><br>';
                //$text     = strip_tags( $text, $allowed_tags );
                $text     = $text; 
            } else {
                if ($type=='char')
                {       // character lmit
                    $text    = JFilterOutput::cleanText($text);
                    $sep     = (utf8_strlen($text)>$limit) ? '' : '';   //   core function of joomla. link: http://api.joomla.org/elementindex_utf8.html
                    $text    = utf8_substr($text,0,$limit) . $sep;  
                } else { // word limit
                    $text    = JFilterOutput::cleanText($text);
                    $text    = explode(' ',$text);
                    $sep    = (count($text)>$limit) ? '' : '';
                    $text   = implode(' ', array_slice($text,0,$limit)) . $sep;  
                }  
            }
            return $text;
        }


        /**
        * Add scripts and stylesheet at frontend from style config file
        * 
        * @param JFactory::getDocument() $document
        * @param string $style
        * @return mod_SPSmartSlider
        */

        public function setAssets($document, $style)
        {

            $xml = simplexml_load_file($this->module_dir.'/tmpl/'.$style.'/config.xml');



            if( isset($xml->files->public->filename) )
            {
                foreach($xml->files->public->filename as $file)
                {
                    if( $file['type']=='javascript' )
                    {
                        if( $file['source']=='external')
                        {
                            $document->addScript($file);
                        } else {
                            $document->addScript(    JURI::root(true).'/modules/'.$this->module_name.'/tmpl/'.$file );
                        }
                    }  

                    if( $file['type']=='stylesheet' ){
                        if( $file['source']=='external')
                        {
                            $document->addStyleSheet($file->data());
                        } else {
                            $document->addStyleSheet(JURI::root(true).'/modules/'.$this->module_name.'/tmpl/'.$file);
                        }
                    } 
                }
            }
            return $this;
        }


        /***
        * Adding jQuery in frontend
        * 
        * @param object $document
        * @param bool $usecdn.   default is false
        */
        public function addJQuery($document, $usecdn=false)
        {
            if (JVERSION < 3) {
                $scripts = (array) array_keys( $document->_scripts );
                $hasjquery=false;
                foreach($scripts as $script)
                {
                    if (preg_match("/\b(jquery|jquery-latest).([0-9\.min|max]+).(.js)\b/i", $script)) {
                        $hasjquery = true;
                    }  
                }

                if( !$hasjquery )
                {
                    if( $usecdn ) $document->addScript( 'http://code.jquery.com/jquery-latest.min.js' );
                    else $document->addScript( JURI::root(true).'/modules/'.$this->module_name.'/assets/jquery.min.js' );
                }
            } else {
                JHtml::_('jquery.framework');        
            }
        }





        /**
        * Error Container array
        * 
        * @var array
        */
        private $errors = array();

        /**
        * Get Errors, If index is null errors stored as numeric array.
        * 
        * @param int | string $index    default is NULL
        * @return mixed
        */
        public function error($index=null)
        {
            if( !empty($this->errors) )
            {
                if( is_null($index) ) return  $this->errors; 
                else
                {
                    if( is_null($this->errors[$index]) ) return false;
                    else return  $this->errors[$index]; 
                } 
            } 
            else return false;
        }


        /**
        * Set errors in error variable. If index is null errors stored as numeric array.
        * 
        * @param mixed $msg
        * @param mixed $index     default is null. 
        */
        public function setError($msg, $index=null)
        {
            if( is_null($index) ) $this->errors[] = $msg;
            else $this->errors[$index] = $msg;
        }


        private function onDataError($params)
        {
            if(  empty($params['data'])  )
            {
                JFile::Delete($params['file']); 
                $this->setError('Cann\'t get any slider data to generate slide in module  "'. $this->module_name.'".');
            }
        }


        /**
        * Simple caching function
        * @version  1.3
        * @param string $file
        * @param string | array $datafn                  e.g:  functionname |  array( object, function) ,
        * @param array  $datafnarg    default is array  e.g:   array( arg1, arg2, ...) ,       
        * @param mixed $time         default is 900  = 15 min
        * @param mixed $onerror      string function or array(object, method )
        * @param bool  $usejson      default is false.   use json encode for caching
        * @return string
        */
        private function Cache( $file,  $datafn, $datafnarg=array(), $time=900, $onerror='', $usejson=false)
        {

            if (is_writable(JPATH_CACHE))
            {
                // check cache dir or create cache dir

                if (!JFolder::exists(JPATH_CACHE.'/'.$this->module_name))
                {
                    JFolder::create(JPATH_CACHE.'/'.$this->module_name.'/'); 
                }

                $cache_file = JPATH_CACHE.'/'.$this->module_name.'/'.$this->module_id.'-'.$file;

                // check cache file, if not then write cache file
                if ( !JFile::exists($cache_file) )
                {
                    $data =   ($usejson==true) ? json_encode(call_user_func_array($datafn, $datafnarg)) :call_user_func_array($datafn, $datafnarg);
                    JFile::write($cache_file, $data);
                }  
                // if cache file expires, then write cache
                elseif ( filesize($cache_file) == 0 || ((filemtime($cache_file) + (int) $time ) < time()) )
                {

                    $data =   ($usejson==true) ? json_encode(call_user_func_array($datafn, $datafnarg)) :call_user_func_array($datafn, $datafnarg);
                    JFile::write($cache_file, $data);
                }
                // read cache file
                $data =  ($usejson==true) ? json_decode(JFile::read($cache_file), true):JFile::read($cache_file);
                $params['file'] = $cache_file;
                $params['data'] = $data;
                if( !empty($onerror) ) call_user_func($onerror, $params);
                return $data;
            } else {
                return   ($usejson==true) ? json_encode(call_user_func_array($datafn, $datafnarg)) :call_user_func_array($datafn, $datafnarg);
            }
        }

        /**
        * Convert numeric number to language
        * 
        * @param int | string $number
        * @return language formatted text
        */
        public function Num2Lang($number, $prefix = 'SP_')
        {
            $number = (array) str_split($number);
            $formated = '';
            foreach($number as $no)
            {
                if (ctype_digit($no))
                {
                    $formated.=JText::_($prefix . $no);    
                } else $formated.=$no;
            }
            return $formated;
        }

}

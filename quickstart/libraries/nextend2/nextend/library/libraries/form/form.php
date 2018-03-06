<?php
N2Loader::import('libraries.xml.helper');

class N2FormAbstract extends N2Data {

    public static $documentation = '';

    public $appType;

    var $_xml;

    var $_xmlfile;

    var $_tabs;

    public static $importPaths = array();

    public $xmlFolder = '';

    /**
     *
     * App type must be decalred if you need to route in the parameters. Route is needed for example for subform!!!
     *
     * @param $appType N2ApplicationType|bool
     */
    public function __construct($appType = false) {
        $this->appType = $appType;
        $this->_xml    = null;
        $this->_tabs   = array();
        parent::__construct();

    }

    function initTabs($removeImportPath = true) {
        if ($this->xmlFolder) {
            N2Form::$importPaths[] = $this->xmlFolder;
        }
        if (count($this->_tabs) == 0 && $this->_xml->params && count($this->_xml->params)) {
            foreach ($this->_xml->params as $tab) {
                $type = N2XmlHelper::getAttribute($tab, 'type');
                if ($type == '') {
                    $type = 'default';
                }

                $class                                                = self::importTab($type);
                $this->_tabs[N2XmlHelper::getAttribute($tab, 'name')] = new $class($this, $tab);

            }
        }
        if ($removeImportPath && $this->xmlFolder) {
            array_pop(N2Form::$importPaths);
        }
    }

    function render($control_name) {
        $this->initTabs(false);
        $this->decorateFormStart();
        foreach ($this->_tabs AS $tabname => $tab) {
            $tab->render($control_name);
        }
        $this->decorateFormEnd();
        if ($this->xmlFolder) {
            array_pop(N2Form::$importPaths);
        }
    }

    function decorateFormStart() {
        echo N2Html::openTag("div", array("class" => "n2-form"));
    }

    function decorateFormEnd() {
        echo N2Html::closeTag("div");
    }

    function loadXMLFile($file) {
        if (!N2Filesystem::existsFile($file)) {
            throw new Exception("xml file not found ('{$file}')! <br /><strong>" . __FILE__ . ":" . __LINE__ . "</strong>");
        }

        if (!function_exists('simplexml_load_string')) {
            throw new Exception(n2_("SimpleXML extension must be enabled in php.ini. Please contact your server administrator to enable it for you."));
        }

        // @fix Warning: simplexml_load_file(): I/O warning : failed to load external entity
        $this->_xml      = simplexml_load_string(file_get_contents($file));
        $this->_xmlfile  = $file;
        $this->xmlFolder = dirname($file);
    }

    function setXML(&$xml) {

        $this->_xml = $xml;
    }

    function getSubFormAjax($tab, $name) {
        $tabsFound = $this->_xml->xpath('//params[@name="' . $tab . '"]');
        if (count($tabsFound) > 0) {
            if ($this->xmlFolder) {
                N2Form::$importPaths[] = $this->xmlFolder;
            }

            $type = N2XmlHelper::getAttribute($tabsFound[0], 'type');
            if ($type == '') {
                $type = 'default';
            }

            $class     = self::importTab($type);
            $tabObject = new $class($this, $tabsFound[0]);
            if (isset($tabObject->_elements[$name])) {
                return $tabObject->_elements[$name];
            }
        }

        return null;
    }

    public function makeTest($key) {
        if ($key != '' && (!defined($key) || !constant($key))) {
            return false;
        }

        return true;
    }

    public static function importTab($type) {
        $class = 'N2Tab' . $type;
        if (!class_exists($class, false)) {
            for ($i = count(N2Form::$importPaths) - 1; $i >= 0; $i--) {
                if (N2Loader::importPath(N2Form::$importPaths[$i] . '/tabs/' . $type)) {
                    break;
                }
            }
        }

        return $class;
    }

    public static function importElement($type) {
        $class = 'N2Element' . $type;
        if (!class_exists($class, false)) {
            for ($i = count(N2Form::$importPaths) - 1; $i >= 0; $i--) {
                if (N2Loader::importPath(N2Form::$importPaths[$i] . '/element/' . $type)) {
                    break;
                }
            }
        }

        return $class;
    }
}

N2Loader::import('libraries.form.form', 'platform');

N2Form::$importPaths[] = dirname(__FILE__);

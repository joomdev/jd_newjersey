<?php

class N2Base {

    public static $isReady = false;

    /**
     * @var array
     */
    public static $applicationInfos = array();

    public static $applications = array();

    /**
     * @var N2ApplicationType
     */
    public static $currentApplicationType;

    private static function init() {
        static $initialized = false;
        if (!$initialized) {
            N2Loader::importAll('libraries.mvc.application');
            N2Loader::importAll('libraries.mvc');
            N2Loader::importAll('libraries.mvc.controllers');
            N2Loader::importAll('libraries.cache');
            N2Loader::importAll('libraries.assets');
            N2Loader::importAll('libraries.google');
            N2Loader::importAll('libraries.assets.css');
            N2Loader::importAll('libraries.assets.js');
            N2Loader::importAll('libraries.assets.less');
            N2Loader::importAll('libraries.assets.google');
            N2Loader::importAll('libraries.uri');
            N2Loader::import('libraries.acl.acl');
            N2Loader::import('libraries.message.message');

            N2Loader::import('libraries.image.helper');

            $initialized   = true;
            self::$isReady = true;
            N2Pluggable::doAction('nextendBaseReady');
        }
    }

    public static function registerApplication($infoPath) {
        /**
         * @var $info N2ApplicationInfo
         */
        $info = require_once($infoPath);
        if (is_object($info)) {
            self::$applicationInfos[$info->getName()] = $info;
        }
    }

    private static function _createApplication($name) {
        if (isset(self::$applicationInfos[$name])) {
            self::init();
            /**
             * @var $nextendApp N2Application
             */
            self::$applications[$name] = self::$applicationInfos[$name]->getInstance();

        } else {
            throw new Exception("Application not available: {$name}");
        }
    }

    /**
     * @param $name
     *
     * @return N2ApplicationInfo
     */
    public static function getApplicationInfo($name) {
        if (!isset(self::$applicationInfos[$name])) {
            return false;
        }

        return self::$applicationInfos[$name];
    }

    public static function getApplications() {
        return self::$applicationInfos;
    }

    /**
     * @param $name
     *
     * @return N2Application
     */
    public static function getApplication($name) {
        if (!isset(self::$applications[$name])) {
            self::_createApplication($name);
            N2Plugin::callPlugin('application', 'applicationLoaded', array($name));
        }

        return self::$applications[$name];
    }

    public static function hasApplication($name) {
        if (isset(self::$applicationInfos[$name])) {
            return true;
        }

        return false;
    }

}

abstract class N2ApplicationInfo {

    private $acl = '';
    private $url = '';

    protected $path = '';
    protected $assetPath = '';

    public function __construct() {

        N2Loader::addPath($this->getName(), $this->getPath());
        $platformPath = N2Filesystem::realpath($this->getPath() . '/../' . N2Platform::getPlatform());
        if ($platformPath) {
            N2Loader::addPath($this->getName() . '.platform', $platformPath);
        }
        $this->loadLocale();

        $filterClass = 'N2' . ucfirst($this->getName()) . 'ApplicationInfoFilter';
        N2Loader::import($filterClass, $this->getName() . '.platform');
        $callable = $filterClass . '::filter';
        if (is_callable($callable)) {
            call_user_func($filterClass . '::filter', $this);
        }

        if (N2Base::$isReady) {
            $this->onNextendBaseReady();
        } else {
            N2Pluggable::addAction('nextendBaseReady', array(
                $this,
                'onNextendBaseReady'
            ));
        }
    }

    public function loadLocale() {
        static $loaded;
        if ($loaded == null) {
            N2Localization::load_plugin_textdomain($this->getPath());
            $loaded = true;
        }
    }

    public function onNextendBaseReady() {
        N2Loader::import('libraries.image.helper');
        N2ImageHelper::addKeyword($this->getPathKey(), $this->getAssetsPath(), $this->getUri());
    }

    public abstract function isPublic();

    public abstract function getLabel();

    public abstract function getName();

    public function getUrl() {
        return $this->url;
    }

    public function getAcl() {
        return $this->acl;
    }

    public function setAcl($acl) {
        $this->acl = $acl;
    }

    public abstract function getInstance();

    public abstract function getPathKey();

    public function getUri() {
        return N2Uri::pathToUri($this->getAssetsPath());
    }

    public function assetsBackend() {

    }

    public function assetsFrontend() {

    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setAssetsPath($path) {
        $this->assetPath = $path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getAssetsPath() {
        return $this->assetPath;
    }

    public function getPath() {
        return $this->path;
    }
}

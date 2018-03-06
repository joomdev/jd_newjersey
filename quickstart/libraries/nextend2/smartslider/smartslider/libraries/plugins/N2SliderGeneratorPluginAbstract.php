<?php

N2Loader::import('libraries.parse.parse');

abstract class N2SliderGeneratorPluginAbstract extends N2PluginBase
{

    public abstract function onGeneratorList(&$group, &$list);
}

class N2GeneratorInfo
{

    public $group, $title, $path, $installed = true, $type = '', $readMore = '', $hasConfiguration = false, $configurationClass = '';

    private $configuration;

    public static function getInstance($group, $title, $path) {
        return new N2GeneratorInfo($group, $title, $path);
    }

    public function __construct($group, $title, $path) {
        $this->group = $group;
        $this->title = $title;
        $this->path  = $path;
    }

    public function getConfiguration() {
        if (!$this->configuration) {
            require_once $this->path . '/../configuration.php';
            $class               = $this->configurationClass;
            $this->configuration = new $class($this);
        }
        return $this->configuration;
    }

    public function setInstalled($installed = true) {
        $this->installed = $installed;
        return $this;
    }

    public function isInstalled() {
        return $this->installed;
    }

    public function setUrl($url) {
        $this->readMore = $url;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setConfiguration($configurationClass) {
        $this->configurationClass = $configurationClass;
        $this->hasConfiguration   = true;
        return $this;
    }

    public function setData($key, $value) {
        $this->{$key} = $value;
        return $this;
    }
}
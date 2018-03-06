<?php

class N2SmartSlider extends N2SmartSliderAbstract
{

    public $_module;

    public function __construct($sliderId, $parameters) {
        parent::__construct($sliderId, $parameters);
        /*
                if (is_object($parameters)) $parameters = intval($parameters->id);

                $sliderid = 0;
                if (is_numeric($parameters)) {
                    $this->_module     = new stdClass();
                    $sliderid          = $parameters;
                    $this->_module->id = $sliderid;
                } elseif (is_array($parameters)) {
                    $module = $parameters[0];
                    $params = $parameters[1];

                    $this->_data = new NextendData();
                    $config      = $params->toArray();
                    $this->_data->loadArray(version_compare(JVERSION, '1.6.0', 'l') || !isset($config['config']) ? $config : $config['config']);

                    $this->setDevices();

                    $this->_module = $module;
                    $sliderid      = $this->_data->get('slider');
                }

                $this->sliderId = intval($sliderid);
        */
    }

    public function initType($type) {

        parent::initType($type);
    }

    public function parseSlider($slider) {
        return $slider;
    }

    public function addCMSFunctions($slider) {
        return JHTML::_('content.prepare', '<div>'.$slider.'</div>', null, 'mod_smartslider');
    }


} 
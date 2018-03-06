<?php

class N2SmartSliderItemHelper {

    public $layer;
    public $data = array(
        'type'   => null,
        'values' => array()
    );

    public function __construct($slide, $type, $layerProperties = array(), $properties = array()) {

        $this->layer = new N2SmartSliderLayerHelper();
        $this->set('type', $type);
        $class      = 'N2SSPluginItemFactory' . $type;
        $item       = new $class();
        $properties = array_merge($item->getValues(), $properties);
        foreach ($properties as $k => $v) {
            $this->setValues($k, $v);
        }
        foreach ($item->getLayerProperties() AS $k => $v) {
            if ($k == 'width' || $k == 'height' || $k == 'top' || $k == 'left') {

                $this->layer->set('desktopportrait' . $k, $v);
            } else {
                $this->layer->set($k, $v);
            }
        }
        $this->layer->set('name', $item->_title . ' layer')
                    ->set('item', $this->data);

        foreach ($layerProperties AS $k => $v) {
            $this->layer->set($k, $v);
        }
        $slide->addLayer($this->layer);
    }

    public function set($key, $value) {
        $this->data[$key] = $value;

        return $this;
    }

    public function setValues($key, $value) {
        $this->data['values'][$key] = $value;

        return $this;
    }

}
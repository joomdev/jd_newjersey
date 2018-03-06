<?php

class N2ElementButton extends N2Element {

    var $_mode = 'hidden';

    var $_tooltip = true;

    function fetchTooltip() {
        if ($this->_tooltip) {
            return parent::fetchTooltip();
        } else {
            return $this->fetchNoTooltip();
        }
    }

    function fetchElement() {

        $attributes = array(
            'class'   => 'n2-form-element-single-button n2-button n2-button-normal n2-radius-s n2-button-l n2-button-grey n2-uc',
            'href'    => '#',
            'onclick' => 'return false;',
            'id'      => $this->_id
        );

        $url = N2XmlHelper::getAttribute($this->_xml, 'url');
        if (!empty($url)) {
            $attributes['href']   = $url;
            $attributes['target'] = N2XmlHelper::getAttribute($this->_xml, 'target');
            unset($attributes['onclick']);
        } else {
            $app = (string)$this->_xml->app;
            if ($app) {
                $queries = (array)$this->_xml->queries;
                $route   = $queries['controller'] . '/' . $queries['action'];
                unset($queries['controller']);
                unset($queries['action']);
                $attributes['href'] = N2Base::getApplication($app)->router->createUrl(array(
                    $route,
                    $queries
                ), true);
                unset($attributes['onclick']);
            }
        }

        return N2Html::tag('a', $attributes, n2_($this->getValue()));
    }
}

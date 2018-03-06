<?php

class N2Box extends N2EmbedWidget implements N2EmbedWidgetInterface
{

    public static $params = array(
        'attributes'         => array(),
        'center'             => null,
        'centerAttributes'   => array(),
        'lt'                 => null,
        'rt'                 => null,
        'lb'                 => null,
        'rb'                 => null,
        'ltAttributes'       => array(),
        'rtAttributes'       => array(),
        'lbAttributes'       => array(),
        'rbAttributes'       => array(),
        'overlay'            => false,
        'placeholderContent' => ''
    );

    public function run($params) {
        $params = array_merge(self::$params, $params);

        $this->addClass($params['attributes'], 'n2-box');
        $this->addClass($params['centerAttributes'], 'n2-box-center');

        $this->addClass($params['ltAttributes'], 'n2-box-lt');
        $this->addClass($params['rtAttributes'], 'n2-box-rt');
        $this->addClass($params['lbAttributes'], 'n2-box-lb');
        $this->addClass($params['rbAttributes'], 'n2-box-rb');


        $this->render($params);
    }

    private function addClass(&$a, $class) {
        if (empty($a['class'])) {
            $a['class'] = '';
        }
        $a['class'] .= ' ' . $class;
    }
} 
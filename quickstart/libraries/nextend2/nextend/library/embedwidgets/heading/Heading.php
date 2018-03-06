<?php

class N2Heading extends N2EmbedWidget implements N2EmbedWidgetInterface
{

    public static $params = array(
        'title'     => '',
        'menu'      => array(),
        'actions'   => array(),
        'snap'      => false,
        'snapClass' => ''
    );

    public function run($params) {
        $params = array_merge(self::$params, $params);

        $this->render($params);
    }

} 
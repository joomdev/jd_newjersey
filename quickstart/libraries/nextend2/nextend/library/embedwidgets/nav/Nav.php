<?php

class N2Nav extends N2EmbedWidget implements N2EmbedWidgetInterface {

    public static $params = array(
        'logoUrl'      => false,
        'logoImageUrl' => false,
        'views'        => array(),
        'actions'      => array()
    );

    public function run($params) {
        $params = array_merge(self::$params, $params);

        $this->render($params);
    }

} 
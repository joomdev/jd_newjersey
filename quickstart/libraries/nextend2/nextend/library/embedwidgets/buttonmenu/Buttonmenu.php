<?php

class N2ButtonMenu extends N2EmbedWidget implements N2EmbedWidgetInterface
{

    public static $params = array(
        'content' => '',
    );

    public function run($params) {
        $params = array_merge(self::$params, $params);
        $this->render($params);
        self::initOnce();
    }

    private static function initOnce() {
        static $init;
        if (!$init) {
            N2JS::addInline('$(".n2-button-menu-open").n2opener();');
            $init = true;
        }
    }
} 
<?php

class N2TopBar extends N2EmbedWidget implements N2EmbedWidgetInterface {

    public static $params = array(
        'menu'         => array(),
        'actions'      => array(),
        'snapClass'    => 'n2-main-top-bar',
        'fixTo'        => true,
        'expert'       => true,
        'notification' => true,
        'hideSidebar'  => false,
        'back'         => false,
        'middle'       => ''
    );

    public function run($params) {
        $params = array_merge(self::$params, $params);

        if (!$params['fixTo']) {
            $params['snapClass'] = '';
        }

        if (!is_array($params['actions'])) {
            $params['actions'] = array();
        }

        if (!$this->viewObject->appType->app->hasExpertMode()) {
            $params['expert'] = false;
        }

        $this->render($params);
    }

} 
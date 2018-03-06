<?php

class N2DefinitionList extends N2EmbedWidget implements N2EmbedWidgetInterface
{

    public static $params = array(
        'class' => 'n2-definition-list',
    );

    public function run($params) {
        $params = array_merge(self::$params, $params);

        $this->render($params);
    }

} 
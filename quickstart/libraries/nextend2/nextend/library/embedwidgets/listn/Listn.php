<?php

/**
 * Class Listn
 *
 * @example $this->widget->init('listn', array(
 *      array(...), array(...), array(...)
 *  )
 * );
 *
 * @see     Listn::$params
 */
class N2Listn extends N2EmbedWidget implements N2EmbedWidgetInterface
{

    /**,
     * @var array
     */
    public static $params = array(
        'ul' => array(
            'htmlOptions' => '',
            'orderable'   => false,
            'link'        => '',
            'iconclass'   => '',
            'title'       => '',
            'actions'     => array(),
            'id'          => false
        )

    );

    public function run($params) {
        $params = array_merge(self::$params, $params);
        $this->render($params);
    }

}
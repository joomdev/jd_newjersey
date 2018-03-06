<?php

class N2SmartsliderBackendGeneratorView extends N2ViewBase {

    public static $sources;

    public static function loadSources() {
        if (!self::$sources) {

            list($groups, $list) = N2SmartsliderGeneratorModel::getGenerators();


            self::$sources = array(
                'available'    => array(),
                'notavailable' => array()
            );
            foreach ($list AS $group => $sources) {
                foreach ($sources AS $type => $info) {
                    /**
                     * @var $info N2GeneratorInfo
                     */
                    if (is_object($info)) {
                        if (!$info->installed) {
                            if (!isset(self::$sources['notavailable'][$group])) {
                                self::$sources['notavailable'][$group] = array();
                            }
                            self::$sources['notavailable'][$group][$type] = $info;
                        } else {
                            if (!isset(self::$sources['available'][$group])) {
                                self::$sources['available'][$group] = array();
                            }
                            self::$sources['available'][$group][$type] = $info;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $list
     */
    function _renderSourceList($list) {
        foreach ($list AS $group => $sources) {
            $this->renderGroupOption($group, $sources);
        }
    }

    public function renderGroupOption($group, $sources) {

        $button  = false;
        $buttons = array();


        foreach ($sources AS $type => $info) {
            /**
             * @var $info N2GeneratorInfo
             */

            if ($info->hasConfiguration) {
                $buttons[$this->appType->router->createUrl(array(
                    "generator/checkConfiguration",
                    array(
                        "sliderid" => N2Request::getInt('sliderid'),
                        "group"    => $group,
                        "type"     => $type
                    )
                ))] = $info->title;
            } elseif (!$info->installed) {
                $button = N2Html::link(n2_("Visit site"), $info->readMore, array(
                    "target" => "_blank",
                    "class"  => "n2-button n2-button-normal n2-button-s n2-radius-s n2-button-grey"
                ));
                break;
            } else {
                $buttons[$this->appType->router->createUrl(array(
                    "generator/createSettings",
                    array(
                        "sliderid" => N2Request::getInt('sliderid'),
                        "group"    => $group,
                        "type"     => $type
                    )
                ))] = $info->title;
            }
        }
        if (!$button && ($count = count($buttons))) {
            if ($count == 1) {
                reset($buttons);
                $key    = key($buttons);
                $button = N2Html::link($buttons[$key], $key, array(
                    "class" => "n2-button n2-button-normal n2-button-s n2-button-blue n2-radius-s n2-h5"
                ));
            } else {
                $keys    = array_keys($buttons);
                $actions = array();
                for ($i = 0; $i < count($keys); $i++) {
                    $actions[] = N2Html::link($buttons[$keys[$i]], $keys[$i], array(
                        'class' => 'n2-h4'
                    ));
                }
                ob_start();
                $this->widget->init("buttonmenu", array(
                    "content" => N2Html::tag('div', array(
                        'class' => 'n2-button-menu'
                    ), N2Html::tag('div', array(
                        'class' => 'n2-button-menu-inner n2-border-radius'
                    ), implode('', $actions)))
                ));
                $buttonMenu = ob_get_clean();
                $button     = N2Html::tag('div', array('class' => 'n2-button n2-button-with-actions n2-button-s n2-button-blue n2-radius-s n2-h5'), N2Html::link($buttons[$keys[0]], $keys[0], array(
                        'class' => 'n2-button-inner'
                    )) . $buttonMenu);
            }
        }


        $this->widget->init("box", array(
            'attributes' => array(
                'class' => 'n2-box-generator',
                'style' => 'background-image: URL(' . N2ImageHelper::fixed(N2Uri::pathToUri(N2Filesystem::translate($info->path . '/../dynamic.png'))) . ');',

            ),
            'placeholderContent' => N2Html::tag('div', array(
                'class' => 'n2-box-placeholder-button'
            ), $button)
        ));
    }
} 
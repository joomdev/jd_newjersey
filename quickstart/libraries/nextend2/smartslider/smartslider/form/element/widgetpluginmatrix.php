<?php

N2Loader::import('libraries.form.element.subpluginmatrix');

class N2ElementWidgetPluginMatrix extends N2ElementSubPluginMatrix
{

    private $group = '';

    private function getWidgetClass($key) {
        return 'N2SSPluginWidget' . $this->getGroup() . $key;
    }

    private function getGroup() {
        if (empty($this->group)) {
            $this->group = N2XmlHelper::getAttribute($this->_xml, 'method');
        }
        return $this->group;
    }

    function fetchElement() {
        return parent::fetchElement();
    }

    function loadList() {
        parent::loadList();
        $this->_list = array_merge(array('disabled' => $this->_form->xmlFolder . '/'), $this->_list);
    }

    function renderForm() {
        $value = $this->getValue();
        if ($value == 'disabled') {
            return '';
        } else {
            $class = $this->getWidgetClass($value);
            if (class_exists($class, false)) {
                $this->_form->fillDefault(call_user_func(array(
                    $class,
                    'getDefaults'
                )));
            }

            return parent::renderForm();
        }
    }

    function getImage($path, $key) {
        return N2Uri::pathToUri(N2Filesystem::translate($path . $key . '.png'));
    }

    function getOptionHtml($path, $k) {
        return N2Html::tag('div', array(
            'class' => 'n2-subform-image-option n2-subform-image-option-simple ' . $this->isActive($k)
        ), N2Html::tag('div', array(
            'class' => 'n2-subform-image-element',
            'style' => 'background-image: URL(' . $this->getImage($path, $k) . ');'
        )));
    }

    protected function getClass() {
        return 'n2-subform-2-rows';
    }
}
<?php

N2Loader::import('libraries.form.element.subformImage');

class N2ElementSubPluginMatrix extends N2ElementSubformImage
{

    var $_list = null;

    function renderContainer() {
        return '';
    }

    function renderButton() {
        return '';
    }

    function getOptions() {
        if ($this->_list == null) {
            $this->loadList();
        }
        $list = array_keys($this->_list);
        //sort($list);
        return $list;
    }

    function getSubFormFolder($value) {
        if ($this->_list == null) {
            $this->loadList();
        }
        if (!isset($this->_list[$value])) list($value) = array_keys($this->_list);
        return $this->_list[$value];
    }

    function loadList() {
        $this->_list = array();
        N2Plugin::callPlugin(N2XmlHelper::getAttribute($this->_xml, 'group'), 'on' . N2XmlHelper::getAttribute($this->_xml, 'method') . 'List', array(&$this->_list));
    }

    protected function getClass() {
        return 'n2-small';
    }

}
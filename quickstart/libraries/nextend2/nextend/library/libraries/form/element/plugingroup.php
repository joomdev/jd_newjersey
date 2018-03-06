<?php

N2Loader::import('libraries.form.element.subform');

class N2ElementPlugingroup extends N2ElementSubform
{

    var $_group = null;

    var $_list = null;

    function getOptions() {
        $this->loadList();
        $list = array();
        foreach ($this->_list AS $k) {
            foreach ($k AS $kk => $vv) {
                $list[] = $kk;
            }
        }
        sort($list);
        if (!in_array($this->getValue(), $list)) {
            $this->setValue($list[0]);
        }
        return $list;
    }

    function setOptions($options) {

        foreach ($this->_group AS $gk => $gv) {
            $group = $this->_xml->addChild('optgroup', '');
            $group->addAttribute('label', htmlspecialchars($gv));
            foreach ($this->_list[$gk] AS $k => $v) {
                $group->addChild('option', htmlspecialchars($v[0]))->addAttribute('value', $k);
            }
        }
    }

    function getSubFormfolder($value) {
        $this->loadList();
        $v = explode('_', $value);
        return $this->_list[$v[0]][$value][1];
    }

    function onRender() {
        $php = N2XmlHelper::getAttribute($this->_xml, 'php');
        if ($php) {
            $v = explode('_', $this->getValue());
            require_once($this->_list[$v[0]][$this->getValue()][1] . N2XmlHelper::getAttribute($this->_xml, 'php'));

            $class     = 'N2Generator' . $this->getValue();
            $generator = new $class($this->_form);
            $generator->initAdmin();
        }
    }

    function loadList() {
        if ($this->_list == null) {
            $this->_group = array();
            $this->_list  = array();
            N2Plugin::callPlugin(N2XmlHelper::getAttribute($this->_xml, 'plugingroup'), N2XmlHelper::getAttribute($this->_xml, 'method'), array(
                &$this->_group,
                &$this->_list
            ));

            $v = explode('_', $this->getValue());
            if (!isset($this->_list[$v[0]][$this->getValue()])) {
                $keys = array_keys($this->_list);
                $ks   = array_keys($this->_list[$keys[0]]);
                $this->setValue($this->_list[$keys[0]][$ks[0]]);
            }
        }
    }

}
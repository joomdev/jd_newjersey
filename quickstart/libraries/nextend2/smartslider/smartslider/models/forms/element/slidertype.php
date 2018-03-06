<?php
N2Loader::import('libraries.form.element.subformImage');

class N2ElementSliderType extends N2ElementSubformImage
{

    var $_list = null;

    function renderSelector() {

        N2JS::addInline('
        new N2Classes.FormElementSliderType(
              "' . $this->_id . '"
            );
        ');

        return parent::renderSelector();
    }

    function getOptions() {
        if ($this->_list == null) {
            $this->loadList();
        }
        $list = array_keys($this->_list);
        return $list;
    }

    function getSubFormfolder($value) {
        if ($this->_list == null) {
            $this->loadList();
        }
        if (!isset($this->_list[$value])) list($value) = array_keys($this->_list);
        return $this->_list[$value];
    }

    function loadList() {
        $_list = array();
        N2Plugin::callPlugin('sstype', 'onTypeList', array(
            &$_list,
            &$this->labels
        ));

        $this->_list = array();

        /**
         * We have to force the proper order in the slider types
         */
        if (isset($_list['simple'])) {
            $this->_list['simple'] = $_list['simple'];
            unset($_list['simple']);
        }

        if (isset($_list['carousel'])) {
            $this->_list['carousel'] = $_list['carousel'];
            unset($_list['carousel']);
        }

        if (isset($_list['showcase'])) {
            $this->_list['showcase'] = $_list['showcase'];
            unset($_list['showcase']);
        }

        if (isset($_list['horizontalaccordion'])) {
            $this->_list['horizontalaccordion'] = $_list['horizontalaccordion'];
            unset($_list['horizontalaccordion']);
        }

        if (isset($_list['verticalaccordion'])) {
            $this->_list['verticalaccordion'] = $_list['verticalaccordion'];
            unset($_list['verticalaccordion']);
        }

        $this->_list += $_list;
    }

}
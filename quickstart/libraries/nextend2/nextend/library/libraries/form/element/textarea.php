<?php

class N2ElementTextarea extends N2Element
{

    function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementText("' . $this->_id . '");');

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-textarea n2-border-radius',
            'style' => N2XmlHelper::getAttribute($this->_xml, 'style')
        ), N2Html::tag('textarea', array(
            'id'           => $this->_id,
            'name'         => $this->_inputname,
            'class'        => 'n2-h5',
            'autocomplete' => 'off',
            'style'        => N2XmlHelper::getAttribute($this->_xml, 'style2')
        ), $this->_form->get($this->_name, $this->_default)));
    }
}

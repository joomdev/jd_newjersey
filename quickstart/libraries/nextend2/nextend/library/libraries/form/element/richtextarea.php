<?php

class N2ElementRichTextarea extends N2Element
{

    function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementRichText("' . $this->_id . '");');

        $tools = array(
            N2Html::tag('div', array('class' => 'n2-textarea-rich-bold'), N2Html::tag('I', array('class' => 'n2-i n2-it n2-i-bold'))),
            N2Html::tag('div', array('class' => 'n2-textarea-rich-italic'), N2Html::tag('I', array('class' => 'n2-i n2-it n2-i-italic'))),
            N2Html::tag('div', array('class' => 'n2-textarea-rich-link'), N2Html::tag('I', array('class' => 'n2-i n2-it n2-i-link'))),
            //N2Html::tag('div', array('class' => 'n2-textarea-rich-list'), N2Html::tag('I', array('class' => 'n2-i n2-it n2-i-list')))
        );
        $rich  = N2Html::tag('div', array('class' => 'n2-textarea-rich'), implode('', $tools));

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-textarea n2-form-element-rich-textarea n2-border-radius',
            'style' => N2XmlHelper::getAttribute($this->_xml, 'style')
        ), $rich . N2Html::tag('textarea', array(
                'id'           => $this->_id,
                'name'         => $this->_inputname,
                'class'        => 'n2 - h5',
                'autocomplete' => 'off',
                'style'        => N2XmlHelper::getAttribute($this->_xml, 'style2')
            ), $this->_form->get($this->_name, $this->_default)));
    }
}

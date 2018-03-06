<?php
N2Loader::import('libraries.form.element.list');

class N2ElementSubform extends N2ElementList
{

    function fetchElement() {

        $options = $this->getOptions();

        if (count($options) === 0) return 'No sub form exists...';

        if (!in_array($this->getValue(), $options)) {
            $this->setValue($options[0]);
        }

        $this->setOptions($options);

        $html = $this->renderSelector();

        $html .= $this->renderButton();

        $html .= $this->renderContainer();

        return N2Html::tag("div", array(
            "class" => "n2-subform " . N2XmlHelper::getAttribute($this->_xml, 'class'),
            "style" => N2XmlHelper::getAttribute($this->_xml, 'style'),
        ), $html);
    }

    function setOptions($options) {
        for ($i = 0; $i < count($options); $i++) {
            $this->_xml->addChild('option', htmlspecialchars(ucfirst($options[$i])))->addAttribute('value', $options[$i]);
        }
    }

    function renderSelector() {
        return parent::fetchElement();
    }

    function renderButton() {
        /*
        N2JS::addInline("
            $('#{$this->_id}_button').on('click', function(e){
                e.preventDefault();
                NextendLightbox.open('nextend-subform-{$this->_id}');
            });
        ");
        */
        return '<a id="' . $this->_id . '_button" class="n2-button n2-button-normal n2-button-s n2-radius-s n2-button-grey n2-uc" href="#">' . n2_('Configure') . '</a>';
    }

    function renderContainer() {
        ob_start();
        N2JS::addInline('
        new N2Classes.FormElementSubform(
              "' . $this->_id . '",
              "nextend-' . $this->_name . '-panel",
              "' . $this->_tab->_name . '",
              "' . $this->getValue() . '"
            );
        ');

        $widget = $this->_form->appType->getLayout()->widget;
        /*
        $widget->init("lightbox", array(
            'id'           => 'nextend-subform-' . $this->_id,
            'logoImageUrl' => $this->_form->appType->app->getLogo(),
            'content'      => $this->renderForm()
        ));
        */
        $html = ob_get_contents();

        return $html;
    }

    function getOptions() {
        return N2Filesystem::folders($this->getSubFormfolder(''));
    }

    function initAjax($control_name) {
        $this->control_name = $control_name;
        $this->_default     = '';
        $this->_name        = N2XmlHelper::getAttribute($this->_xml, 'name');
        $this->_id          = $this->generateId($control_name . $this->_name);
        $this->_inputname   = $control_name . '[' . $this->_name . ']';
    }

    function renderForm() {
        $file  = N2XmlHelper::getAttribute($this->_xml, 'file');

        $form = new N2Form($this->_form->appType);

        $form->_data = &$this->_form->_data;

        $form->loadXMLFile($this->getSubFormfolder($this->getValue()) . $file);

        ob_start();
        $this->onRender();

        $form->render($this->control_name);
        return ob_get_clean();

    }

    function onRender() {

    }

    function getSubFormFolder($value) {
        if ($value != '') $value .= DIRECTORY_SEPARATOR;
        return $this->_form->xmlFolder . DIRECTORY_SEPARATOR . N2XmlHelper::getAttribute($this->_xml, 'folder') . DIRECTORY_SEPARATOR . $value;
    }
}
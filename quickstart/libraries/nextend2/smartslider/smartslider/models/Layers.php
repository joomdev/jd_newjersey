<?php

class N2SmartsliderLayersModel extends N2Model {

    function renderForm($data = array()) {

        N2Pluggable::addAction('N2TabTabbedSidebarslide-editor-settings', array(
            $this,
            'extendSlideEditorSettings'
        ));

        $configurationXmlFile = dirname(__FILE__) . '/forms/layer.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form();
        $form->loadArray($data);

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('layer');
    }

    /**
     * @param $tab N2TabTabbedSidebar
     */
    public function extendSlideEditorSettings($tab) {

        $xml = dirname(__FILE__) . '/forms/group.xml';
        if (N2Filesystem::existsFile($xml)) {
            $tab->addTabXML($xml);
        }

    }

} 
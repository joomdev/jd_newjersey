<?php

N2Loader::import('libraries.form.form');

class N2SmartsliderItemModel extends N2Model {

    function renderForm($type, $configurationXmlFile, $data = array()) {

        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));
        $form->loadArray($data);

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('item_' . $type);
    }

} 
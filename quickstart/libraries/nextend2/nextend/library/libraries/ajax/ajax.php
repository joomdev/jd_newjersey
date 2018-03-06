<?php

class N2Ajax
{

    public function parseRequest() {
        $mode = N2Request::getVar('mode');
        switch ($mode) {
            case 'pluginmethod':
                $this->pluginmethod();
                break;
            default:
                return;
                break;
        }
    }

    public function subform($appType, $configurationXmlFile, $values, $control_name, $name) {

        if (N2Filesystem::fileexists($configurationXmlFile)) {

            N2Loader::import('libraries.form.form');
            $form = new N2Form($appType);


            $form->loadArray($values);
            //$subformValue = array();
            //$subformValue[N2Post::getVar('name')] = N2Post::getVar('value');
            //$form->loadArray($subformValue);
            $form->loadXMLFile($configurationXmlFile);
            n2_ob_end_clean_all(); // To clear the output of the platform
            ob_start();
            $subform = $form->getSubFormAjax(N2Post::getVar('tab'), $name);
            $subform->initAjax($control_name);
            echo $subform->renderForm();

            //echo N2AssetsManager::generateAjaxCSS();

            $scripts = N2AssetsManager::generateAjaxJS();
            $html    = ob_get_clean();

            $response = array(
                'html'    => $html,
                'scripts' => $scripts
            );
        } else {
            $response = array('error' => 'Configuration file not found: ' . $configurationXmlFile);
        }

        return $response;
    }

}
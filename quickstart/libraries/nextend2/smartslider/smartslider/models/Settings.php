<?php

class N2SmartsliderSettingsModel extends N2Model {

    public function form($xml) {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $data = array();
        switch ($xml) {
            case 'joomla':
                $data = N2SmartSliderJoomlaSettings::getAll();
                break;
            default:
                $data = N2SmartSliderSettings::getAll();
                break;
        }
        $this->render(dirname(__FILE__) . '/forms/settings/' . $xml . '.xml', $data);
    }

    public function render($xmlpath, $data) {
        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));

        $form->loadArray($data);

        $form->loadXMLFile($xmlpath);

        echo $form->render('settings');

        N2JS::addFirstCode('
            new NextendForm("smartslider-form", ' . json_encode($form->_data) . ', null, "' . N2Filesystem::toLinux(N2Filesystem::pathToRelativePath($xmlpath)) . '", "settings", "' . N2Uri::ajaxUri('nextend', 'smartslider') . '");
        ');
    }

    public function save() {
        $namespace = N2Request::getCmd('namespace', 'default');
        $settings  = N2Request::getVar('settings');
        if ($namespace && $settings) {
            if ($namespace == 'default') $namespace = 'settings';
            if ($namespace == 'font' && N2Request::getInt('sliderid')) {
                $namespace .= N2Request::getInt('sliderid');
                self::markChanged(N2Request::getInt('sliderid'));
            }

            N2SmartSliderSettings::store($namespace, json_encode($settings));
        }

        return true;
    }

    public static function markChanged($id) {
        N2SmartSliderHelper::getInstance()
                           ->setSliderChanged($id, 1);
    }

    public function saveDefaults($defaults) {
        if (!empty($defaults)) {
            foreach ($defaults AS $referenceKey => $value) {
                N2StorageSectionAdmin::set('smartslider', 'default', $referenceKey, $value);
            }
        }

        return true;
    }

} 
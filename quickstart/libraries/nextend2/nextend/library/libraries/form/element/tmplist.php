<?php

N2Loader::import('libraries.form.element.list');

class N2ElementTmpList extends N2ElementList
{

    function fetchElement() {
        $dir             = N2Platform::getPublicDir();
        $extension       = N2XmlHelper::getAttribute($this->_xml, 'extension');
        $files           = scandir($dir);
        $validated_files = array();

        foreach ($files as $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == $extension) {
                $validated_files[] = $file;
            }
        }

        $this->_xml->addChild('option', n2_('Choose a file to import'))->addAttribute('value', '');

        foreach ($validated_files AS $f) {
            $this->_xml->addChild('option', $f)->addAttribute('value', $f);
        }

        return parent::fetchElement();
    }
}

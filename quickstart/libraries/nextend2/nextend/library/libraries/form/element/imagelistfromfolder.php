<?php
N2Loader::import('libraries.form.element.imagelist');

class N2ElementImageListFromFolder extends N2ElementImageList
{

    function setFolder() {
        $folder = N2XmlHelper::getAttribute($this->_xml, 'folder');
        if (!empty($folder) && $folder[0] != '$') {
            $folder = dirname($this->_form->_xmlfile) . '/' . $folder . '/';
        } else {
            $folder = N2ImageHelper::fixed($folder, true);
        }
        $this->_folder = N2Filesystem::translate($folder);
    }
}
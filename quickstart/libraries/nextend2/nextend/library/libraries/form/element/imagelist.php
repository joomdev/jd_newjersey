<?php
N2Loader::import('libraries.form.element.radio');

class N2ElementImageList extends N2ElementRadio
{

    function fetchElement() {


        $this->setfolder();
        $files = N2Filesystem::files($this->_folder);
        if (N2XmlHelper::getAttribute($this->_xml, 'required') == '') {
            $this->_xml->addChild('option', n2_('No image'))
                       ->addAttribute('value', -1);
        }
        for ($i = 0; $i < count($files); $i++) {
            $ext = pathinfo($files[$i], PATHINFO_EXTENSION);
            if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'svg' || $ext == 'gif') {
                $this->_xml->addChild('option', htmlspecialchars(ucfirst($files[$i])))
                           ->addAttribute('value', N2Filesystem::toLinux(N2Filesystem::pathToRelativePath($this->_folder . $files[$i])));
            }
        }

        $html = N2Html::openTag("div", array(
            'class' => 'n2-imagelist',
            'style' => N2XmlHelper::getAttribute($this->_xml, 'style')
        ));

        $html .= parent::fetchElement();
        $html .= N2Html::closeTag('div');

        return $html;
    }

    function generateOptions(&$xml) {
        $this->values = array();
        $html         = '';
        foreach ($xml->option AS $option) {
            $v     = N2XmlHelper::getAttribute($option, 'value');
            $image = N2Uri::pathToUri($v);

            $selected = $this->isSelected($v);

            if ($v != -1) {
                $value          = $this->parseValue($image);
                $this->values[] = $value;
                $html .= N2Html::openTag("div", array("class" => "n2-radio-option n2-imagelist-option" . ($selected ? ' n2-active' : '')));

                $ext = pathinfo($image, PATHINFO_EXTENSION);
                if ($ext == 'svg') {
                    $image = 'data:image/svg+xml;base64,' . n2_base64_encode(N2Filesystem::readFile(N2Filesystem::getBasePath() . $v));
                }

                $html .= N2Html::image($image, (string)$option, array('data-image' => $value));
                $html .= N2Html::closeTag("div");
            } else {
                $this->values[] = -1;
                $html .= N2Html::tag("div", array("class" => "n2-radio-option" . ($selected ? ' n2-active' : '')), ((string)$option));
            }
        }

        return $html;
    }

    function parseValue($image) {
        return N2ImageHelper::dynamic($image);
    }

    function setFolder() {
        $assetsDir     = N2XmlHelper::getAttribute($this->_xml, 'assetsdir');
        $this->_folder = str_replace(DIRECTORY_SEPARATOR, '/', (defined($assetsDir) ? constant($assetsDir) : N2LIBRARYASSETS)) . '/' . N2XmlHelper::getAttribute($this->_xml, 'folder') . '/';
    }

    function isSelected($value) {
        if (basename($value) == basename($this->getValue())) {
            return true;
        }
        return false;
    }
}

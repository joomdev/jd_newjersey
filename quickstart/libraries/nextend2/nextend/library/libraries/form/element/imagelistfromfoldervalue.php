<?php
N2Loader::import('libraries.form.element.imagelistfromfolder');

class N2ElementImageListFromFolderValue extends N2ElementImageListFromFolder
{

    function generateOptions(&$xml) {
        $this->values = array();
        $html         = '';
        foreach ($xml->option AS $option) {
            $v     = N2XmlHelper::getAttribute($option, 'value');
            $image = N2Uri::pathToUri($v);

            $selected = $this->isSelected($this->parseValue($v));

            if ($v != -1) {
                $this->values[] = $this->parseValue($image);
                $html .= N2Html::openTag("div", array("class" => "n2-radio-option n2-imagelist-option" . ($selected ? ' n2-active' : '')));
                $html .= N2Html::image($image, (string)$option);
                $html .= N2Html::closeTag("div");
            } else {
                $this->values[] = -1;
                $html .= N2Html::tag("div", array("class" => "n2-radio-option" . ($selected ? ' n2-active' : '')), ((string)$option));
            }
        }

        return $html;
    }

    function parseValue($image) {
        return pathinfo($image, PATHINFO_FILENAME);
    }
}
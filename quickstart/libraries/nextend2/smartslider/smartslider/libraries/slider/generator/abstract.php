<?php

abstract class N2GeneratorAbstract {

    /** @var  N2GeneratorInfo */
    protected $info;

    protected $data;

    public function __construct($info, $data) {
        $this->info = $info;
        $this->data = $data;
    }

    public final function getData($slides, $startIndex, $group) {
        $data       = array();
        $linearData = $this->_getData($slides * $group, $startIndex - 1);
        $keys       = array();
        for ($i = 0; $i < count($linearData); $i++) {
            $keys = array_merge($keys, array_keys($linearData[$i]));
        }

        $columns = array_fill_keys($keys, '');

        for ($i = 0; $i < count($linearData); $i++) {
            $firstIndex = intval($i / $group);
            if (!isset($data[$firstIndex])) {
                $data[$firstIndex] = array();
            }
            $data[$firstIndex][$i % $group] = array_merge($columns, $linearData[$i]);
        }

        if (count($data) && count($data[count($data) - 1]) != $group) {
            if (count($data) - 1 == 0 && count($data[count($data) - 1]) > 0) {
                for ($i = 0; count($data[0]) < $group; $i++) {
                    $data[0][] = $data[0][$i];
                }
            } else {
                array_pop($data);
            }
        }
        return $data;
    }

    protected abstract function _getData($count, $startIndex);

    function makeClickableLinks($s) {
        return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
    }

    protected function getIDs() {
        return array_map('intval', explode("\n", str_replace(array(
            "\r\n",
            "\n\r",
            "\r"
        ), "\n", $this->data->get('ids'))));
    }

    public function filterName($name) {
        return $name;
    }

    public function hash($key) {
        return md5($key);
    }

    public static function cacheKey($params) {
        return '';
    }
}
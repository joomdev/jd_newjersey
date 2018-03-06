<?php
N2Loader::import('libraries.slider.generator.abstract', 'smartslider');
require_once(JPATH_SITE . '/components/com_content/helpers/route.php');
require_once(dirname(__FILE__) . '/../../imagefallback.php');

class N2GeneratorJoomlaContentArticle extends N2GeneratorAbstract {

    public function datify($date, $format) {
        $result = date($format, strtotime($date));
        return $result;
    }

    private function translate($from, $translate) {
        if (!empty($translate) && !empty($from)) {
            foreach ($translate AS $key => $value) {
                $from = str_replace($key, $value, $from);
            }
        }
        return $from;
    }

    protected function _getData($count, $startIndex) {
        N2Loader::import('nextend.database.database');
        $db = JFactory::getDbo();

        $categories = array_map('intval', explode('||', $this->data->get('sourcecategories', '')));
        $tags       = array_map('intval', explode('||', $this->data->get('sourcetags', '0')));

        $query = 'SELECT ';
        $query .= 'con.id, ';
        $query .= 'con.title, ';
        $query .= 'con.alias, ';
        $query .= 'con.introtext, ';
        $query .= 'con.fulltext, ';
        $query .= 'con.created, ';
        $query .= 'con.catid, ';
        $query .= 'cat.title AS cat_title, ';
        $query .= 'cat.alias AS cat_alias, ';
        $query .= 'con.created_by, con.state, ';
        $query .= 'usr.name AS created_by_alias, ';
        $query .= 'con.images, ';
        $query .= 'con.urls, ';
        $query .= 'con.attribs ';

        $query .= 'FROM #__content AS con ';

        $query .= 'LEFT JOIN #__users AS usr ON usr.id = con.created_by ';

        $query .= 'LEFT JOIN #__categories AS cat ON cat.id = con.catid ';

        $query .= 'LEFT JOIN #__content_frontpage AS cf ON cf.content_id = con.id ';

        $jNow  = JFactory::getDate();
        $now   = $jNow->toSql();
        $where = array(
            'con.state = 1 ',
            "(con.publish_up = '0000-00-00 00:00:00' OR con.publish_up < '" . $now . "') AND (con.publish_down = '0000-00-00 00:00:00' OR con.publish_down > '" . $now . "') "
        );

        if (!in_array(0, $categories)) {
            $where[] = 'con.catid IN (' . implode(',', $categories) . ') ';
        }

        if (!in_array(0, $tags)) {
            $where[] = 'con.id IN (SELECT content_item_id FROM #__contentitem_tag_map WHERE type_alias = \'com_content.article\' AND tag_id IN (' . implode(',', $tags) . ')) ';
        }

        $sourceUserID = intval($this->data->get('sourceuserid', ''));
        if ($sourceUserID) {
            $where[] = 'con.created_by = ' . $sourceUserID . ' ';
        }

        switch ($this->data->get('sourcefeatured', 0)) {
            case 1:
                $where[] = 'con.featured = 1 ';
                break;
            case -1:
                $where[] = 'con.featured = 0 ';
                break;
        }
        $language = explode(",", $this->data->get('sourcelanguage', '*'));
        if (!empty($language[0]) && $language[0] != '*') {
            $where[] = 'con.language IN (' . implode(",", $db->quote($language)) . ') ';
        }

        $articleIds = $this->data->get('sourcearticleids', '');
        if (!empty($articleIds)) {
            $where[] = 'con.id IN (' . $articleIds . ') ';
        }

        $articleIdsExcluded = $this->data->get('sourcearticleidsexcluded', '');
        if (!empty($articleIdsExcluded)) {
            $where[] = 'con.id NOT IN (' . $articleIdsExcluded . ') ';
        }

        $accessLevels = explode('||', $this->data->get('sourceaccesslevels', '*'));
        if (!in_array(0, $accessLevels)) {
            $where[] = 'con.access IN (' . implode(",", $accessLevels) . ')';
        }

        if (count($where) > 0) {
            $query .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $order = N2Parse::parse($this->data->get('joomlaorder', 'con.title|*|asc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count;

        $db->setQuery($query);
        $result = $db->loadAssocList();

        $sourceTranslate = $this->data->get('sourcetranslatedate', '');
        $translateValue  = explode('||', $sourceTranslate);
        $translate       = array();
        if ($sourceTranslate != 'January->January||February->February||March->March' && !empty($translateValue)) {
            foreach ($translateValue AS $tv) {
                $translateArray = explode('->', $tv);
                if (!empty($translateArray) && count($translateArray) == 2) {
                    $translate[$translateArray[0]] = $translateArray[1];
                }
            }
        }

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('content');
        $uri = N2Uri::getBaseUri();

        $data    = array();
        $idArray = array();
        for ($i = 0; $i < count($result); $i++) {
            $idArray[$i] = $result[$i]['id'];
            $r           = Array(
                'title' => $result[$i]['title']
            );

            $article       = new stdClass();
            $article->text = N2SmartSlider::removeShortcode($result[$i]['introtext']);
            $_p            = array();
            $dispatcher->trigger('onContentPrepare', array(
                'com_smartslider3',
                &$article,
                &$_p,
                0
            ));
            if (!empty($article->text)) {
                $r['description'] = $article->text;
            }

            $article->text = $result[$i]['fulltext'];
            $_p            = array();
            $dispatcher->trigger('onContentPrepare', array(
                'com_smartslider3',
                &$article,
                &$_p,
                0
            ));
            if (!empty($article->text)) {
                $result[$i]['fulltext'] = $article->text;
                if (!isset($r['description'])) {
                    $r['description'] = $result[$i]['fulltext'];
                } else {
                    $r['fulltext'] = $result[$i]['fulltext'];
                }
            }

            $images = (array)json_decode($result[$i]['images'], true);

            $r['image'] = $r['thumbnail'] = NextendImageFallBack::fallback($uri . "/", array(
                @$images['image_intro'],
                @$images['image_fulltext']
            ), array(
                @$r['description']
            ));

            $r += array(
                'url'               => ContentHelperRoute::getArticleRoute($result[$i]['id'] . ':' . $result[$i]['alias'], $result[$i]['catid'] . ':' . $result[$i]['cat_alias']),
                'url_label'         => sprintf(n2_('View %s'), n2_('article')),
                'category_list_url' => 'index.php?option=com_content&view=category&id=' . $result[$i]['catid'],
                'category_blog_url' => 'index.php?option=com_content&view=category&layout=blog&id=' . $result[$i]['catid'],
                'fulltext_image'    => !empty($images['image_fulltext']) ? N2ImageHelper::dynamic($uri . "/" . $images['image_fulltext']) : '',
                'category_title'    => $result[$i]['cat_title'],
                'created_by'        => $result[$i]['created_by_alias'],
                'id'                => $result[$i]['id'],
                'created_date'      => $this->translate($this->datify($result[$i]['created'], $this->data->get('sourcedateformat', 'Y-m-d')), $translate),
                'created_time'      => $this->translate($this->datify($result[$i]['created'], $this->data->get('sourcetimeformat', 'G:i')), $translate)
            );
			
			if(!empty($images)){
				$r += $images;
			}

            $urls = json_decode($result[$i]['urls'], true);
            if(!empty($urls['urla'])){
                $r['urla']      = $urls['urla'];
                $r['urlatext']  = $urls['urlatext'];
            }
            if(!empty($urls['urlb'])){
                $r['urlb']      = $urls['urlb'];
                $r['urlbtext']  = $urls['urlbtext'];
            }
            if(!empty($urls['urlc'])){
                $r['urlc']      = $urls['urlc'];
                $r['urlctext']  = $urls['urlctext'];
            }
            
            $attribs = (array)json_decode($result[$i]['attribs'], true);
            $r['spfeatured_image'] = '';
            if(array_key_exists("spfeatured_image", $attribs) && !empty($attribs['spfeatured_image'])){
                $r['spfeatured_image'] = N2ImageHelper::dynamic($uri . "/" . $attribs['spfeatured_image']);
            }
            if(array_key_exists("gallery", $attribs) && !empty($attribs['gallery'])){
                $gallery = (array)json_decode($attribs['gallery'],true);
                for ($g=0; $g < count($gallery["gallery_images"]); $g++) { 
                    $r['spgallery_'.$g] = N2ImageHelper::dynamic($uri . "/" . $gallery["gallery_images"][$g]);
                }
            }

            $data[] = $r;
        }

        if ($this->data->get('sourcefields', 0)) {
            $query = "SELECT fv.value, fv.item_id, f.title, f.type FROM #__fields_values AS fv LEFT JOIN #__fields AS f ON fv.field_id = f.id WHERE fv.item_id IN (" . implode(',', $idArray) . ")";
            $db->setQuery($query);
            $result    = $db->loadAssocList();
            $AllResult = array();
            foreach ($result AS $r) {
                if ($r['type'] == 'media') {
                    $r['value'] = N2ImageHelper::dynamic($uri . "/" . $r["value"]);
                }
                $r['title'] = htmlentities($r['title']);
                $keynum = 2;
                while(isset($AllResult[$r['item_id']][$r['title']])){
                     $r['title'] = $r['title'] . $keynum;
                     $keynum++;
                }
                $AllResult[$r['item_id']][$r['title']] = $r['value'];
            }

            for ($i = 0; $i < count($data); $i++) {
                if (isset($AllResult[$data[$i]['id']])) {
                    foreach ($AllResult[$data[$i]['id']] as $key => $value) {
                        $key            = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]*/', '', $key);
                        $data[$i][$key] = $value;
                    }
                }
            }
        }
        return $data;
    }

}
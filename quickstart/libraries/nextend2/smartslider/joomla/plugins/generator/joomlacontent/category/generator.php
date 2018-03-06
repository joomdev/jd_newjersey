<?php
N2Loader::import('libraries.slider.generator.abstract', 'smartslider');
require_once(dirname(__FILE__) . '/../../imagefallback.php');

class N2GeneratorJoomlaContentCategory extends N2GeneratorAbstract
{

    protected function _getData($count, $startIndex) {
        $model = new N2Model('categories');

        $category = array_map('intval', explode('||', $this->data->get('sourcecategory', '')));
        $tags     = array_map('intval', explode('||', $this->data->get('sourcetags', '0')));

        $query = 'SELECT ';
        $query .= 'cat.id, ';
        $query .= 'cat.title, ';
        $query .= 'cat.alias, ';
        $query .= 'cat.description, ';
        $query .= 'cat.params, ';
        $query .= 'cat_parent.id AS parent_id, ';
        $query .= 'cat_parent.title AS parent_title ';

        $query .= 'FROM #__categories AS cat ';

        $query .= 'LEFT JOIN #__categories AS cat_parent ON cat_parent.id = cat.parent_id ';

        $where = array(
            'cat.parent_id IN (' . implode(',', $category) . ') ',
            'cat.published = 1 '
        );

        if (!in_array(0, $tags)) {
            $where[] = 'cat.id IN (SELECT content_item_id FROM #__contentitem_tag_map WHERE type_alias = \'com_content.category\'  AND tag_id IN (' . implode(',', $tags) . ')) ';
        }

        $language = $this->data->get('sourcelanguage', '*');
        if ($language) {
            $where[] = 'cat.language = ' . $model->db->quote($language) . ' ';
        }

        if (count($where) > 0) {
            $query .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $order = N2Parse::parse($this->data->get('joomlacartegoryorder', 'cat.title|*|asc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count . ' ';

        $result = $model->db->queryAll($query);

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('content');
        $uri = N2Uri::getBaseUri();
        $uri .= "/";

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $r = Array(
                'title' => $result[$i]['title']
            );

            $article       = new stdClass();
            $article->text = N2SmartSlider::removeShortcode($result[$i]['description']);
            $_p            = array();
            $dispatcher->trigger('onContentPrepare', array(
                'com_smartslider3',
                &$article,
                &$_p,
                0
            ));
            if (!empty($article->text)) {
                $r['description'] = $article->text;
            } else {
                $r['description'] = '';
            }
            $params = (array)json_decode($result[$i]['params'], true);

            $r['image'] = $r['thumbnail'] = NextendImageFallBack::fallback($uri, array(@$params['image']), array($r['description']));

            $r += array(
                'url'       => 'index.php?option=com_content&view=category&id=' . $result[$i]['id'],
                'url_label' => sprintf(n2_('View %s'), n2_('category')),
                'url_blog'  => 'index.php?option=com_content&view=category&layout=blog&id=' . $result[$i]['id']
            );

            if ($result[$i]['parent_title'] != 'ROOT') {
                $r += array(
                    'parent_title'    => $result[$i]['parent_title'],
                    'parent_url'      => 'index.php?option=com_content&view=category&id=' . $result[$i]['parent_id'],
                    'parent_url_blog' => 'index.php?option=com_content&view=category&layout=blog&id=' . $result[$i]['parent_id']
                );
            } else {
                $r += array(
                    'parent_title'    => '',
                    'parent_url'      => '',
                    'parent_url_blog' => ''
                );
            }

            $r += array(
                'alias'     => $result[$i]['alias'],
                'id'        => $result[$i]['id'],
                'parent_id' => $result[$i]['parent_id']
            );

            $data[] = $r;
        }

        return $data;
    }

}

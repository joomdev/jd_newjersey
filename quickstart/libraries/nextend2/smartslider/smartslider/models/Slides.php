<?php

class N2SmartsliderSlidesModel extends N2Model {

    private $currentData, $slider;

    public function __construct() {
        parent::__construct("nextend2_smartslider3_slides");
    }

    public function get($id) {
        return $this->db->findByPk($id);
    }

    public function getAll($sliderid = 0, $where = '') {
        return $this->db->queryAll('SELECT * FROM ' . $this->getTable() . ' WHERE slider = ' . $sliderid . ' ' . $where . ' ORDER BY ordering', false, "assoc", null);
    }

    public function getRowFromPost($sliderId, $slide, $base64 = true) {

        if (!isset($slide['title'])) return false;
        if ($slide['title'] == '') $slide['title'] = n2_('New slide');

        if (isset($slide['publishdates'])) {
            $date = explode('|*|', $slide['publishdates']);
        } else {
            $date[0] = isset($slide['publish_up']) ? $slide['publish_up'] : null;
            $date[1] = isset($slide['publish_down']) ? $slide['publish_down'] : null;
            unset($slide['publish_up']);
            unset($slide['publish_down']);
        }
        $up   = strtotime(isset($date[0]) ? $date[0] : '');
        $down = strtotime(isset($date[1]) ? $date[1] : '');

        $generator_id = isset($slide['generator_id']) ? intval($slide['generator_id']) : 0;

        $slide['version'] = N2SS3::$version;

        $params = $slide;
        unset($params['title']);
        unset($params['slide']);
        unset($params['description']);
        unset($params['thumbnail']);
        unset($params['published']);
        unset($params['first']);
        unset($params['publishdates']);
        unset($params['generator_id']);

        return array(
            'title'        => $slide['title'],
            'slide'        => ($base64 ? n2_base64_decode($slide['slide']) : $slide['slide']),
            'description'  => $slide['description'],
            'thumbnail'    => $slide['thumbnail'],
            'published'    => (isset($slide['published']) ? $slide['published'] : 0),
            'publish_up'   => date('Y-m-d H:i:s', ($up && $up > 0 ? $up : strtotime('-1 day'))),
            'publish_down' => date('Y-m-d H:i:s', ($down && $down > 0 ? $down : strtotime('+10 years'))),
            'first'        => (isset($slide['first']) ? $slide['first'] : 0),
            'params'       => json_encode($params),
            'slider'       => $sliderId,
            'ordering'     => $this->getMaximalOrderValue($sliderId) + 1,
            'generator_id' => $generator_id
        );
    }

    /**
     * @param      $sliderId
     * @param      $slide
     * @param bool $base64
     *
     * @return bool
     */
    public function create($sliderId, $slide, $base64 = true) {

        $row = $this->getRowFromPost($sliderId, $slide, $base64);

        $slideId = $this->_create($row['title'], $row['slide'], $row['description'], $row['thumbnail'], $row['published'], $row['publish_up'], $row['publish_down'], 0, $row['params'], $row['slider'], $row['ordering'], $row['generator_id']);

        self::markChanged($sliderId);

        return $slideId;
    }

    protected function getMaximalOrderValue($sliderid = 0) {

        $query  = "SELECT MAX(ordering) AS ordering FROM " . $this->getTable() . " WHERE slider = :id";
        $result = $this->db->queryRow($query, array(
            ":id" => $sliderid
        ));

        if (isset($result['ordering'])) return $result['ordering'] + 1;

        return 0;
    }

    public function renderEditForm($slider, $slide) {
        $this->slider = $slider;
        if ($slide) {
            $params = json_decode($slide['params'], true);
            if ($params == null) $params = array();
            $params += $slide;
            $params['sliderid']     = $slide['slider'];
            $params['generator_id'] = $slide['generator_id'];
            echo '<input name="slide[generator_id]" value="' . $slide['generator_id'] . '" type="hidden" />';
        } else {
            $params = array(
                'static-slide' => N2Request::getInt('static')
            );
        }

        $data = new N2Data($params);

        if ($data->get('background-type') == '') {
            $params['background-type'] = 'color';
            if ($data->get('backgroundVideoMp4')) {
                $params['background-type'] = 'video';
            } else if ($data->get('backgroundImage')) {
                $params['background-type'] = 'image';
            }
        }

        $params['first'] = isset($slide['first']) ? $slide['first'] : 0;
        $this->editForm($params);

        return $data;
    }

    public function simpleEditForm($data = array()) {
        $configurationXmlFile = dirname(__FILE__) . '/forms/slide.xml';
        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));

        $data['publishdates'] = isset($data['publishdates']) ? $data['publishdates'] : ((isset($data['publish_up']) ? $data['publish_up'] : '') . '|*|' . (isset($data['publish_down']) ? $data['publish_down'] : ''));

        if (isset($data['slide'])) {
            $data['slide'] = n2_base64_encode($data['slide']);
        }

        $form->loadArray($data);

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slide');
    }

    /**
     * @param $tab N2TabTabbed
     */
    public function extendSlideSettings($tab) {

        $slidersModel = new N2SmartsliderSlidersModel();
        $slider       = $slidersModel->get(N2Request::getInt('sliderid', 0));

        $slideXML = call_user_func(array(
                'N2SSPluginType' . $slider['type'],
                "getPath"
            )) . '/slide.xml';
        if (N2Filesystem::existsFile($slideXML)) {
            $tab->addTabXML($slideXML, 1);
        }

        if ($tab->_form->get('generator_id') > 0) {
            $tab->addTabXML(dirname(__FILE__) . '/forms/slide_generator.xml', 10);

            $button = $tab->_tabs['generator']->_xml->params[0]->addChild('param');
            $button->addAttribute('type', 'button');
            $button->addAttribute('name', 'button');
            $button->addAttribute('label', '');
            $button->addAttribute('url', N2Base::getApplication('smartslider')
                                               ->getApplicationType('backend')->router->createUrl(array(
                    "generator/edit",
                    array(
                        'generator_id' => $this->currentData['generator_id']
                    )
                )));
            $button->addAttribute('target', '_self');
            $button->addAttribute('default', n2_('Edit generator'));
        }

    }

    /**
     * @param $tab N2TabTabbedWithHide
     */
    public function removeSlideSettingsBackground($tab) {

        $tab->removeTab('background');

    }


    /**
     * @param array $data
     */
    private function editForm($data = array()) {

        $this->currentData = $data;
        if (!$this->slider->isStaticEdited || (!isset($data['static-slide']) || $data['static-slide'] != 1)) {
            N2Pluggable::addAction('N2TabTabbedslide-settings', array(
                $this,
                'extendSlideSettings'
            ));
        } else {
            N2Pluggable::addAction('N2TabTabbedslide-settings', array(
                $this,
                'removeSlideSettingsBackground'
            ));
        }

        $this->simpleEditForm($data);

        N2JS::addFirstCode("new NextendForm('smartslider-form','', {});");
    }

    /**
     * @param int  $id
     * @param      $slide
     * @param bool $base64
     *
     * @return bool
     */
    public function save($id, $slide, $base64 = true) {
        if (!isset($slide['title']) || $id <= 0) return false;
        if ($slide['title'] == '') $slide['title'] = n2_('New slide');

        if (isset($slide['publishdates'])) {
            $date = explode('|*|', $slide['publishdates']);
        } else {
            $date[0] = $slide['publish_up'];
            $date[1] = $slide['publish_down'];
            unset($slide['publish_up']);
            unset($slide['publish_down']);
        }
        $up   = strtotime(isset($date[0]) ? $date[0] : '');
        $down = strtotime(isset($date[1]) ? $date[1] : '');

        $slide['version'] = N2SS3::$version;

        $tmpslide = $slide;
        unset($tmpslide['title']);
        unset($tmpslide['slide']);
        unset($tmpslide['description']);
        unset($tmpslide['thumbnail']);
        unset($tmpslide['published']);
        unset($tmpslide['publishdates']);
        unset($tmpslide['generator_id']);

        $this->db->update(array(
            'title'        => $slide['title'],
            'slide'        => ($base64 ? n2_base64_decode($slide['slide']) : $slide['slide']),
            'description'  => $slide['description'],
            'thumbnail'    => $slide['thumbnail'],
            'published'    => (isset($slide['published']) ? $slide['published'] : 0),
            'publish_up'   => date('Y-m-d H:i:s', ($up && $up > 0 ? $up : strtotime('-1 day'))),
            'publish_down' => date('Y-m-d H:i:s', ($down && $down > 0 ? $down : strtotime('+10 years'))),
            'params'       => json_encode($tmpslide)
        ), array('id' => $id));

        self::markChanged(N2Request::getInt('sliderid'));

        return $id;
    }

    public function updateParams($id, $params) {

        $this->db->update(array(
            'params' => json_encode($params)
        ), array('id' => $id));

        return $id;
    }

    public function quickSlideUpdate($slide, $title, $description, $link) {

        if ($title == '') $title = n2_('New slide');

        $params         = json_decode($slide['params'], true);
        $params['link'] = $link;

        return $this->db->update(array(
            'title'       => $title,
            'description' => $description,
            'params'      => json_encode($params)
        ), array('id' => $slide['id']));
    }

    public function delete($id) {

        $slide = $this->get($id);

        if ($slide['generator_id'] > 0) {
            $slidesWithSameGenerator = $this->getAll($slide['slider'], 'AND generator_id = ' . intval($slide['generator_id']));
            if (count($slidesWithSameGenerator) == 1) {
                $generatorModel = new N2SmartsliderGeneratorModel();
                $generatorModel->delete($slide['generator_id']);
            }
        }

        $this->db->deleteByAttributes(array(
            "id" => intval($id)
        ));

        self::markChanged($slide['slider']);

    }

    public function createQuickImage($image, $sliderId) {
        $publish_up   = date('Y-m-d H:i:s', strtotime('-1 day'));
        $publish_down = date('Y-m-d H:i:s', strtotime('+10 years'));

        $parameters = array(
            'backgroundImage' => $image['image']
        );

        if (!empty($image['alt'])) {
            $parameters['backgroundAlt'] = $image['alt'];
        }

        $parameters['version'] = N2SS3::$version;

        return $this->_create($image['title'], json_encode(array()), $image['description'], $image['image'], 1, $publish_up, $publish_down, 0, json_encode($parameters), $sliderId, $this->getMaximalOrderValue($sliderId), '');
    }

    public function createQuickVideo($video, $sliderId) {
        $publish_up   = date('Y-m-d H:i:s', strtotime('-1 day'));
        $publish_down = date('Y-m-d H:i:s', strtotime('+10 years'));

        $parameters = array(
            'thumbnailType' => 'videoDark'
        );

        $slide = new N2SmartSliderSlideHelper();

        switch ($video['type']) {
            case 'youtube':
                new N2SmartSliderItemHelper($slide, 'youtube', array(
                    'desktopportraitwidth'  => '100%',
                    'desktopportraitheight' => '100%',
                    'desktopportraitalign'  => 'left',
                    'desktopportraitvalign' => 'top'
                ), array(
                    "code"       => $video['video'],
                    "youtubeurl" => $video['video'],
                    "image"      => $video['image']
                ));
                break;
            case 'vimeo':
                new N2SmartSliderItemHelper($slide, 'vimeo', array(
                    'desktopportraitwidth'  => '100%',
                    'desktopportraitheight' => '100%',
                    'desktopportraitalign'  => 'left',
                    'desktopportraitvalign' => 'top'
                ), array(
                    "vimeourl" => $video['video'],
                    "image"    => ''
                ));
                break;
            case 'video':
            default:
                return false;
        }
        $layers = $slide->data['slide'];

        $parameters['version'] = N2SS3::$version;

        return $this->_create($video['title'], json_encode($layers), $video['description'], $video['image'], 1, $publish_up, $publish_down, 0, json_encode($parameters), $sliderId, $this->getMaximalOrderValue($sliderId), '');
    }

    public function createQuickPost($post, $sliderId) {
        $publish_up   = date('Y-m-d H:i:s', strtotime('-1 day'));
        $publish_down = date('Y-m-d H:i:s', strtotime('+10 years'));

        $data = new N2Data($post);

        $parameters = array(
            'backgroundImage' => $data->get('image'),
            'link'            => $data->get('link') . '|*|_self'
        );

        $title       = $data->get('title');
        $description = $data->get('description');


        $parameters['version'] = N2SS3::$version;

        return $this->_create($title, json_encode($this->getSlideLayers($title, $description)), $description, $data->get('image'), 1, $publish_up, $publish_down, 0, json_encode($parameters), $sliderId, $this->getMaximalOrderValue($sliderId), '');
    }

    private function getSlideLayers($hasTitle = false, $hasDescription = false) {
        $slide = new N2SmartSliderSlideHelper();
        if ($hasTitle && $hasDescription) {

            new N2SmartSliderItemHelper($slide, 'heading', array(
                'desktopportraitleft'   => 30,
                'desktopportraittop'    => 12,
                'desktopportraitalign'  => 'left',
                'desktopportraitvalign' => 'top'
            ), array(
                'heading' => '{name/slide}'
            ));

            new N2SmartSliderItemHelper($slide, 'text', array(
                'desktopportraitleft'   => 30,
                'desktopportraittop'    => 70,
                'desktopportraitalign'  => 'left',
                'desktopportraitvalign' => 'top'
            ), array(
                'content' => '{description/slide}'
            ));

            return $slide->data['slide'];
        } else if ($hasTitle) {

            new N2SmartSliderItemHelper($slide, 'heading', array(
                'desktopportraitleft'   => 30,
                'desktopportraittop'    => -12,
                'desktopportraitalign'  => 'left',
                'desktopportraitvalign' => 'bottom'
            ), array(
                'heading' => '{name/slide}'
            ));

            return $slide->data['slide'];
        }

        return array();
    }

    public function import($slide, $sliderId) {
        return $this->_create($slide['title'], $slide['slide'], $slide['description'], $slide['thumbnail'], $slide['published'], $slide['publish_up'], $slide['publish_down'], $slide['first'], $slide['params']->toJson(), $sliderId, $slide['ordering'], $slide['generator_id']);
    }

    private function _create($title, $slide, $description, $thumbnail, $published, $publish_up, $publish_down, $first, $params, $slider, $ordering, $generator_id) {
        $this->db->insert(array(
            'title'        => $title,
            'slide'        => $slide,
            'description'  => $description,
            'thumbnail'    => $thumbnail,
            'published'    => $published,
            'publish_up'   => $publish_up,
            'publish_down' => $publish_down,
            'first'        => $first,
            'params'       => $params,
            'slider'       => $slider,
            'ordering'     => $ordering,
            'generator_id' => $generator_id
        ));

        return $this->db->insertId();
    }

    public function duplicate($id) {
        $slide = $this->get($id);

        // Shift the afterwards slides ++
        $this->db->query("UPDATE {$this->getTable()} SET ordering = ordering + 1 WHERE slider = :sliderid AND ordering > :ordering", array(
            ":sliderid" => intval($slide['slider']),
            ":ordering" => intval($slide['ordering'])
        ), '');

        if (!empty($slide['generator_id'])) {
            $generatorModel        = new N2SmartsliderGeneratorModel();
            $slide['generator_id'] = $generatorModel->duplicate($slide['generator_id']);
        }

        $slide['slide'] = N2Data::json_encode(N2SSSlideComponentLayer::translateIds(json_decode($slide['slide'], true)));

        $slideId = $this->_create($slide['title'] . n2_(' - copy'), $slide['slide'], $slide['description'], $slide['thumbnail'], $slide['published'], $slide['publish_up'], $slide['publish_down'], 0, $slide['params'], $slide['slider'], $slide['ordering'] + 1, $slide['generator_id']);

        self::markChanged($slide['slider']);

        return $slideId;
    }

    public function copy($id, $targetSliderId) {
        $id    = intval($id);
        $slide = $this->get($id);
        if ($slide['generator_id'] > 0) {
            $generatorModel        = new N2SmartSliderGeneratorModel();
            $slide['generator_id'] = $generatorModel->duplicate($slide['generator_id']);
        }

        $slide['slide'] = N2Data::json_encode(N2SSSlideComponentLayer::translateIds(json_decode($slide['slide'], true)));

        $slideId = $this->_create($slide['title'], $slide['slide'], $slide['description'], $slide['thumbnail'], $slide['published'], $slide['publish_up'], $slide['publish_down'], 0, $slide['params'], $targetSliderId, $this->getMaximalOrderValue($targetSliderId), $slide['generator_id']);
        self::markChanged($slide['slider']);

        return $slideId;
    }

    public function first($id) {
        $slide = $this->get($id);

        $this->db->update(array("first" => 0), array(
            "slider" => $slide['slider']
        ));

        $this->db->update(array(
            "first" => 1
        ), array(
            "id" => $id
        ));

        self::markChanged($slide['slider']);
    }

    public function publish($id) {

        self::markChanged(N2Request::getInt('sliderid'));

        return $this->db->update(array(
            "published" => 1
        ), array("id" => intval($id)));
    }

    public function unPublish($id) {
        $this->db->update(array(
            "published" => 0
        ), array(
            "id" => intval($id)
        ));

        self::markChanged(N2Request::getInt('sliderid'));

    }

    public function deleteBySlider($sliderid) {

        $slides = $this->getAll($sliderid);
        foreach ($slides as $slide) {
            $this->delete($slide['id']);
        }
        self::markChanged($sliderid);
    }

    /**
     * @param $sliderid
     * @param $ids
     *
     * @return bool|int
     */
    public function order($sliderid, $ids) {
        if (is_array($ids) && count($ids) > 0) {
            $i = 0;
            foreach ($ids AS $id) {
                $id = intval($id);
                if ($id > 0) {
                    $update = $this->db->update(array(
                        'ordering' => $i,
                    ), array(
                        "id"     => $id,
                        "slider" => $sliderid
                    ));

                    $i++;
                }
            }

            self::markChanged($sliderid);

            return $i;
        }

        return false;
    }

    public static function markChanged($sliderid) {
        N2SmartSliderHelper::getInstance()
                           ->setSliderChanged($sliderid, 1);
    }

    public function makeStatic($slideId) {
        $slideData = $this->get($slideId);
        if ($slideData['generator_id'] > 0) {
            $sliderObj = new N2SmartSlider($slideData['slider'], array());
            $rootSlide = new N2SmartSliderSlide($sliderObj, $slideData);
            $rootSlide->initGenerator(array());
            $slides = $rootSlide->expandSlide();

            // Shift the afterwards slides with the slides count
            $this->db->query("UPDATE {$this->getTable()} SET ordering = ordering + " . count($slides) . " WHERE slider = :sliderid AND ordering > :ordering", array(
                ":sliderid" => intval($slideData['slider']),
                ":ordering" => intval($slideData['ordering'])
            ), '');

            $firstUsed = false;
            $i         = 1;
            foreach ($slides AS $slide) {
                $row = $slide->getRow();
                // set the proper ordering
                $row['ordering'] += $i;
                if ($row['first']) {
                    // Make sure to mark only one slide as start slide
                    if ($firstUsed) {
                        $row['first'] = 0;
                    } else {
                        $firstUsed = true;
                    }
                }
                $this->db->insert($row);
                $i++;
            }

            $this->db->query("UPDATE {$this->getTable()} SET published = 0, first = 0 WHERE id = :id", array(
                ":id" => $slideData['id']
            ), '');

            return count($slides);
        } else {
            return false;
        }
    }

    /**
     * @param $slide  N2SmartSliderSlide
     * @param $slider N2SmartSliderAbstract
     * @param $widget
     * @param $appType
     *
     * @throws Exception
     */
    public static function box($slide, $slider, $widget, $appType, $optimize) {
        $lt   = array();
        $lt[] = N2Html::tag('div', array(
            'class' => 'n2-ss-box-select',
        ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-tick2'), ''));

        $rt = array();

        $rb = array();

        $image = $slide->getThumbnail();
        if (empty($image)) {
            $image = N2ImageHelper::fixed('$system$/images/placeholder/image.png');
        }

        $editUrl = $appType->router->createUrl(array(
            'slides/edit',
            array(
                'sliderid' => $slider->sliderId,
                'slideid'  => $slide->id
            )
        ));

        $lb = array();

        if ($slide->parameters->get('static-slide', 0)) {
            $lb[] = '<div class="n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5 n2-uc">' . n2_('Static slide') . '</div>';
        }

        if ($slide->generator_id > 0) {
            $lb[] = '<div class="n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5 n2-uc">' . n2_('Dynamic slide') . '</div>';
        }

        $class = 'n2-box-small n2-box-selectable n2-box-slide ';
        $class .= ($slide->isFirst() ? ' n2-slide-state-first' : '');
        $class .= ($slide->published ? ' n2-slide-state-published' : '');
        $class .= ($slide->hasGenerator() ? ' n2-slide-state-has-generator' : '');
        $class .= ($slide->isCurrentlyEdited() ? ' n2-ss-slide-active' : '');

        $attributes = array(
            'style'            => 'background-image: URL("' . $optimize->optimizeThumbnail($image) . '");',
            'class'            => $class,
            'data-slideid'     => $slide->id,
            'data-title'       => $slide->getRawTitle(),
            'data-description' => $slide->getRawDescription(),
            'data-link'        => $slide->getRawLink(),
            'data-image'       => N2ImageHelper::fixed($image),
            'data-editUrl'     => $editUrl
        );

        if ($slide->hasGenerator()) {
            $attributes['data-generator'] = $appType->router->createUrl(array(
                'generator/edit',
                array(
                    'generator_id' => $slide->generator_id
                )
            ));
        }
        $widget->init("box", array(
            'attributes'         => $attributes,
            'lt'                 => implode('', $lt),
            'lb'                 => implode('', $lb),
            'rt'                 => implode('', $rt),
            'rtAttributes'       => array('class' => 'n2-on-hover'),
            'rb'                 => implode('', $rb),
            'overlay'            => N2Html::tag('div', array(
                'class' => 'n2-box-overlay n2-on-hover'
            ), N2Html::tag('div', array('class' => 'n2-button n2-button-normal n2-button-s n2-button-green n2-radius-s n2-uc n2-h5'), n2_('Edit'))),
            'placeholderContent' => N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-title n2-h4'
                ), N2Html::link($slide->getTitle() . ($slide->hasGenerator() ? ' [' . $slide->getSlideStat() . ']' : ''), $editUrl, array('class' => 'n2-h4'))) . N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-buttons'
                ), N2Html::tag('i', array('class' => 'n2-slide-first n2-i n2-it n2-i-star'), '') . N2Html::tag('a', array(
                        'class'      => 'n2-slide-published',
                        'data-n2tip' => 'Publish - Unpublish',
                        'href'       => $appType->router->createUrl(array(
                            'slides/publish',
                            array(
                                'sliderid' => $slider->sliderId,
                                'slideid'  => $slide->id
                            ) + N2Form::tokenizeUrl()
                        ))
                    ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-unpublished'), '')))

        ));
    }

    public static function prepareSample(&$layers) {
        for ($i = 0; $i < count($layers); $i++) {

            if (isset($layers[$i]['type'])) {
                switch ($layers[$i]['type']) {
                    case 'content':
                        N2SSSlideComponentContent::prepareSample($layers[$i]);
                        break;
                    case 'row':
                        N2SSSlideComponentRow::prepareSample($layers[$i]);
                        break;
                    case 'col':
                        N2SSSlideComponentCol::prepareSample($layers[$i]);
                        break;
                    case 'group':
                        N2SSSlideComponentGroup::prepareSample($layers[$i]);
                        break;
                    default:
                        N2SSSlideComponentLayer::prepareSample($layers[$i]);
                }
            } else {
                N2SSSlideComponentLayer::prepareSample($layers[$i]);
            }
        }
    }
} 
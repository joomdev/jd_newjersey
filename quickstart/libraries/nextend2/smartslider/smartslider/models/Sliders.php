<?php

N2Loader::import("libraries.slider.abstract", "smartslider");
N2Loader::import("models.SlidersXref", "smartslider");
N2Loader::import("models.Slides", "smartslider");

class N2SmartsliderSlidersModel extends N2Model {

    /**
     * @var N2SmartsliderSlidersXrefModel
     */
    private $xref;

    public function __construct() {
        parent::__construct("nextend2_smartslider3_sliders");

        $this->xref = new N2SmartsliderSlidersXrefModel();
    }

    public function get($id) {
        return $this->db->queryRow("SELECT * FROM " . $this->getTable() . " WHERE id = :id", array(
            ":id" => $id
        ));
    }

    public function getWithThumbnail($id) {
        $slidesModel = new N2SmartsliderSlidesModel();

        return $this->db->queryRow("SELECT sliders.*, IF(sliders.thumbnail != '',sliders.thumbnail,(SELECT slides.thumbnail from " . $slidesModel->getTable() . " AS slides WHERE slides.slider = sliders.id AND slides.published = 1 AND slides.generator_id = 0 AND slides.thumbnail NOT LIKE '' ORDER BY  slides.first DESC, slides.ordering ASC LIMIT 1)) AS thumbnail,
         IF(sliders.type != 'group', 
                        (SELECT count(*) FROM " . $slidesModel->getTable() . " AS slides2 WHERE slides2.slider = sliders.id GROUP BY slides2.slider),
                        (SELECT count(*) FROM " . $this->xref->getTable() . " AS xref2 WHERE xref2.group_id = sliders.id GROUP BY xref2.group_id)
                  ) AS slides
        FROM " . $this->getTable() . " AS sliders
        WHERE sliders.id = :id", array(
            ":id" => $id
        ));
    }

    public function refreshCache($sliderid) {
        N2Cache::clearGroup(N2SmartSliderAbstract::getCacheId($sliderid));
        N2Cache::clearGroup(N2SmartSliderAbstract::getAdminCacheId($sliderid));
        self::markChanged($sliderid);
    }


    /**
     * @return mixed
     */
    public function getAll($groupID, $orderBy = 'ordering', $orderByDirection = 'ASC') {
        $slidesModel = new N2SmartsliderSlidesModel();

        $_orderby = $orderBy . ' ' . $orderByDirection;
        if ($groupID != 0 && $orderBy == 'ordering') {
            $_orderby = 'xref.' . $orderBy . ' ' . $orderByDirection;
        }

        $sliders = $this->db->queryAll("
            SELECT sliders.*, 
                  IF(sliders.thumbnail != '',
                      sliders.thumbnail,
                          IF(sliders.type != 'group',
                              (SELECT slides.thumbnail FROM " . $slidesModel->getTable() . " AS slides WHERE slides.slider = sliders.id AND slides.published = 1 AND slides.generator_id = 0 AND slides.thumbnail NOT LIKE '' ORDER BY  slides.first DESC, slides.ordering ASC LIMIT 1),
                              ''
                          )
                  ) AS thumbnail,
                  
                  IF(sliders.type != 'group', 
                        (SELECT count(*) FROM " . $slidesModel->getTable() . " AS slides2 WHERE slides2.slider = sliders.id GROUP BY slides2.slider),
                        (SELECT count(*) FROM " . $this->xref->getTable() . " AS xref2 WHERE xref2.group_id = sliders.id GROUP BY xref2.group_id)
                  ) AS slides
            FROM " . $this->getTable() . " AS sliders
            LEFT JOIN " . $this->xref->getTable() . " AS xref ON xref.slider_id = sliders.id
            WHERE " . ($groupID == 0 ? "xref.group_id IS NULL OR xref.group_id = 0" : "xref.group_id = '" . $groupID . "'") . "
            ORDER BY " . $_orderby);

        return $sliders;
    }

    public function _getAll() {
        return $this->db->queryAll("SELECT sliders.* FROM " . $this->getTable() . " AS sliders");
    }

    public function getGroups() {
        return $this->db->queryAll("SELECT id, title FROM " . $this->getTable() . " WHERE type LIKE 'group' ORDER BY title ASC");
    }

    public static function renderAddForm($data = array()) {
        return self::editForm($data);
    }

    public static function renderEditForm($slider) {

        $data = json_decode($slider['params'], true);
        if ($data == null) $data = array();
        $data['title']     = $slider['title'];
        $data['type']      = $slider['type'];
        $data['thumbnail'] = $slider['thumbnail'];

        return self::editForm($data);
    }

    private static function editForm($data = array()) {

        $configurationXmlFile = dirname(__FILE__) . '/forms/slider.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));
        $form->set('class', 'nextend-smart-slider-admin');

        $form->loadArray($data);

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slider');

        N2Loader::import('libraries.form.element.url');
        N2JS::addFirstCode('nextend.NextendElementUrlParams=' . N2ElementUrl::getNextendElementUrlParameters() . ';');

        return $data;
    }

    public static function renderImportByUploadForm() {

        $configurationXmlFile = dirname(__FILE__) . '/forms/import/upload.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slider');
    }

    function import($slider, $groupID = 0) {
        try {
            $this->db->insert(array(
                'title'     => $slider['title'],
                'type'      => $slider['type'],
                'thumbnail' => empty($slider['thumbnail']) ? '' : $slider['thumbnail'],
                'params'    => $slider['params']->toJSON(),
                'time'      => date('Y-m-d H:i:s', N2Platform::getTime())
            ));

            $sliderID = $this->db->insertId();

            $this->xref->add($groupID, $sliderID);

            return $sliderID;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function restore($slider, $groupID) {

        if (isset($slider['id']) && $slider['id'] > 0) {

            $groups = $this->xref->getGroups($slider['id']);

            $this->delete($slider['id']);

            try {
                $this->db->insert(array(
                    'id'        => $slider['id'],
                    'title'     => $slider['title'],
                    'type'      => $slider['type'],
                    'thumbnail' => empty($slider['thumbnail']) ? '' : $slider['thumbnail'],
                    'params'    => $slider['params']->toJSON(),
                    'time'      => date('Y-m-d H:i:s', N2Platform::getTime())
                ));

                $sliderID = $this->db->insertId();

                if ($groupID) {
                    $this->xref->add($groupID, $sliderID);
                }

                if (!empty($groups)) {
                    foreach ($groups AS $group) {
                        $this->xref->add($group['group_id'], $sliderID);
                    }
                }

                return $sliderID;
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $this->import($slider);
    }

    /**
     * @param $sliderId
     * @param $params N2Data
     */
    function importUpdate($sliderId, $params) {

        $this->db->update(array(
            'params' => $params->toJson()
        ), array(
            "id" => $sliderId
        ));
    }

    function create($slider, $groupID = 0) {
        if (!isset($slider['title'])) return false;
        if ($slider['title'] == '') $slider['title'] = n2_('New slider');

        $title = $slider['title'];
        unset($slider['title']);
        $type = $slider['type'];
        unset($slider['type']);

        $thumbnail = '';
        if (!empty($slider['thumbnail'])) {
            $thumbnail = $slider['thumbnail'];
            unset($slider['thumbnail']);
        }

        try {
            $this->db->insert(array(
                'title'     => $title,
                'type'      => $type,
                'params'    => json_encode($slider),
                'thumbnail' => $thumbnail,
                'time'      => date('Y-m-d H:i:s', N2Platform::getTime()),
                'ordering'  => $this->getMaximalOrderValue()
            ));

            $sliderID = $this->db->insertId();

            $this->xref->add($groupID, $sliderID);

            return $sliderID;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function save($id, $slider) {
        if (!isset($slider['title']) || $id <= 0) return false;
        if ($slider['title'] == '') $slider['title'] = n2_('New slider');

        $title = $slider['title'];
        unset($slider['title']);
        $type = $slider['type'];
        unset($slider['type']);

        $thumbnail = '';
        if (!empty($slider['thumbnail'])) {
            $thumbnail = $slider['thumbnail'];
            unset($slider['thumbnail']);
        }

        $this->db->update(array(
            'title'     => $title,
            'type'      => $type,
            'params'    => json_encode($slider),
            'thumbnail' => $thumbnail
        ), array(
            "id" => $id
        ));

        self::markChanged($id);

        return $id;
    }

    function setThumbnail($id, $thumbnail) {

        $this->db->update(array(
            'thumbnail' => $thumbnail
        ), array(
            "id" => $id
        ));

        self::markChanged($id);

        return $id;
    }

    function delete($id) {
        $slidesModel = new N2SmartsliderSlidesModel();
        $slidesModel->deleteBySlider($id);

        $this->xref->deleteGroup($id);

        $this->xref->deleteSlider($id);
        $this->db->deleteByPk($id);

        N2Cache::clearGroup(N2SmartSliderAbstract::getCacheId($id));
        N2Cache::clearGroup(N2SmartSliderAbstract::getAdminCacheId($id));

        self::markChanged($id);
    }

    function deleteSlides($id) {
        $slidesModel = new N2SmartsliderSlidesModel();
        $slidesModel->deleteBySlider($id);
        self::markChanged($id);
    }

    function duplicate($id, $withGroup = true) {

        $slider = $this->get($id);

        unset($slider['id']);

        $slider['title'] .= n2_(' - copy');
        $slider['time'] = date('Y-m-d H:i:s', N2Platform::getTime());

        try {
            $this->db->insert($slider);
            $newSliderId = $this->db->insertId();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (!$newSliderId) {
            return false;
        }

        if ($slider['type'] == 'group') {
            $subSliders = $this->xref->getSliders($id);

            foreach ($subSliders AS $subSlider) {
                $newSubSliderID = $this->duplicate($subSlider['slider_id'], false);
                $this->xref->add($newSliderId, $newSubSliderID);
            }

        } else {

            $slidesModel = new N2SmartsliderSlidesModel();

            foreach ($slidesModel->getAll($id) AS $slide) {
                $slidesModel->copy($slide['id'], $newSliderId);
            }

            if ($withGroup) {
                $groups = $this->xref->getGroups($id);
                foreach ($groups AS $group) {
                    $this->xref->add($group['group_id'], $newSliderId);
                }
            }
        }

        return $newSliderId;
    }

    function redirectToCreate() {
        N2Request::redirect($this->appType->router->createUrl(array("sliders/create")), 302, true);
    }

    function exportSlider($id) {

    }

    function exportSliderAsHTML($id) {

    }

    public static function markChanged($sliderid) {
        N2SmartSliderHelper::getInstance()
                           ->setSliderChanged($sliderid, 1);
    }

    public static function box($slider, $widget, $appType) {
        $lt   = array();
        $lt[] = N2Html::tag('div', array(
            'class' => 'n2-ss-box-select',
        ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-tick2'), ''));

        $rt = array();

        $rb = array();

        $thumbnail = $slider['thumbnail'];
        if (empty($thumbnail)) {
            if ($slider['type'] == 'group') {
                $thumbnail = '$ss$/admin/images/group.png';
            } else {
                $thumbnail = '$system$/images/placeholder/image.png';
            }
        }

        $editUrl = $appType->router->createUrl(array(
            'slider/edit',
            array(
                'sliderid' => $slider['id']
            )
        ));

        $lb = array(
            N2Html::tag('div', array(
                'class' => 'n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5',
            ), '#' . $slider['id'])
        );


        $attributes = array(
            'style'         => 'background-image: URL("' . N2ImageHelper::fixed($thumbnail) . '");',
            'class'         => 'n2-ss-box-slider n2-box-selectable ' . ($slider['type'] == 'group' ? 'n2-ss-box-slider-group' : 'n2-ss-box-slider-slider'),
            'data-title'    => $slider['title'],
            'data-editUrl'  => $editUrl,
            'data-sliderid' => $slider['id']
        );
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
                    'class' => 'n2-box-placeholder-title'
                ), N2Html::link($slider['title'], $editUrl, array('class' => 'n2-h4'))) . N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-buttons'
                ), N2Html::tag('div', array(
                    'class' => 'n2-button n2-button-normal n2-button-s n2-radius-s n2-button-grey n2-h4 n2-right',
                ), $slider['slides'] | 0))
        ));
    }

    public static function embedBox($mode, $slider, $widget, $appType) {
        $lt = array();

        $rt = array();

        $rb = array();

        $thumbnail = $slider['thumbnail'];
        if (empty($thumbnail)) {
            if ($slider['type'] == 'group') {
                $thumbnail = '$ss$/admin/images/group.png';
            } else {
                $thumbnail = '$system$/images/placeholder/image.png';
            }
        }

        $lb = array(
            N2Html::tag('div', array(
                'class' => 'n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5',
            ), '#' . $slider['id'])
        );


        $attributes = array(
            'style' => 'background-image: URL(' . N2ImageHelper::fixed($thumbnail) . ');',
            'class' => 'n2-ss-box-slider n2-box-selectable ' . ($slider['type'] == 'group' ? 'n2-ss-box-slider-group' : 'n2-ss-box-slider-slider')
        );

        if ($slider['type'] == 'group') {
            $attributes['onclick'] = 'window.location="' . $appType->router->createUrl(array(
                    'sliders/' . $mode,
                    array(
                        'groupID' => $slider['id']
                    )
                )) . '";';
        } else {
            $attributes['onclick'] = 'window.parent.postMessage("' . $slider['id'] . '", "*");';
        }

        $widget->init("box", array(
            'attributes'         => $attributes,
            'lt'                 => implode('', $lt),
            'lb'                 => implode('', $lb),
            'rt'                 => implode('', $rt),
            'rtAttributes'       => array('class' => 'n2-on-hover'),
            'rb'                 => implode('', $rb),
            'placeholderContent' => N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-title n2-h4'
                ), $slider['title']) . N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-buttons'
                ), N2Html::tag('div', array(
                    'class' => 'n2-button n2-button-normal n2-button-s n2-radius-s n2-button-grey n2-h4 n2-right',
                ), $slider['slides'] | 0))
        ));
    }

    public function order($groupID, $ids, $isReverse = false) {
        if (is_array($ids) && count($ids) > 0) {
            if ($isReverse) {
                $ids = array_reverse($ids);
            }
            $groupID = intval($groupID);
            if ($groupID <= 0) {
                $groupID = false;
            }
            $i = 0;
            foreach ($ids AS $id) {
                $id = intval($id);
                if ($id > 0) {
                    if (!$groupID) {
                        $this->db->update(array(
                            'ordering' => $i,
                        ), array(
                            "id" => $id
                        ));
                    } else {
                        $this->xref->db->update(array(
                            'ordering' => $i,
                        ), array(
                            "slider_id" => $id,
                            "group_id"  => $groupID
                        ));
                    }

                    $i++;
                }
            }

            return $i;
        }

        return false;
    }

    protected function getMaximalOrderValue() {

        $query  = "SELECT MAX(ordering) AS ordering FROM " . $this->getTable() . "";
        $result = $this->db->queryRow($query);

        if (isset($result['ordering'])) return $result['ordering'] + 1;

        return 0;
    }

    public static function renderGroupEditForm($slider) {

        $data = json_decode($slider['params'], true);
        if ($data == null) $data = array();
        $data['title']     = $slider['title'];
        $data['type']      = $slider['type'];
        $data['thumbnail'] = $slider['thumbnail'];

        return self::editGroupForm($data);
    }

    private static function editGroupForm($data = array()) {

        $configurationXmlFile = dirname(__FILE__) . '/forms/slidergroup.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));
        $form->set('class', 'nextend-smart-slider-admin');

        $form->loadArray($data);

        $form->loadXMLFile($configurationXmlFile);

        echo $form->render('slider');

        N2Loader::import('libraries.form.element.url');
        N2JS::addFirstCode('nextend.NextendElementUrlParams=' . N2ElementUrl::getNextendElementUrlParameters() . ';');

        return $data;
    }

    public static function renderShapeDividerForm() {
    }

    public static function renderParticleForm() {
    }
} 
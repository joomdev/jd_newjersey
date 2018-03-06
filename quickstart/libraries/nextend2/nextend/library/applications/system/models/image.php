<?php
N2Loader::import('libraries.image.manager');

class N2SystemImageModel extends N2SystemVisualModel
{

    public $type = 'image';

    public function __construct() {
        $this->storage = new N2StorageImage();
    }

    public function renderForm() {
        $form = new N2Form();
        $form->loadXMLFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . 'form.xml');
        $form->render('n2-image-editor');
    }

    public function addVisual($image, $visual) {

        $visualId = $this->storage->add($image, $visual);

        $visual = $this->storage->getById($visualId);
        if (!empty($visual)) {
            return $visual;
        }
        return false;
    }

    public function getVisual($image) {
        return $this->storage->getByImage($image);
    }

    public function deleteVisual($id) {
        $visual = $this->storage->getById($id);
        $this->storage->deleteById($id);
        return $visual;
    }

    public function changeVisual($id, $value) {
        if ($this->storage->setById($id, $value)) {
            return $this->storage->getById($id);
        }
        return false;
    }

    public function getVisuals($setId) {
        return $this->storage->getAll();
    }
}
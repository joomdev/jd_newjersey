<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2017 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerBaseMain extends AController
{
    /**
     * Try to detect the CMS version
     */
    public function detectversion()
    {
        /** @var AngieModelBaseMain $model */
        $model = $this->getThisModel();
        $model->detectVersion();

        echo json_encode(true);
    }

    public function startover()
    {
        $this->container->session->reset();
        $this->container->session->saveData();
        $this->setRedirect('index.php?view=main');
    }
}
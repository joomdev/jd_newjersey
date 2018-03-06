<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2017 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

abstract class AngieModelBaseSetup extends AModel
{
    /**
     * Cached copy of the configuration model
     *
     * @var  AngieModelWordpressConfiguration
     */
    protected $configModel = null;

    /**
     * Overridden constructor
     *
     * @param   array       $config     Configuration array
     * @param   \AContainer $container
     */
    public function __construct($config = array(), AContainer $container = null)
    {
        parent::__construct($config, $container);

        $this->configModel = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);
    }

    /**
     * Return an object containing the configuration variables we read from the
     * state or the request.
     *
     * @return  stdClass
     */
    public function getStateVariables()
    {
        static $params = array();

        if(empty($params))
        {
            $params = array_merge($params, $this->getSiteParamsVars());
            $params = array_merge($params, $this->getSuperUsersVars());
        }

        return (object) $params;
    }

    abstract protected function getSiteParamsVars();

    abstract protected function getSuperUsersVars();

    abstract public function applySettings();

    /**
     * Returns the database connection variables for the default database.
     *
     * @return null|stdClass
     */
    protected function getDbConnectionVars()
    {
        /** @var AngieModelDatabase $model */
        $model		 = AModel::getAnInstance('Database', 'AngieModel', array(), $this->container);
        $keys		 = $model->getDatabaseNames();
        $firstDbKey	 = array_shift($keys);

        return $model->getDatabaseInfo($firstDbKey);
    }

    /**
     * Shorthand method to get the connection to the current database
     *
     * @return ADatabaseDriver
     */
    protected function getDatabase()
    {
        $connectionVars = $this->getDbConnectionVars();
        $name = $connectionVars->dbtype;
        $options = array(
            'database'	 => $connectionVars->dbname,
            'select'	 => 1,
            'host'		 => $connectionVars->dbhost,
            'user'		 => $connectionVars->dbuser,
            'password'	 => $connectionVars->dbpass,
            'prefix'	 => $connectionVars->prefix,
        );
        $db		 = ADatabaseFactory::getInstance()->getDriver($name, $options);

        return $db;
    }
}
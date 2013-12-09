<?php

namespace Horne;

use DateTime;
use DateTimeZone;
use Horne\Application;

/**
 *
 */
class View
{
    /**
     *
     * @var Application
     */
    protected $application;

    /**
     *
     * @var array
     */
    protected $vars;

    /**
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     *
     * @param string $tplFile
     * @param array $vars
     */
    public function execute($tplFile, array $vars = array())
    {
        $this->vars = $vars;

        require $tplFile;
    }

    /**
     *
     * @return string
     */
    public function getPathToRoot()
    {
        return $this->vars['pathToRoot'];
    }

    /**
     *
     * @param string $type
     * @param array $order
     * @param int $limitCount
     * @param int $limitOffset
     */
    public function getMetasByType($type, array $order = array(), $limitCount = -1, $limitOffset = 0)
    {
        return $this->application->getMetasByType($type, $order, $limitCount, $limitOffset);
    }

    /**
     *
     *
     * See http://nikic.github.io/2012/01/28/htmlspecialchars-improvements-in-PHP-5-4.html
     *
     * @param string $s
     * @return string
     */
    public function e($s)
    {
        return htmlspecialchars(
            $s,
            ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED,
            'UTF-8'
        );
    }

    /**
     *
     * @param string $date
     * @return string
     */
    public function datef($date)
    {
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $date, new DateTimeZone('UTC'));

        return $dt->format('j M Y');
    }

    public function getSetting($key)
    {
        return $this->application->getSetting($key);
    }

    public function url($id)
    {
        return $this->application->url($id);
    }

    public function render($id, array $vars = array())
    {
        return $this->application->render($id, $vars);
    }

    public function getAllMetas()
    {
        return $this->application->getAllMetas();
    }

    /**
     *
     * @param int $id
     * @return Module
     */
    public function getModule($id)
    {
        return $this->application->getModule($id);
    }

    /**
     *
     * @param string $tag
     * @return array
     */
    public function getMetasByTag($tag)
    {
        return $this->application->getMetasByTag($tag);
    }

    public function getMetaById($id)
    {
        return $this->application->getMetaById($id);
    }

    public function syntax($source, $lang)
    {
        return $this->application->syntax($source, $lang);
    }
}

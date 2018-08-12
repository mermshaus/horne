<?php

namespace Horne;

class Api
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return string
     */
    public function getPathToRoot()
    {
        return $this->application->getPathToRoot();
    }

    /**
     * @param string $type
     * @param array  $order
     * @param int    $limitCount
     * @param int    $limitOffset
     *
     * @return MetaBag[]
     */
    public function getMetasByType($type, array $order = [], $limitCount = -1, $limitOffset = 0)
    {
        return $this->application->getMetasByType($type, $order, $limitCount, $limitOffset);
    }

    /**
     * See http://nikic.github.io/2012/01/28/htmlspecialchars-improvements-in-PHP-5-4.html
     *
     * @param string $s
     *
     * @return string
     */
    public function e($s)
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED, 'UTF-8');
    }

    /**
     * @param string $date
     *
     * @return string
     */
    public function datef($date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $date, new \DateTimeZone('UTC'));

        return $dt->format('j M Y');
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getSetting($key)
    {
        return $this->application->getSetting($key);
    }

    /**
     * @param string $id
     *
     * @return string
     * @throws HorneException
     * @throws \InvalidArgumentException
     */
    public function url($id)
    {
        return $this->application->url($id);
    }

    /**
     * @param string $id
     * @param array  $vars
     *
     * @return string
     * @throws HorneException
     */
    public function render($id, array $vars = [])
    {
        return $this->application->render($id, $vars);
    }

    /**
     * @return MetaBag[]
     */
    public function getAllMetas()
    {
        return $this->application->getAllMetas();
    }

    /**
     * @param string $id
     *
     * @return Module\ModuleInterface
     * @throws HorneException
     */
    public function getModule($id)
    {
        return $this->application->getModule($id);
    }

    /**
     * @param string $tag
     *
     * @return MetaBag[]
     */
    public function getMetasByTag($tag)
    {
        return $this->application->getMetasByTag($tag);
    }

    /**
     * @param string $id
     *
     * @return MetaBag
     * @throws HorneException
     */
    public function getMetaById($id)
    {
        return $this->application->getMetaById($id);
    }

    /**
     * @param string $source
     * @param string $lang
     *
     * @return string
     */
    public function syntax($source, $lang = 'text')
    {
        return $this->application->syntax($source, $lang);
    }
}

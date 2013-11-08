<?php

namespace Horne;

use DateTime;
use DateTimeZone;
use Exception;
use GeSHi;
use Horne\Module\Blog\Blog;
use Horne\Module\Debug\Debug;
use Horne\Module\ModuleInterface;
use Horne\OutputFilter\OutputFilterInterface;
use Kaloa\Filesystem\PathHelper;

/**
 *
 */
class Application
{
    /**
     *
     * @var MetaRepository
     */
    public $metas;

    /**
     *
     * @var array
     */
    public $config;

    /**
     *
     * @var string
     */
    protected $pathToRoot = '.';

    /**
     *
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     *
     * @var array
     */
    protected $filters;

    protected $modules = array();

    /**
     *
     */
    public function __construct()
    {
        error_reporting(-1);
        ini_set('display_errors', 1);
        date_default_timezone_set('UTC');

        $this->pathHelper = new PathHelper();
        $this->filters = array();
    }

    public function getModule($id)
    {
        return $this->modules[$id];
    }

    public function setFilters($key, array $filters)
    {
        foreach ($filters as $filter) {
            if (!$filter instanceof OutputFilterInterface) {
                throw new HorneException('Filter must implement OutputFilterInterface');
            }
        }

        $this->filters[$key] = $filters;
    }

    /**
     *
     * @param string $id
     * @return string
     * @throws Exception
     */
    protected function url($id)
    {
        $metaBag = $this->metas->getById($id);
        $payload = $metaBag->getMetaPayload();

        return $this->pathHelper->normalize($this->pathToRoot . $payload['path']);
    }

    /**
     *
     * @return string
     */
    protected function getPathToRoot()
    {
        return $this->pathToRoot;
    }

    /**
     *
     * @param string $date
     * @return string
     */
    protected function datef($date)
    {
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $date, new DateTimeZone('UTC'));

        return $dt->format('j. M Y');
    }

    /**
     *
     * @param string $s
     * @return string
     */
    protected function e($s)
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    /**
     *
     * @param  string $tplFile  Template file
     * @param  array  $vars     Values constituting the template's content
     * @return string           Rendered output
     */
    protected function renderTpl($tplFile, array $vars = array())
    {
        ob_start();
        require $tplFile;
        return ob_get_clean();
    }

    public function render($id)
    {
        return $this->renderTpl($this->metas->getById($id)->getSourcePath());
    }

    public function applyFilters($content, $m)
    {
        $filterChain = array();

        if (isset($m['filters'])) {
            foreach ($m['filters'] as $filter) {
                if (array_key_exists($filter, $this->filters)) {
                    $filterChain[$filter] = $this->filters[$filter];
                } else {
                    throw new HorneException('No filters set for ' . $filter);
                }
            }
        }

        foreach ($filterChain as $key => $filters) {
            #echo '    Filter: ' . $key . "\n";
            foreach ($filters as $filter) {
                /* @var $filter OutputFilterInterface */
                $content = $filter->run($content);
            }
        }

        return $content;
    }

    /**
     *
     * @param MetaBag $m
     * @return string
     */
    protected function buildOneContentFile(MetaBag $mb)
    {
        $content = '';

        if ($mb->getSourcePath() !== '') {
            $content = $this->renderTpl($mb->getSourcePath());
        }

        $content = $this->applyFilters($content, $mb->getMetaPayload());



        $currentMetaBag = $mb;

//        echo 'ID: ' . $mb->getId() . "\n";

        while ($currentMetaBag->getType() !== '_master') {
            $currentMetaBag = $this->metas->getById('/types/' . $currentMetaBag->getType() . '.html');

            $content = $this->renderTpl($currentMetaBag->getSourcePath(), array(
                'meta' => $mb->getMetaPayload(),
                'content' => $content
            ));

            $content = $this->applyFilters($content, $currentMetaBag->getMetaPayload());
        }

        return $content;
    }

    /**
     *
     * @param string $type
     * @return array
     */
    protected function getMetasByType($type, array $order = array(), $limitCount = -1, $limitOffset = 0)
    {
        return $this->metas->getByType($type, $order, $limitCount, $limitOffset);
    }

    /**
     *
     * @param string $type
     * @return array
     */
    protected function getMetasByTag($type)
    {
        $metas = array();

        foreach ($this->metas->getAll() as $meta) {
            $m = $meta->getMetaPayload();
            if (!isset($m['tags'])) {
                continue;
            }

            foreach ($m['tags'] as $tag) {
                if ($tag === $type) {
                    $metas[] = $meta;
                    continue 2;
                }
            }
        }

        return $metas;
    }

    /**
     *
     * @param string $source
     * @param string $dest
     */
    protected function copyWithMkdir($source, $dest)
    {
        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        copy($source, $dest);
    }

    /**
     *
     * @param string $workingDirectory
     * @param string $path
     * @return string
     */
    protected function dingsify($workingDirectory, $path)
    {
        if ('/' !== substr($path, 0, 1)) {
            $path = $workingDirectory . '/' . $path;
        }

        return $this->pathHelper->normalize($path);
    }

    /**
     *
     * @param string $workingDirectory
     * @param array $json
     */
    public function run($workingDirectory, array $json)
    {
        $json['sourceDir'] = $this->dingsify($workingDirectory, $json['sourceDir']);
        $json['outputDir'] = $this->dingsify($workingDirectory, $json['outputDir']);

        /* Exclude paths */

        if (!array_key_exists('excludePaths', $json)) {
            $json['excludePaths'] = array();
        }

        foreach ($json['excludePaths'] as &$path) {
            $path = $this->dingsify($json['sourceDir'], ltrim($path, '/'));
        }
        unset($path);

        /* Theme directory */

        if (!isset($json['themeDir'])) {
            $json['themeDir'] = '';
            $json['defaultLayoutPath'] = $this->dingsify($workingDirectory, $json['defaultLayoutPath']);
        } else {
            $json['themeDir'] = $this->dingsify($workingDirectory, $json['themeDir']);

            $json['defaultLayoutPath'] = $this->dingsify($json['themeDir'], $json['defaultLayoutPath']);
        }

        $this->config = $json;

        $this->metas = (new MetaCollector($this->pathHelper))->gatherMetas(
            $json['sourceDir'],
            $json['defaultLayoutPath'],
            $json['outputDir'],
            $json['themeDir'],
            $json['excludePaths']
        );


        if (isset($json['modules']['blog'])) {
            $this->modules['blog'] = new Blog($this);
        }

        if (isset($json['modules']['debug'])) {
            $this->modules['debug'] = new Debug($this);
        }


        foreach ($this->modules as $module) {
            /* @var $module ModuleInterface */
            $module->hookProcessingBefore();
        }








        // Processing starts here

        foreach ($this->metas->getAll() as $m) {
            $type = $m->getType();

            switch (true) {
                case 'asset' === $type:
                    echo '[copy] ' . $m->getSourcePath() . "\n";
                    $this->copyWithMkdir($m->getSourcePath(), $m->getDestPath());
                    break;
                case substr($type, 0, 1) === '_':
                case 'layout' === $type:
                    // nop
                    break;
                default:
                    // Adjust path to root
                    $this->pathToRoot = '.';
                    $tmp = substr($m->getDestPath(), strlen($json['outputDir']) + 1);
                    $amount = substr_count($tmp, '/');
                    if ($amount > 0) {
                        $this->pathToRoot = str_repeat('../', $amount);
                    }

                    echo '[compile] ' . $m->getMetaPayload()['id'];
                    echo ' -> ' . $tmp . "\n";

                    $renderedOutput = $this->buildOneContentFile($m);

                    if (!is_dir(dirname($m->getDestPath()))) {
                        mkdir(dirname($m->getDestPath()), 0755, true);
                    }

                    file_put_contents($m->getDestPath(), $renderedOutput);
                    break;
            }
        }

        $this->pathToRoot = '.';
    }

    /**
     *
     * @param string $source
     * @param string $lang
     * @return string
     */
    protected function syntax($source, $lang)
    {
        $html = '';

        if (class_exists('\\GeSHi')) {
            $geshi = new GeSHi(ltrim(rtrim($source), "\r\n"), $lang);
            $geshi->enable_classes();
            $geshi->enable_keyword_links(false);

            $html = $geshi->parse_code();
        } else {
            $html = '<pre>' . $this->e($source) . '</pre>';
        }

        return $html;
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getSetting($key)
    {
        $parts = explode('.', $key);

        return $this->config['modules'][$parts[0]][$parts[1]];
    }
}

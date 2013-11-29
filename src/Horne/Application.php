<?php

namespace Horne;

use DateTime;
use DateTimeZone;
use Horne\Module\ModuleInterface;
use Horne\OutputFilter\OutputFilterInterface;
use Kaloa\Filesystem\PathHelper;
use Kaloa\Renderer\SyntaxHighlighter;
use Kir\Data\Arrays\RecursiveAccessor\StringPath\Accessor as StringPathAccessor;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class Application
{
    const VERSION = '0.1.0';

    /**
     *
     * @var MetaRepository
     */
    public $metas;

    /**
     *
     * @var StringPathAccessor
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

    /**
     *
     * @var array
     */
    protected $modules = array();

    /**
     *
     * @var SyntaxHighlighter
     */
    protected $syntaxHighlighter = null;

    /**
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     *
     */
    public function __construct()
    {
        $this->pathHelper = new PathHelper();
        $this->filters = array();
        $this->syntaxHighlighter = new SyntaxHighlighter();
        $this->output = new NullOutput();
    }

    /**
     *
     * @param OutputInterface $output
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     *
     * @return SyntaxHighlighter
     */
    public function getSyntaxHighlighter()
    {
        return $this->syntaxHighlighter;
    }

    /**
     *
     * @param SyntaxHighlighter $syntaxHighlighter
     */
    public function setSyntaxHighlighter(SyntaxHighlighter $syntaxHighlighter)
    {
        $this->syntaxHighlighter = $syntaxHighlighter;
    }

    /**
     *
     * @param string $id
     * @return ModuleInterface
     */
    public function getModule($id)
    {
        if (!array_key_exists($id, $this->modules)) {
            throw new HorneException(sprintf('Module %s is not loaded', $id));
        }

        return $this->modules[$id];
    }

    /**
     *
     * @param string $key
     * @param array $filters Array of OutputFilterInterface
     * @throws HorneException
     */
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
     */
    protected function url($id)
    {
        $metaBag = $this->metas->getById($id);
        $payload = $metaBag->getMetaPayload();

        if (!isset($payload['path'])) {
            return '';
        }

        $tmp = $payload['path'];

        if (isset($payload['slug'])) {
            $tmp = $payload['slug'];
        }

        return $this->pathHelper->normalize($this->pathToRoot . $tmp);
    }

    /**
     *
     * @return string
     */
    public function getPathToRoot()
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

        return $dt->format('j M Y');
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
     * @param  array  $vars     Content for the template
     * @return string           Rendered output
     */
    protected function renderTpl($tplFile, array $vars = array())
    {
        ob_start();

        if (strpos($tplFile, '.md') !== false) {
            $content = file_get_contents($tplFile);

            $firstSep = strpos($content, '---');
            $secondSep = strpos($content, '---', $firstSep + 1);

            echo substr($content, $secondSep + 3);
        } else {
            require $tplFile;
        }

        return ob_get_clean();
    }

    /**
     *
     * @param string $id
     * @param  array  $vars  Content for the template
     * @return string
     */
    public function render($id, array $vars = array())
    {
        return $this->renderTpl($this->metas->getById($id)->getSourcePath(), $vars);
    }

    /**
     *
     * @param string $content
     * @param array $mb
     * @return string
     * @throws HorneException
     */
    public function applyFilters($content, MetaBag $mb)
    {
        $m = $mb->getMetaPayload();

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

        foreach ($filterChain as $filters) {
            foreach ($filters as $filter) {
                /* @var $filter OutputFilterInterface */
                $content = $filter->run($content, $mb);
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

        $content = $this->applyFilters($content, $mb);

        $currentMetaBag = $mb;

        while ($currentMetaBag->getLayout() !== null) {
            $currentMetaBag = $this->metas->getById($currentMetaBag->getLayout());

            $content = $this->renderTpl($currentMetaBag->getSourcePath(), array(
                'meta'    => $mb->getMetaPayload(),
                'content' => $content
            ));

            $content = $this->applyFilters($content, $currentMetaBag);
        }

        return $content;
    }

    /**
     *
     * @param string $type
     * @param array $order
     * @param int $limitCount
     * @param int $limitOffset
     * @return array
     */
    protected function getMetasByType($type, array $order = array(), $limitCount = -1, $limitOffset = 0)
    {
        return $this->metas->getByType($type, $order, $limitCount, $limitOffset);
    }

    protected $cacheGetMetasByTag = array();

    /**
     *
     * @param string $tag
     * @return array
     */
    protected function getMetasByTag($tag)
    {
        if (count($this->cacheGetMetasByTag) === 0) {
            foreach ($this->metas->getAll() as $meta) {
                $m = $meta->getMetaPayload();
                if (!isset($m['tags'])) {
                    continue;
                }

                foreach ($m['tags'] as $tag2) {
                    if (!array_key_exists($tag2, $this->cacheGetMetasByTag)) {
                        $this->cacheGetMetasByTag[$tag2] = array();
                    }
                    $this->cacheGetMetasByTag[$tag2][] = $meta;
                }
            }
        }

        return $this->cacheGetMetasByTag[$tag];
    }

    public function getMetaById($id)
    {
        return $this->metas->getById($id);
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
    protected function sanitizeCoreConfig($workingDirectory, array &$json)
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

        if (!array_key_exists('generateGzipHtml', $json)) {
            $json['generateGzipHtml'] = false;
        }

        if (!array_key_exists('modules', $json)) {
            $json['modules'] = array();
        }

        if (!array_key_exists('metaOverrides', $json)) {
            $json['metaOverrides'] = array();
        }

        $this->config = new StringPathAccessor($json);
    }

    /**
     *
     */
    protected function initializeModules()
    {
        foreach (array_keys($this->config->get('modules')) as $key) {
            $fqcn = '\\Horne\\Module\\' . ucfirst($key) . '\\' . ucfirst($key);
            $this->modules[$key] = new $fqcn($this);
        }

        foreach ($this->modules as $key => $module) {
            $this->config->set(
                'modules' . '.' . $key,
                $module->hookLoadConfig($this->config->get('modules' . '.' . $key))
            );
        }
    }

    /**
     *
     * @param string $workingDirectory
     * @param array $json
     */
    public function run($workingDirectory, array $json)
    {
        $this->sanitizeCoreConfig($workingDirectory, $json);
        unset($json);

        $this->initializeModules();

        $this->metas = new MetaRepository();

        foreach ($this->modules as $module) {
            /* @var $module ModuleInterface */
            $module->hookProcessingBefore();
        }

        $tmp = new MetaCollector(
            $this->pathHelper,
            $this->config->get('sourceDir'),
            $this->config->get('outputDir')
        );

        $tmp->gatherMetas($this->metas, $this->config->get('excludePaths'));

        foreach ($this->modules as $module) {
            /* @var $module ModuleInterface */
            $module->hookProcessingBefore2();
        }

        foreach ($this->config->get('metaOverrides') as $id => $newData) {
            $meta = $this->metas->getById($id);
            $payload = $meta->getMetaPayload();

            $newPath = $this->pathHelper->normalize(
                $this->config->get('outputDir') . $newData['path']
            );

            if (0 !== strpos($newPath, $this->config->get('outputDir'))) {
                throw new HorneException('Path ' . $newPath . ' not in $outputDir');
            }

            $meta->setDestPath($newPath);

            $payload['path'] = $newData['path'];
            $payload['slug'] = $newData['slug'];
            $meta->setMetaPayload($payload);
        }

        // Processing starts here

        $metas = $this->metas->getAll();

        // Sort metas by type and id

        usort($metas, function ($a, $b) {
            if ($a->getType() !== $b->getType()) {
                return strcmp($a->getType(), $b->getType());
            }

            return strcmp($a->getId(), $b->getId());
        });

        foreach ($metas as $m) {
            $type = $m->getType();

            switch (true) {
                case 'asset' === $type:
                    $this->output->writeln('[copy] <info>' . $m->getSourcePath() . '</info>');
                    $this->copyWithMkdir($m->getSourcePath(), $m->getDestPath());
                    break;
                case substr($type, 0, 1) === '_':
                case 'layout' === $type:
                    // nop
                    break;
                default:
                    // Adjust path to root
                    $this->pathToRoot = '.';

                    $payload = $m->getMetaPayload();
                    $key = 'path';
                    if (isset($payload['slug'])) {
                        $key = 'slug';
                    }
                    $tmp = ltrim($payload[$key], '/');
                    $amount = substr_count($tmp, '/');
                    if ($amount > 0) {
                        $this->pathToRoot = rtrim(str_repeat('../', $amount), '/');
                    }

                    $tmp2 = substr($m->getDestPath(), strlen($this->config->get('outputDir')) + 1);

                    $this->output->writeln('[compile] <info>' . $m->getMetaPayload()['id'] . '</info> -> <info>' . $tmp2 . '</info>');

                    $renderedOutput = $this->buildOneContentFile($m);

                    if (!is_dir(dirname($m->getDestPath()))) {
                        mkdir(dirname($m->getDestPath()), 0755, true);
                    }

                    file_put_contents($m->getDestPath(), $renderedOutput);

                    if ($this->config->get('generateGzipHtml')) {
                        $gzipFileExtensionSuffix = ($this->config->has('gzipFileExtensionSuffix'))
                                ? $this->config->get('gzipFileExtensionSuffix')
                                : '.gz';

                        $gzipPath = $m->getDestPath() . $gzipFileExtensionSuffix;

                        file_put_contents($gzipPath, gzencode($renderedOutput, 9));
                        touch($gzipPath, filemtime($m->getDestPath()));
                    }
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
        $html = $this->syntaxHighlighter->highlight($source, $lang);

        return $html;
    }

    /**
     * Returns a config setting by dot-separated key
     *
     * @param string $key
     * @return mixed
     */
    public function getSetting($key)
    {
        $parts = explode('.', $key);

        // This means: "blog.setting" will be expanded to "modules.blog.setting"
        // if there is no top-level key "blog" and if "blog" is a key in
        // "modules"
        if (
            count($parts) > 1
            && !$this->config->has($parts[0])
            && $this->config->has('modules' . '.' . $parts[0])
        ) {
            array_unshift($parts, 'modules');
        }

        return $this->config->get($parts, null);
    }
}

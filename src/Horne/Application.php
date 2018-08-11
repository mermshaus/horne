<?php

namespace Horne;

use Horne\Module\ModuleInterface;
use Horne\OutputFilter\OutputFilterInterface;
use Kaloa\Filesystem\PathHelper;
use Kaloa\Renderer\SyntaxHighlighter;
use Kir\Data\Arrays\RecursiveAccessor\StringPath\Accessor as StringPathAccessor;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Application
{
    const VERSION = '0.3.3';

    /**
     * @var MetaRepository
     */
    public $metas;

    /**
     * @var StringPathAccessor
     */
    public $config;

    /**
     * @var string
     */
    protected $pathToRoot = '.';

    /**
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var SyntaxHighlighter
     */
    protected $syntaxHighlighter;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $cacheGetMetasByTag = [];

    public function __construct()
    {
        $this->pathHelper        = new PathHelper();
        $this->filters           = [];
        $this->syntaxHighlighter = new SyntaxHighlighter();
        $this->output            = new NullOutput();
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return SyntaxHighlighter
     */
    public function getSyntaxHighlighter()
    {
        return $this->syntaxHighlighter;
    }

    /**
     * @param SyntaxHighlighter $syntaxHighlighter
     */
    public function setSyntaxHighlighter(SyntaxHighlighter $syntaxHighlighter)
    {
        $this->syntaxHighlighter = $syntaxHighlighter;
    }

    /**
     * @param string $id
     *
     * @return ModuleInterface
     * @throws HorneException
     */
    public function getModule($id)
    {
        if (!array_key_exists($id, $this->modules)) {
            throw new HorneException(sprintf('Module %s is not loaded', $id));
        }

        return $this->modules[$id];
    }

    /**
     * @param string $key
     * @param array  $filters Array of OutputFilterInterface
     *
     * @return void
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
     * @param string $id
     *
     * @return string
     * @throws HorneException
     * @throws \InvalidArgumentException
     */
    public function url($id)
    {
        $metaBag     = $this->metas->getById($id);
        $metaPayload = $metaBag->getMetaPayload();

        if (!isset($metaPayload['path'])) {
            return '';
        }

        $tmp = $metaPayload['path'];

        if (isset($metaPayload['slug'])) {
            $tmp = $metaPayload['slug'];
        }

        return $this->pathHelper->normalize($this->pathToRoot . $tmp);
    }

    /**
     * @return string
     */
    public function getPathToRoot()
    {
        return $this->pathToRoot;
    }

    /**
     * @param  string $tplFile  Template file
     * @param  array  $vars     Content for the template
     * @return string           Rendered output
     */
    protected function renderTpl($tplFile, array $vars = [])
    {
        ob_start();

        if (strpos($tplFile, '.md') !== false) {
            $content   = file_get_contents($tplFile);
            $firstSep  = strpos($content, '---');
            $secondSep = strpos($content, '---', $firstSep + 1);

            echo substr($content, $secondSep + 3);
        } else {
            $view = new View();
            $api  = new Api($this);

            $view->execute($tplFile, $api, $vars);
        }

        return ob_get_clean();
    }

    /**
     * @param string $id
     * @param array  $vars Content for the template
     *
     * @return string
     * @throws HorneException
     */
    public function render($id, array $vars = [])
    {
        return $this->renderTpl($this->metas->getById($id)->getSourcePath(), $vars);
    }

    /**
     * @param string  $content
     * @param MetaBag $metaBag
     *
     * @return string
     * @throws HorneException
     */
    public function applyFilters($content, MetaBag $metaBag)
    {
        $metaPayload = $metaBag->getMetaPayload();

        $filterChain = [];

        if (isset($metaPayload['filters'])) {
            foreach ($metaPayload['filters'] as $filter) {
                if (array_key_exists($filter, $this->filters)) {
                    $filterChain[$filter] = $this->filters[$filter];
                } else {
                    throw new HorneException('No filters set for ' . $filter);
                }
            }
        }

        foreach ($filterChain as $filters) {
            foreach ($filters as $filter) {
                /* @var OutputFilterInterface $filter */
                $content = $filter->run($content, $metaBag);
            }
        }

        return $content;
    }

    /**
     * @param MetaBag $metaBag
     *
     * @return string
     * @throws HorneException
     */
    protected function buildOneContentFile(MetaBag $metaBag)
    {
        $content = '';

        if ($metaBag->getSourcePath() !== '') {
            $content = $this->renderTpl($metaBag->getSourcePath());
        }

        $content = $this->applyFilters($content, $metaBag);

        $currentMetaBag = $metaBag;

        while ($currentMetaBag->getLayout() !== null) {
            $currentMetaBag = $this->metas->getById($currentMetaBag->getLayout());

            $content = $this->renderTpl($currentMetaBag->getSourcePath(), [
                'meta'    => $metaBag->getMetaPayload(),
                'content' => $content,
            ]);

            $content = $this->applyFilters($content, $currentMetaBag);
        }

        return $content;
    }

    /**
     * @param string $type
     * @param array  $order
     * @param int    $limitCount
     * @param int    $limitOffset
     *
     * @return array
     */
    public function getMetasByType($type, array $order = [], $limitCount = -1, $limitOffset = 0)
    {
        return $this->metas->getByType($type, $order, $limitCount, $limitOffset);
    }

    /**
     * @return MetaBag[]
     */
    public function getAllMetas()
    {
        return $this->metas->getAll();
    }

    /**
     * @param string $tag
     *
     * @return MetaBag[]
     */
    public function getMetasByTag($tag)
    {
        if (count($this->cacheGetMetasByTag) === 0) {
            foreach ($this->metas->getAll() as $meta) {
                $metaPayload = $meta->getMetaPayload();
                if (!isset($metaPayload['tags'])) {
                    continue;
                }

                foreach ($metaPayload['tags'] as $tag2) {
                    if (!array_key_exists($tag2, $this->cacheGetMetasByTag)) {
                        $this->cacheGetMetasByTag[$tag2] = [];
                    }
                    $this->cacheGetMetasByTag[$tag2][] = $meta;
                }
            }
        }

        return $this->cacheGetMetasByTag[$tag];
    }

    /**
     * @param int $id
     *
     * @return MetaBag
     * @throws HorneException
     */
    public function getMetaById($id)
    {
        return $this->metas->getById($id);
    }

    /**
     * @param string $source
     * @param string $dest
     *
     * @return void
     */
    protected function copyWithMkdir($source, $dest)
    {
        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        copy($source, $dest);
    }

    /**
     * @param string $workingDirectory
     * @param string $path
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function dingsify($workingDirectory, $path)
    {
        if (strpos($path, '/') !== 0) {
            $path = $workingDirectory . '/' . $path;
        }

        return $this->pathHelper->normalize($path);
    }

    /**
     * @param string $workingDirectory
     * @param array  $json
     *
     * @throws \InvalidArgumentException
     */
    protected function sanitizeCoreConfig($workingDirectory, array &$json)
    {
        $json['sourceDir'] = $this->dingsify($workingDirectory, $json['sourceDir']);
        $json['outputDir'] = $this->dingsify($workingDirectory, $json['outputDir']);

        /* Exclude paths */

        if (!array_key_exists('excludePaths', $json)) {
            $json['excludePaths'] = [];
        }

        foreach ($json['excludePaths'] as &$path) {
            $path = $this->dingsify($json['sourceDir'], ltrim($path, '/'));
        }
        unset($path);

        if (!array_key_exists('generateGzipHtml', $json)) {
            $json['generateGzipHtml'] = false;
        }

        if (!array_key_exists('modules', $json)) {
            $json['modules'] = [];
        }

        if (!array_key_exists('metaOverrides', $json)) {
            $json['metaOverrides'] = [];
        }

        $this->config = new StringPathAccessor($json);
    }

    /**
     * @return void
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
     * @param string $workingDirectory
     * @param array  $json
     *
     * @return void
     * @throws HorneException
     * @throws \InvalidArgumentException
     */
    public function run($workingDirectory, array $json)
    {
        $this->sanitizeCoreConfig($workingDirectory, $json);
        unset($json);

        if ($this->config->get('initScript', null) !== null) {
            $initScriptPath = $this->config->get('sourceDir') . '/' . $this->config->get('initScript');
            require_once $initScriptPath;
        }

        $this->initializeModules();

        $this->metas = new MetaRepository($this->config->get('sourceDir'));

        foreach ($this->modules as $module) {
            /* @var ModuleInterface $module */
            $module->hookProcessingBefore();
        }

        $tmp = new MetaCollector(
            $this->pathHelper,
            $this->metas,
            $this->config->get('sourceDir'),
            $this->config->get('outputDir')
        );

        $tmp->gatherMetas($this->config->get('excludePaths'));

        foreach ($this->modules as $module) {
            /* @var ModuleInterface $module */
            $module->hookProcessingBefore2();
        }

        /** @todo This needs more error handling */
        foreach ($this->config->get('metaOverrides') as $id => $newData) {
            $meta    = $this->metas->getById($id);
            $payload = $meta->getMetaPayload();

            $newPath = $this->pathHelper->normalize(
                $this->config->get('outputDir') . $newData['path']
            );

            if (strpos($newPath, $this->config->get('outputDir')) !== 0) {
                throw new HorneException('Path ' . $newPath . ' not in $outputDir');
            }

            $meta->setDestPath($newPath);

            $payload['path'] = $newData['path'];
            $payload['slug'] = $newData['slug'];

            if (
                array_key_exists('title', $newData)
                && array_key_exists('title', $payload)
            ) {
                $payload['title'] = trim($newData['title']);
            }

            $meta->setMetaPayload($payload);
        }

        // Processing starts here

        $metas = $this->metas->getAll();

        // Sort metas by type and id

        usort($metas, function (MetaBag $a, MetaBag $b) {
            if ($a->getType() !== $b->getType()) {
                return strcmp($a->getType(), $b->getType());
            }

            return strcmp($a->getId(), $b->getId());
        });

        foreach ($metas as $m) {
            $type = $m->getType();

            switch (true) {
                case $type === 'asset':
                    $this->output->writeln('[copy] <info>' . $m->getSourcePath() . '</info>');
                    $this->copyWithMkdir($m->getSourcePath(), $m->getDestPath());
                    break;
                case strpos($type, '_') === 0:
                case $type === 'layout':
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
                        $gzipFileExtensionSuffix = $this->config->has('gzipFileExtensionSuffix')
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
     * @param string $source
     * @param string $lang
     *
     * @return string
     */
    public function syntax($source, $lang)
    {
        return $this->syntaxHighlighter->highlight($source, $lang);
    }

    /**
     * Returns a config setting by dot-separated key
     *
     * @param string $key
     *
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

    /**
     * @param string $path
     * @param string $root
     *
     * @return void
     * @throws HorneException
     * @throws \InvalidArgumentException
     */
    public function source($path, $root = null)
    {
        $metaReader = new MetaReader($this->pathHelper, $this->config->get('outputDir'));

        $o = [
            'path' => $path,
            'root' => $root,
        ];

        $metaBag = $metaReader->load($o);

        $this->metas->add($metaBag);
    }
}

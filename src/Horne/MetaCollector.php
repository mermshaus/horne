<?php

namespace Horne;

use Horne\MetaRepository;
use Kaloa\Filesystem\PathHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 *
 */
class MetaCollector
{
    /**
     *
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     *
     * @var string
     */
    protected $sourceDir;

    /**
     *
     * @var string
     */
    protected $outputDir;

    /**
     *
     * @var MetaRepository
     */
    protected $metaRepository;

    /**
     *
     * @param PathHelper $pathHelper
     * @param string $sourceDir
     * @param string $outputDir
     */
    public function __construct(PathHelper $pathHelper, MetaRepository $metaRepository, $sourceDir, $outputDir)
    {
        $this->pathHelper = $pathHelper;
        $this->metaRepository = $metaRepository;
        $this->sourceDir = $sourceDir;
        $this->outputDir = $outputDir;
    }

    /**
     *
     * @param array $excludePaths
     * @return array
     */
    public function gatherMetas(array $excludePaths)
    {
        $objects = array();

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->sourceDir)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            /*if (strpos($file->getPathname(), '/_')) {
                continue;
            }*/

            foreach ($excludePaths as $path) {
                if (0 === strpos($file->getPathname(), $path)) {
                    continue 2;
                }
            }

            $objects[] = [
                'path' => $file->getPathname(),
                'root' => $this->sourceDir
            ];
        }

        $mr = new MetaReader($this->pathHelper, $this->outputDir);

        foreach ($objects as $o) {
            $metaBag = $mr->load($o);

            if ($metaBag !== null) {
                $this->metaRepository->add($metaBag);
            }
        }
    }
}

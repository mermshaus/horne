<?php

namespace Horne;

use Kaloa\Filesystem\PathHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class MetaCollector
{
    /**
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     * @var string
     */
    protected $sourceDir;

    /**
     * @var string
     */
    protected $outputDir;

    /**
     * @var MetaRepository
     */
    protected $metaRepository;

    /**
     * @param PathHelper            $pathHelper
     * @param \Horne\MetaRepository $metaRepository
     * @param string                $sourceDir
     * @param string                $outputDir
     */
    public function __construct(PathHelper $pathHelper, MetaRepository $metaRepository, $sourceDir, $outputDir)
    {
        $this->pathHelper     = $pathHelper;
        $this->metaRepository = $metaRepository;
        $this->sourceDir      = $sourceDir;
        $this->outputDir      = $outputDir;
    }

    /**
     * @param string[] $excludePaths
     *
     * @return void
     * @throws HorneException
     * @throws \InvalidArgumentException
     */
    public function gatherMetas(array $excludePaths)
    {
        $objects = [];

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
                if (strpos($file->getPathname(), $path) === 0) {
                    continue 2;
                }
            }

            $objects[] = [
                'path' => $file->getPathname(),
                'root' => $this->sourceDir
            ];
        }

        $metaReader = new MetaReader($this->pathHelper, $this->outputDir);

        foreach ($objects as $o) {
            $metaBag = $metaReader->load($o);

            if ($metaBag !== null) {
                $this->metaRepository->add($metaBag);
            }
        }
    }
}

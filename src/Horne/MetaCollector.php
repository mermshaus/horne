<?php

namespace Horne;

use Horne\MetaBag;
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

    protected $sourceDir;

    protected $outputDir;

    /**
     *
     * @var MetaRepository
     */
    protected $metaRepository;

    public function __construct(PathHelper $pathHelper, $sourceDir, $outputDir)
    {
        $this->pathHelper = $pathHelper;
        $this->sourceDir = $sourceDir;
        $this->outputDir = $outputDir;
    }

    protected function assertOutputDir($outputDir, $path)
    {
        if (0 !== strpos($path, $outputDir)) {
            throw new HorneException('Path ' . $path . ' not in $outputDir');
        }
    }

    /**
     *
     * @param string $path
     * @return array
     * @throws HorneException
     */
    protected function getJsonMetaDataFromFile($path)
    {
        $data = file_get_contents($path);

        $start = strpos($data, '---');
        $end   = strpos($data, '---', $start + 1);

        if ($start === false || $end === false) {
            throw new HorneException('No meta data found in ' . $path);
        }

        $jsonString = substr($data, $start + 3, $end - ($start + 3));

        $jsonArray = json_decode($jsonString, true);

        if ($jsonArray === null) {
            throw new HorneException('Meta data in ' . $path . ' seems to be invalid');
        }

        return $jsonArray;
    }

    /**
     *
     * @param array $o
     * @param string $outputDir
     */
    public function addMeta($o, $outputDir)
    {
        $fileExtension = strtolower(pathinfo($o['path'], PATHINFO_EXTENSION));

        switch (true) {
            case in_array($fileExtension, ['md', 'phtml']):
                $data = $this->getJsonMetaDataFromFile($o['path']);

                if (!isset($data['path'])) {
                    $data['path'] = substr($o['path'], strlen($o['root']));
                    $data['path'] = preg_replace(
                        '/\.' . preg_quote($fileExtension, '/') . '$/',
                        '.html',
                        $data['path']
                    );
                }

                if (!isset($data['id'])) {
                    $data['id'] = $data['path'];
                }

                if (!isset($data['type'])) {
                    $data['type'] = 'page';
                }

                $dest = $this->pathHelper->normalize($outputDir . '/' . $data['path']);
                break;
            default:
                $rel = '/' . substr($o['path'], strlen($o['root']) + 1);

                $data = [
                    'id' => $rel,
                    'type' => 'asset'
                ];

                $dest = $this->pathHelper->normalize($outputDir . '/' . $rel);
                break;
        }

        $metaBag = new MetaBag($o['path'], $dest, $data);

        foreach ($this->metaRepository->getAll() as $item) {
            /* @var $item MetaBag */
            if ($item->getId() === $metaBag->getId()) {
                // If both in sourceDir, throw Exception (in other words:
                // files from sourceDir can override files added by modules)
                if (
                    0 === strpos($item->getSourcePath(), $this->sourceDir)
                    && 0 === strpos($metaBag->getSourcePath(), $this->sourceDir)
                ) {
                    throw new HorneException('Meta with id ' . $metaBag->getId() . ' does already exist. Error is in ' . $metaBag->getSourcePath());
                } else {
                    $this->metaRepository->removeById($item->getId());
                    break;
                }
            }
        }

        $this->assertOutputDir($outputDir, $dest);
        $this->metaRepository->add($metaBag);
    }

    /**
     *
     * @param string $sourceDir
     * @param string $outputDir
     * @param array $excludePaths
     * @return array
     */
    public function gatherMetas(MetaRepository $mr, array $excludePaths)
    {
        $this->metaRepository = $mr;

        $objects = [];

        $bla2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->sourceDir));
        foreach ($bla2 as $file) {
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

        foreach ($objects as $o) {
            $this->addMeta($o, $this->outputDir);
        }
    }
}

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

    /**
     *
     * @var MetaRepository
     */
    protected $metaRepository;

    public function __construct(PathHelper $pathHelper)
    {
        $this->pathHelper = $pathHelper;
        $this->metaRepository = new MetaRepository();
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

        $start = strpos($data, '/* ---');
        $end   = strpos($data, '--- */', $start);

        if ($start === false || $end === false) {
            throw new HorneException('No meta data found in ' . $path);
        }

        $jsonString = substr($data, $start + 6, $end - ($start + 6));

        $jsonArray = json_decode($jsonString, true);

        if ($jsonArray === null) {
            throw new HorneException('Meta data in ' . $path . ' seems to be invalid');
        }

        return $jsonArray;
    }

    public function addMeta($o, $outputDir)
    {
        if (strtolower(pathinfo($o['path'], PATHINFO_EXTENSION)) !== 'phtml') {
            $dest = $outputDir . '/' . substr($o['path'], strlen($o['root']) + 1);
            $this->assertOutputDir($outputDir, $dest);
            $this->metaRepository->add(new MetaBag($o['path'], $dest, [
                'id' => $o['path'],
                'type' => 'asset'
            ]));
        } else {
            $data = $this->getJsonMetaDataFromFile($o['path']);

            if (!isset($data['type'])) {
                $data['type'] = 'page';
                //throw new Exception('No type in ' . $source);
            }

            if (!isset($data['path'])) {
                $data['path'] = substr($o['path'], strlen($o['root']));
                $data['path'] = preg_replace('/phtml$/', 'html', $data['path']);
            }

            if (!isset($data['id'])) {
                $data['id'] = $data['path'];
            }

            if (!isset($data['type'])) {
                $data['type'] = 'page';
            }

            $dest = $this->pathHelper->normalize($outputDir . '/' . $data['path']);

            $this->assertOutputDir($outputDir, $dest);
            $this->metaRepository->add(new MetaBag($o['path'], $dest, $data));
        }
    }

    /**
     *
     * @param string $sourceDir
     * @param string $outputDir
     * @param array $excludePaths
     * @return array
     */
    public function gatherMetas($sourceDir, $outputDir, array $excludePaths)
    {
        $objects = [];

        $bla2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir));
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
                'root' => $sourceDir
            ];
        }

        foreach ($objects as $o) {
            $this->addMeta($o, $outputDir);
        }

        return $this->metaRepository;
    }
}

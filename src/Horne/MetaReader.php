<?php

namespace Horne;

use Kaloa\Filesystem\PathHelper;

class MetaReader
{
    /**
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     * @var string
     */
    protected $outputDir;

    /**
     * @param PathHelper $pathHelper
     * @param string     $outputDir
     */
    public function __construct(PathHelper $pathHelper, $outputDir)
    {
        $this->pathHelper = $pathHelper;
        $this->outputDir  = $outputDir;
    }

    /**
     * @param string $path
     *
     * @return array
     * @throws HorneException
     */
    private function getJsonMetaDataFromFile($path)
    {
        $data = file_get_contents($path);

        $start = strpos($data, '---');
        $end   = strpos($data, '---', $start + 1);

        if ($start === false || $end === false) {
            throw new HorneException(sprintf(
                'No meta data found in %s',
                $path
            ));
        }

        $jsonString = substr($data, $start + 3, $end - ($start + 3));

        $jsonArray = json_decode($jsonString, true);

        if ($jsonArray === null) {
            throw new HorneException(sprintf(
                'Meta data in %s seems to be invalid',
                $path
            ));
        }

        return $jsonArray;
    }

    /**
     * @param string $outputDir
     * @param string $path
     *
     * @return void
     * @throws HorneException
     */
    private function assertOutputDir($outputDir, $path)
    {
        if (strpos($path, $outputDir) !== 0) {
            throw new HorneException('Path ' . $path . ' not in $outputDir');
        }
    }

    /**
     * @param array $o
     *
     * @return MetaBag|null
     * @throws HorneException
     * @throws \InvalidArgumentException
     */
    public function load(array $o)
    {
        $fileExtension = strtolower(pathinfo($o['path'], PATHINFO_EXTENSION));

        if (in_array($fileExtension, ['md', 'phtml'])) {
            $data = $this->getJsonMetaDataFromFile($o['path']);

            if (!array_key_exists('type', $data)) {
                $data['type'] = 'page';
            }

            if (!isset($data['path']) && in_array($data['type'], ['_script', 'layout'])) {
                $data['path'] = '/dev/null';
            }

            if (!isset($data['path'])) {
                if ($o['root'] === null) {
                    throw new HorneException(sprintf(
                        'Meta data in %s must contain a \'path\' setting',
                        $o['path']
                    ));
                }

                $data['path'] = substr($o['path'], strlen($o['root']));
                $data['path'] = preg_replace(
                    '/\.' . preg_quote($fileExtension, '/') . '$/',
                    '.html',
                    $data['path']
                );
            }
            $data['path'] = str_replace('\\', '/', $data['path']);

            if (!isset($data['id'])) {
                $data['id'] = $data['path'];
            }

            if (!isset($data['type'])) {
                $data['type'] = 'page';
            }

            if (!array_key_exists('layout', $data) && $data['type'] !== 'layout') {
                $data['layout'] = 'horne-layout-page';
            }

            if (isset($data['publish']) && $data['publish'] === false) {
                // Skip files that have the "publish" field set to false
                return null;
            }

            $dest = $this->pathHelper->normalize($this->outputDir . '/' . $data['path']);
        } else {
            $rel = '/' . substr($o['path'], strlen($o['root']) + 1);

            $data = [
                'id'   => $rel,
                'type' => 'asset',
                'path' => $rel,
            ];

            $dest = $this->pathHelper->normalize($this->outputDir . '/' . $rel);
        }

        $this->assertOutputDir($this->outputDir, $dest);

        return new MetaBag($o['path'], $dest, $data);
    }
}

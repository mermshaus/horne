<?php

namespace Horne;

use Kaloa\Filesystem\PathHelper;

/**
 *
 */
class MetaReader
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
    protected $outputDir;

    /**
     *
     * @param PathHelper $pathHelper
     * @param string $outputDir
     */
    public function __construct(
        PathHelper $pathHelper,
        $outputDir
    ) {
        $this->pathHelper = $pathHelper;
        $this->outputDir = $outputDir;
    }

    /**
     *
     * @param string $path
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
     *
     * @param string $outputDir
     * @param string $path
     * @throws HorneException
     */
    private function assertOutputDir($outputDir, $path)
    {
        if (0 !== strpos($path, $outputDir)) {
            throw new HorneException('Path ' . $path . ' not in $outputDir');
        }
    }

    /**
     *
     * @param array $o
     * @throws HorneException
     */
    public function load(array $o)
    {
        $fileExtension = strtolower(pathinfo($o['path'], PATHINFO_EXTENSION));

        switch (true) {
            case in_array($fileExtension, array('md', 'phtml')):
                $data = $this->getJsonMetaDataFromFile($o['path']);

                if (!isset($data['path']) && in_array($data['type'], array('_script', 'layout'))) {
                    $data['path'] = '/dev/null';
                }

                if (!isset($data['path'])) {
                    if (null === $o['root']) {
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
                break;
            default:
                $rel = '/' . substr($o['path'], strlen($o['root']) + 1);

                $data = [
                    'id' => $rel,
                    'type' => 'asset',
                    'path' => $rel
                ];

                $dest = $this->pathHelper->normalize($this->outputDir . '/' . $rel);
                break;
        }

        $this->assertOutputDir($this->outputDir, $dest);

        $metaBag = new MetaBag($o['path'], $dest, $data);

        return $metaBag;
    }
}

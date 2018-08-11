<?php

namespace Horne\Module;

use Horne\Application;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

abstract class AbstractModule implements ModuleInterface
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param array $settings
     *
     * @return array
     */
    public function hookLoadConfig(array $settings)
    {
        return $settings;
    }

    /**
     * @return void
     */
    public function hookProcessingBefore()
    {
    }

    /**
     * @return void
     */
    public function hookProcessingBefore2()
    {
    }

    /**
     * @param string $directory
     *
     * @return void
     * @throws \Horne\HorneException
     * @throws \InvalidArgumentException
     */
    protected function sourceDir($directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            /* @var SplFileInfo $file */

            if ($file->isFile() === false) {
                continue;
            }

            $this->application->source($file->getPathname(), realpath($directory));
        }
    }
}

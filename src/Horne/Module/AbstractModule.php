<?php

namespace Horne\Module;

use Horne\Application;
use Horne\Module\ModuleInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 *
 */
abstract class AbstractModule implements ModuleInterface
{
    /**
     *
     * @var Application
     */
    protected $application;

    /**
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function hookLoadConfig(array $settings)
    {

    }

    public function hookProcessingBefore()
    {

    }

    public function hookProcessingBefore2()
    {

    }

    /**
     *
     * @param string $directory
     */
    protected function sourceDir($directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            /* @var $file SplFileInfo */

            if (false === $file->isFile()) {
                continue;
            }

            $this->application->source($file->getPathname());
        }
    }
}

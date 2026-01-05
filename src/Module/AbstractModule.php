<?php

namespace Horne\Module;

use Horne\Application;

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

    public function hookLoadConfig(array $settings): array
    {
        return $settings;
    }

    public function hookProcessingBefore(): void
    {
    }

    public function hookProcessingBefore2(): void
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
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            /* @var \SplFileInfo $file */

            if ($file->isFile() === false) {
                continue;
            }

            $this->application->source($file->getPathname(), realpath($directory));
        }
    }
}

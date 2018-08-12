<?php

namespace Horne;

class GeSHiRepository
{
    /**
     * @var \GeSHi[]
     */
    protected $geshiInstances = [];

    /**
     * @var \GeSHi
     */
    protected $geshiFallback;

    /**
     * @var \Closure
     */
    protected $geshiFactory;

    /**
     * @param \Closure $geshiFactory
     */
    public function __construct(\Closure $geshiFactory = null)
    {
        if ($geshiFactory === null) {
            $geshiFactory = function ($language) {
                $geshi = new \GeSHi();
                $geshi->enable_classes();
                $geshi->enable_keyword_links(false);
                $geshi->set_language($language);

                return $geshi;
            };
        }

        $this->geshiFactory = $geshiFactory;
    }

    /**
     * @param string $language
     *
     * @return \GeSHi
     * @throws HorneException
     */
    protected function createNewInstance($language)
    {
        $factory = $this->geshiFactory;

        $instance = $factory($language);

        if (!$instance instanceof \GeSHi) {
            throw new HorneException(sprintf(
                'Factory did not return GeSHi instance for language %s',
                $language
            ));
        }

        return $instance;
    }

    /**
     * @param string $language
     *
     * @return \GeSHi
     * @throws \Exception
     */
    public function obtain($language)
    {
        if (!array_key_exists($language, $this->geshiInstances)) {
            $geshi = $this->createNewInstance($language);

            // Will be GESHI_ERROR_NO_SUCH_LANG when language not found
            if (!$geshi->error) {
                $this->geshiInstances[$language] = $geshi;
            } else {
                if ($this->geshiFallback === null) {
                    $this->geshiFallback = $this->createNewInstance('text');
                }
                $this->geshiInstances[$language] = $this->geshiFallback;
            }
        }

        return $this->geshiInstances[$language];
    }

    /**
     * @param string $language
     * @param \GeSHi $geshi
     */
    public function set($language, \GeSHi $geshi)
    {
        $this->geshiInstances[$language] = $geshi;
    }

    /**
     * @param string $language
     *
     * @throws HorneException
     */
    public function clear($language)
    {
        if (!array_key_exists($language, $this->geshiInstances)) {
            throw new HorneException(sprintf(
                'Instance for language %s does not exist',
                $language
            ));
        }
    }

    /**
     * @return void
     */
    public function clearAll()
    {
        $this->geshiInstances = [];
        $this->geshiFallback  = null;
    }

    /**
     * @param string $language
     *
     * @return bool
     */
    public function has($language)
    {
        return array_key_exists($language, $this->geshiInstances);
    }
}

<?php

class MetaRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @throws PHPUnit_Framework_Exception
     */
    public function testInstantiate()
    {
        $metaRepository = new \Horne\MetaRepository('');

        static::assertInstanceOf('Horne\\MetaRepository', $metaRepository);
    }

    /**
     * @throws PHPUnit_Framework_Exception
     * @throws \Horne\HorneException
     */
    public function testAddMetaBag()
    {
        $metaBag = new \Horne\MetaBag('', '', []);

        $metaRepository = new \Horne\MetaRepository('');

        $metaRepository->add($metaBag);

        static::assertCount(1, $metaRepository->getAll());
    }
}

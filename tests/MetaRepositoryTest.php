<?php

declare(strict_types=1);

namespace Horne\Tests;

use Horne\MetaBag;
use Horne\MetaRepository;
use PHPUnit\Framework\TestCase;

class MetaRepositoryTest extends TestCase
{
    public function testInstantiate()
    {
        $metaRepository = new MetaRepository('');

        static::assertInstanceOf(MetaRepository::class, $metaRepository);
    }

    public function testAddMetaBag()
    {
        $metaBag = new MetaBag('', '', []);

        $metaRepository = new MetaRepository('');

        $metaRepository->add($metaBag);

        static::assertCount(1, $metaRepository->getAll());
    }
}

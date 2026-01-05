<?php

declare(strict_types=1);

namespace Horne\OutputFilter;

use Horne\MetaBag;

interface OutputFilterInterface
{
    public function run(string $content, MetaBag $metaBag): string;
}

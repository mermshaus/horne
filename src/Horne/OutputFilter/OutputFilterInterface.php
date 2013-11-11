<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;

interface OutputFilterInterface
{
    public function run($content, MetaBag $mb);
}

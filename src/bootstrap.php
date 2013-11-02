<?php

use Horne\Application;
use Horne\OutputFilter\InigoOutputFilter;
use Horne\OutputFilter\KramdownOutputFilter;
use Horne\OutputFilter\TableOfContentsOutputFilter;
use Horne\OutputFilter\UppercaseOutputFilter;
use Horne\OutputFilter\XmlOutputFilter;

require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../vendor/geshi/geshi.php')) {
    require_once __DIR__ . '/../vendor/geshi/geshi.php';
}

$horne = new Application();

$horne->setFilters('toc', [
    new TableOfContentsOutputFilter()
]);

$horne->setFilters('uppercase', [
    new UppercaseOutputFilter()
]);

$horne->setFilters('kramdown', [
    new KramdownOutputFilter()
]);

$horne->setFilters('inigo', [
    new InigoOutputFilter()
]);

$horne->setFilters('xml', [
    new XmlOutputFilter()
]);

return $horne;

<?php

namespace Horne;

use Horne\OutputFilter;

$horne = new Application();

$horne->setSyntaxHighlighter(new GeSHiSyntaxHighlighter(new GeSHiRepository()));

$horne->setFilters('toc', [new OutputFilter\TableOfContentsOutputFilter($horne)]);
$horne->setFilters('uppercase', [new OutputFilter\UppercaseOutputFilter($horne)]);
$horne->setFilters('kramdown', [new OutputFilter\KramdownOutputFilter($horne)]);
$horne->setFilters('inigo', [new OutputFilter\InigoOutputFilter($horne)]);
$horne->setFilters('xml', [new OutputFilter\XmlOutputFilter($horne)]);
$horne->setFilters('xmllegacy', [new OutputFilter\XmlLegacyOutputFilter($horne)]);

return $horne;

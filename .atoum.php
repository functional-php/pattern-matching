<?php

use \mageekguy\atoum;

$report = $script->addDefaultReport();

$coverageField = new atoum\report\fields\runner\coverage\html('Pattern Matching', './reports/');
$report->addField($coverageField);

$cloverWriter = new atoum\writers\file('./reports/atoum.coverage.xml');
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);
$runner->addReport($cloverReport);

$runner->addTestsFromDirectory('./tests');

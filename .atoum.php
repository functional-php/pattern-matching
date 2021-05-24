<?php

use atoum\atoum\reports;
use atoum\atoum\reports\coverage;
use atoum\atoum\writers\std;

$script->addDefaultReport();

$clover = new \atoum\atoum\reports\sonar\clover();
$writer = new \atoum\atoum\writers\file('coverage.xml');
$clover->addWriter($writer);
$runner->addReport($clover);
$runner->addTestsFromDirectory('./tests');

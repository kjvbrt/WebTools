<?php
require('../config.php');

$layer = 'table';
$acc = 'fcc-hh';
$evtType = 'full-sim';
$genType = 'none';
$campaign = 'v04';

$dataFilePath = BASE_PATH . '/data/FCChh/FCCsim_v04.txt';
$description = 'FCC-hh Full Simulation v0.4 in EDM4hep format.';
?>

<?php require(BASE_PATH . '/fcc-hh/page.php') ?>

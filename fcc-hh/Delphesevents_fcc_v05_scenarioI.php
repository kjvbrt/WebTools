<?php
require('../config.php');

$layer = 'table';
$acc = 'fcc-hh';
$evtType = 'delphes';
$genType = 'none';
$campaign = 'v05-scenarioI';

$dataFilePath = BASE_PATH . '/data/FCChh/Delphesevents_fcc_v05_scenarioI.txt';
$description = 'Delphes FCC-hh Physics events v0.5 scenario I. in EDM4Hep format.';
?>

<?php require(BASE_PATH . '/fcc-hh/page.php') ?>

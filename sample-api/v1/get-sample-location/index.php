<?php
require('../../../config.php');

$apiVersion = 1;

$response = array();
$response['data'] = array();
$data = &$response['data'];
header('Content-Type: application/json; charset=utf-8');

// Accelerator
$acc = htmlspecialchars($_GET["accelerator"]);
if ($acc !== 'fcc-hh' && $acc !== 'fcc-ee') {
  $response = array(
    'status' => 'error',
    'message' => 'Accelerator not recognized!'
  );
  exit(json_encode($response));
}
$data['accelerator'] = $acc;

// Event Type
$evtType = htmlspecialchars($_GET["event-type"]);
if ($evtType !== 'gen' && $evtType !== 'delphes' && $evtType !== 'full-sim') {
  $response = array(
    'status' => 'error',
    'message' => 'Event type not recognized!'
  );
  exit(json_encode($response));
}
$data['event-type'] = $evtType;

// File Type

// Campaign
$campaign = htmlspecialchars($_GET["campaign"]);
if (!array_key_exists($campaign, $campaignNames)) {
  $response = array(
    'status' => 'error',
    'message' => 'Campaign not recognized!'
  );
  exit(json_encode($response));
}
$data['campaign'] = $campaign;

// Detector
$detector = htmlspecialchars($_GET["detector"]);
if (!array_key_exists($detector, $detectorNames)) {
  $response = array(
    'status' => 'error',
    'message' => 'Detector not recognized!'
  );
  exit(json_encode($response));
}
$data['detector'] = $detector;

// Process name
$procName = htmlspecialchars($_GET["process-name"]);
if (strlen($procName) > 256) {
  $response = array(
    'status' => 'error',
    'message' => 'Process name too long!'
  );
  exit(json_encode($response));
}
if (strlen($procName) < 4) {
  $response = array(
    'status' => 'error',
    'message' => 'Process name is too short! Provide at least 3 characters'
  );
  exit(json_encode($response));
}
if (preg_match('/[^a-zA-Z0-9_-]+/', $procName)) {
  $response = array(
    'status' => 'error',
    'message' => 'Process name contains special characters!'
  );
  exit(json_encode($response));
}
$data['process-name'] = $procName;

// Decide which input file to search
if ($acc === 'fcc-ee') {
  if ($evtType === 'delphes') {
    if ($campaign === 'winter2023') {
      if ($detector === 'idea') {
        $dataFilePath = BASE_PATH . '/data/FCCee/Delphesevents_winter2023_IDEA.txt';
        $description = 'Delphes FCCee Physics events winter2023 production (IDEA Detector).';
      }
      if ($detector === 'idea-3t') {
        $dataFilePath = BASE_PATH . '/data/FCCee/Delphesevents_winter2023_IDEA3T.txt';
        $description = 'Delphes FCCee Physics events winter2023 production (IDEA 3T Detector).';
      }
    }
  }
}

// Search input file
$colNames = array();
if ($evtType === 'gen') {
  $colNames = array('name', 'n-events',
                    'n-files', 'n-files-bad', 'n-files-eos', 'size',
                    'path', 'main-process', 'final-states',
                    'matching-param',
                    'cross-section');
}
if ($evtType === 'delphes') {
  $colNames = array('name', 'n-events', 'sum-of-weights',
                    'n-files', 'n-files-bad', 'n-files-eos', 'size',
                    'path', 'main-process', 'final-states',
                    'cross-section',
                    'k-factor', 'matching-eff');
}
if ($evtType === 'full-sim' && $acc === 'fcc-hh') {
  $colNames = array('name', 'n-events', 'n-files', 'n-files-eos',
                    'n-files-bad', 'size',
                    'aleksa', 'azaborow', 'cneubuse', 'djamin', 'helsens',
                    'jhrdinka', 'jkiesele', 'novaj', 'rastein', 'selvaggi',
                    'vavolkl');
}

$txt_file = file_get_contents($dataFilePath);
$data['last-update'] = filemtime($dataFilePath);

$rows = explode("\n", $txt_file);

$samples = array();
$nColsExpected = count($colNames);
foreach($rows as $rowId => $row) {
  // get row items
  $rowItems = explode(',,', $row);
  $nCols = count($rowItems);

  // Exclude total row
  if ($nCols > 1) {
    if ($rowItems[0] === 'total') {
      continue;
    }
  }

  // Exclude non-standard rows
  if ($nCols != $nColsExpected) {
    continue;
  }

  // Parse row
  $samples[$rowItems[0]] = array();
  for ($i = 1; $i < $nCols; $i++) {
    $samples[$rowItems[0]][$colNames[$i]] = $rowItems[$i] ?? '';
  }
}

if (!array_key_exists($procName, $samples)) {
  $response = array(
    'status' => 'error',
    'message' => 'Process name not found!'
  );
  exit(json_encode($response));
}

$data['location'] = $samples[$procName]['path'];

$response['status'] = 'success';
if (!array_key_exists('message', $response)) {
  $response['message'] = 'All OK.';
}
echo json_encode($response);
?>

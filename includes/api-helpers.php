<?php
// Parse input query string

function parseQueryString() {
  $data = array();

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
  if ($evtType !== 'gen' &&
      $evtType !== 'delphes' &&
      $evtType !== 'full-sim') {
    $response = array(
      'status' => 'error',
      'message' => 'Event type not recognized!'
    );
    exit(json_encode($response));
  }
  $data['event-type'] = $evtType;

  // File Type
  $fileType = htmlspecialchars($_GET["file-type"]);
  if ($fileType !== 'lhe' &&
      $fileType !== 'stdhep' &&
      $fileType !== 'edm4hep-root') {
    $response = array(
      'status' => 'error',
      'message' => 'File type not recognized!'
    );
    exit(json_encode($response));
  }
  $data['file-type'] = $fileType;

  // Campaign
  $campaign = htmlspecialchars($_GET["campaign"]);
  if (!array_key_exists($campaign, $GLOBALS['campaignNames'])) {
    $response = array(
      'status' => 'error',
      'message' => 'Campaign not recognized!'
    );
    exit(json_encode($response));
  }
  $data['campaign'] = $campaign;

  // Detector
  $det = htmlspecialchars($_GET["detector"]);
  if (!array_key_exists($det, $GLOBALS['detectorNames'])) {
    $response = array(
      'status' => 'error',
      'message' => 'Detector not recognized!'
    );
    exit(json_encode($response));
  }
  $data['detector'] = $det;

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

  return $data;
}


// Decide which input file to search
function getEvtProdDataFile($data) {
  if ($data['accelerator'] === 'fcc-ee') {
    switch ($data['event-type']) {
      case 'gen':
        if ($data['file-type'] === 'stdhep') {
          $dataFilePath = BASE_PATH . '/data/FCCee/STDHEP_events';
          $dataFilePath .= '_' . $GLOBALS['campaignTags'][$data['campaign']];
          $dataFilePath .= '.txt';
          break;
        }
        if ($data['file-type'] === 'lhe') {
          $dataFilePath = BASE_PATH . '/data/FCCee/LHEevents.txt';
          break;
        }
        $response = array(
          'status' => 'error',
          'message' => 'Unable to find sample data file!'
        );
        exit(json_encode($response));
        break;
      case 'delphes':
        $dataFilePath = BASE_PATH . '/data/FCCee/Delphesevents';
        $dataFilePath .= '_' . $GLOBALS['campaignTags'][$data['campaign']];
        $dataFilePath .= '_' . str_replace(' ', '_', $GLOBALS['detectorNames'][$data['detector']]);
        $dataFilePath .= '.txt';
        break;
      case 'full-sim':
        $response = array(
          'status' => 'error',
          'message' => 'Not implemented yet!'
        );
        exit(json_encode($response));
        break;
      default;
        $response = array(
          'status' => 'error',
          'message' => 'Unable to find sample data file!'
        );
        exit(json_encode($response));
    }
  }

  return $dataFilePath;
}


// Get samples array produced by Event Producer
function getEvtProdPSampleArray($dataFilePath, &$data) {
  $colNames = array();
  if ($data['event-type'] === 'gen') {
    $colNames = array('name', 'n-events',
                      'n-files', 'n-files-bad', 'n-files-eos', 'size',
                      'path', 'main-process', 'final-states',
                      'matching-param',
                      'cross-section');
  }
  if ($data['event-type'] === 'delphes') {
    $colNames = array('name', 'n-events', 'sum-of-weights',
                      'n-files', 'n-files-bad', 'n-files-eos', 'size',
                      'path', 'main-process', 'final-states',
                      'cross-section',
                      'k-factor', 'matching-eff');
  }
  if ($data['event-type'] === 'full-sim' && $data['accelerator'] === 'fcc-hh') {
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

  return $samples;
}
?>

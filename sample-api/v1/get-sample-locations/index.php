<?php
require('../../../config.php');
require('../../../includes/api-helpers.php');

$apiVersion = 1;

$response = array();
header('Content-Type: application/json; charset=utf-8');

$data = parseQueryString();

// Decide which input file to search
$dataFilePath = getEvtProdDataFile($data);

// Search input file
$samples = getEvtProdPSampleArray($dataFilePath, $data);

if (!array_key_exists($data['process-name'], $samples)) {
  $response = array(
    'status' => 'error',
    'message' => 'Process name not found!'
  );
  exit(json_encode($response));
}

$locations = array();
$locations['cern'] = $samples[$data['process-name']]['path'];
$data['locations'] = $locations;

// Build response
$response['data'] = $data;
$response['status'] = 'success';
if (!array_key_exists('message', $response)) {
  $response['message'] = 'All OK.';
}
// error_log(print_r($response, true));
echo json_encode($response);
?>

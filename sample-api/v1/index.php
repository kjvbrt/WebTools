<?php
require('../../config.php');

$apiVersion = 1;

$response = array(
  'status' => 'success',
  'message' => 'This is FCC Physics Events API v' . $apiVersion,
  'contact' => 'FCC-PED-SoftwareAndComputing-Analysis@cern.ch'
);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>

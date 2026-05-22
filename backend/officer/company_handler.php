<?php
require '../../utilities/database/database.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["success" => false, "message" => "Invalid request method"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  echo json_encode(["success" => false, "message" => "Invalid JSON"]);
  exit;
}

try {
  $stmt = $conn->prepare("
    UPDATE companies SET
      company_name = ?,
      company_email = ?,
      company_website = ?,
      company_address = ?,
      intern_allowance = ?,
      partnership_status = ?,
      max_internslots = ?,
      revenue_growth = ?,
      profit_margins = ?,
      roi = ?,
      roa = ?
    WHERE company_id = ?
  ");

  $stmt->execute([
    $data['company_name'],
    $data['company_email'],
    $data['company_website'],
    $data['company_address'],
    $data['intern_allowance'],
    $data['partnership_status'],
    $data['max_internslots'],
    $data['revenue_growth'],
    $data['profit_margins'],
    $data['roi'],
    $data['roa'],
    $data['company_id']
  ]);

  echo json_encode(["success" => true]);

} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
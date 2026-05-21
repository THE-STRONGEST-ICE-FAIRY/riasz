<?php
require '../../utilities/database/database.php';

$stmt = $conn->query("SELECT program_id, program_name, school_id FROM programs");

echo json_encode($stmt->fetchAll());
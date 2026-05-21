<?php
require '../../utilities/database/database.php';

$stmt = $conn->query("SELECT school_id, school_name FROM schools");

echo json_encode($stmt->fetchAll());
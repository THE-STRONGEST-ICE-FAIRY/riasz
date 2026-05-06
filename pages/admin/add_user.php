<?php
require '../../utilities/database/database.php';

try {

    $conn->beginTransaction();

    $first = $_POST["first_name"];
    $last = $_POST["last_name"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $given_id = $_POST["schooluser_given_id"] ?? null;

    // USERS
    $stmt = $conn->prepare("
        INSERT INTO users 
        (user_first_name, user_last_name, user_email, user_date_created, user_date_updated, user_role, user_is_archived)
        VALUES (?, ?, ?, NOW(), NOW(), ?, 0)
    ");
    $stmt->execute([$first, $last, $email, $role]);

    $user_id = $conn->lastInsertId();

    // SCHOOL USERS
    $stmt = $conn->prepare("
        INSERT INTO schoolusers 
        (user_id, schooluser_given_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$user_id, $given_id]);

    $schooluser_id = $conn->lastInsertId();

    // ROLE LOGIC
    if ($role === "program") {
        $stmt = $conn->prepare("
            INSERT INTO programdirectors (schooluser_id, program_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$schooluser_id, $_POST["program_id"]]);
    }

    if ($role === "executive") {
        $stmt = $conn->prepare("
            INSERT INTO executivedirectors (schooluser_id, school_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$schooluser_id, $_POST["school_id"]]);
    }

    $conn->commit();

    exit;

} catch (Exception $e) {
    $conn->rollBack();
    die("Error: " . $e->getMessage());
}

?>
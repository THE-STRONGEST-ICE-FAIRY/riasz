<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$userId = (int)($_SESSION['user_id'] ?? 0);
$previousLoginAt = $_SESSION['previous_login_at'] ?? null;
$latestLoginAt = null;

if (($previousLoginAt === null || $previousLoginAt === '') && $userId > 0) {
    require '../../utilities/database/database.php';
    $stmt = $conn->prepare("
        SELECT userlog_timestamp
        FROM userlogs
        WHERE user_id = ? AND userlog_type = 'login'
        ORDER BY userlog_timestamp DESC, userlog_id DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $latestLoginAt = $stmt->fetchColumn() ?: '';
    if ($previousLoginAt === null) {
        $previousLoginAt = $latestLoginAt;
    }
}

$timestamp = is_string($previousLoginAt) && $previousLoginAt !== ''
    ? strtotime($previousLoginAt)
    : false;
$hasPreviousLogin = $timestamp !== false;
$message = $userId > 0
    ? 'No previous login is recorded.'
    : 'Sign in to view login history.';

if (!$hasPreviousLogin && is_string($latestLoginAt) && $latestLoginAt !== '') {
    $latestTimestamp = strtotime($latestLoginAt);
    if ($latestTimestamp !== false) {
        $message = 'Your first recorded sign-in was on ' . date('l, F d, Y', $latestTimestamp) . '.';
    }
}

echo json_encode([
    'status' => 'success',
    'has_previous_login' => $hasPreviousLogin,
    'label' => $hasPreviousLogin ? date('l, F d, Y', $timestamp) : '',
    'message' => $message,
], JSON_INVALID_UTF8_SUBSTITUTE);

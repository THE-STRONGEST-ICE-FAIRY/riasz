<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data sent from the JavaScript fetch request
    $title = isset($_POST['title']) ? trim($_POST['title']) : 'Untitled Task';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $startTime = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $endTime = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    
    if ($action === 'create_notification') {
        // TODO: Insert your database logic here to save the event/task
        // e.g., INSERT INTO notifications (title, date, created_at) VALUES (...)
        
        // Return a success response back to the front-end
        echo json_encode([
            'status' => 'success',
            'message' => 'Notification successfully created.',
            'data' => [
                'title' => $title,
                'date'  => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]
        ]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit;
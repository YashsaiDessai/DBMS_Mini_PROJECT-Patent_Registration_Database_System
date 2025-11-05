<?php
// create_applicant.php — returns only JSON, logs errors instead of printing them

// Turn off display errors (don't leak HTML error pages to the client)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Optional: buffer output so stray whitespace won't break JSON
ob_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'POST required']);
        ob_end_flush();
        exit;
    }

    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $organization = isset($_POST['organization']) ? trim($_POST['organization']) : '';

    if ($name === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Name is required']);
        ob_end_flush();
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO applicants (name, email, organization) VALUES (?, ?, ?)");
    if (!$stmt) {
        // log the error server-side
        error_log("DB prepare failed: " . $mysqli->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error']);
        ob_end_flush();
        exit;
    }

    $stmt->bind_param('sss', $name, $email, $organization);
    if (!$stmt->execute()) {
        error_log("DB execute failed: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error']);
        $stmt->close();
        ob_end_flush();
        exit;
    }

    $newId = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();

    // send clean JSON only
    echo json_encode(['success' => true, 'id' => $newId]);

} catch (Throwable $t) {
    error_log("Unhandled exception in create_applicant.php: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

// flush and end output buffering
ob_end_flush();

<?php
// backend/upload_document.php
// Accepts a multipart/form-data POST with fields: patent_id, file (input name "file")
// Returns JSON only; logs server-side errors.

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success'=>false,'error'=>'POST required']);
        ob_end_flush();
        exit;
    }

    $patent_id = isset($_POST['patent_id']) ? trim($_POST['patent_id']) : '';
    if ($patent_id === '') {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'patent_id required']);
        ob_end_flush();
        exit;
    }

    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'file required']);
        ob_end_flush();
        exit;
    }

    $file = $_FILES['file'];

    // Basic checks
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'upload error code: '.$file['error']]);
        ob_end_flush();
        exit;
    }

    // Limit file size (example: 10 MB)
    $maxBytes = 10 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'file too large']);
        ob_end_flush();
        exit;
    }

    // Optional: restrict MIME types / extensions (example allow pdf, docx, txt, png, jpg)
    $allowedExt = ['pdf','docx','doc','txt','png','jpg','jpeg'];
    $origName = basename($file['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'file type not allowed']);
        ob_end_flush();
        exit;
    }

    // Prepare upload folder (outside web root if possible; here we keep in backend/uploads)
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Create a safe stored filename
    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $origName);
    $targetPath = $uploadDir . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        http_response_code(500);
        error_log("upload_document.php move_uploaded_file failed for " . $file['tmp_name']);
        echo json_encode(['success'=>false,'error'=>'failed to move uploaded file']);
        ob_end_flush();
        exit;
    }

    // Save metadata to DB (filepath saved relative to backend folder)
    $filepath = 'backend/uploads/' . $safeName;
    $stmt = $mysqli->prepare("INSERT INTO documents (patent_id, filename, filepath) VALUES (?, ?, ?)");
    if (!$stmt) {
        error_log("upload_document.php prepare failed: " . $mysqli->error);
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Server error']);
        ob_end_flush();
        exit;
    }
    $stmt->bind_param('sss', $patent_id, $origName, $filepath);
    if (!$stmt->execute()) {
        error_log("upload_document.php execute failed: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Server error']);
        $stmt->close();
        ob_end_flush();
        exit;
    }

    $newId = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();

    echo json_encode(['success'=>true,'id'=>$newId,'filename'=>$origName,'path'=>$filepath]);

} catch (Throwable $t) {
    error_log("upload_document.php exception: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Server error']);
}
ob_end_flush();

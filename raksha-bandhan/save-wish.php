<?php
/**
 * Appends a wish to wishes.txt on the server.
 * Requires PHP hosting (upload this folder to any PHP host).
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'POST only']);
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['text'])) {
  // also accept form-encoded
  $text = isset($_POST['text']) ? trim($_POST['text']) : '';
} else {
  $text = trim($data['text']);
}

$text = str_replace(["\r", "\n"], ' ', $text);
$text = strip_tags($text);
if ($text === '' || mb_strlen($text) > 500) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid wish']);
  exit;
}

$file = __DIR__ . '/wishes.txt';
$line = date('Y-m-d H:i:s') . ' | ' . $text . PHP_EOL;

if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Could not write wishes.txt — check folder permissions (chmod 666 wishes.txt or 755 folder)']);
  exit;
}

echo json_encode(['ok' => true, 'saved' => $text]);

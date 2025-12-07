<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$topicsFile = 'topics.json';
$commentsFile = 'comments.json';

$topics = json_decode(file_get_contents($topicsFile), true);
$comments = json_decode(file_get_contents($commentsFile), true);

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit();
}

function sanitizeInput($data) {
    if (!is_string($data)) return $data;
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// ============================
// Topics Functions
// ============================

function getAllTopicsJSON($topics) {
    sendResponse(["success" => true, "data" => $topics]);
}

function getTopicByIdJSON($topics, $id) {
    foreach ($topics as $topic) {
        if ($topic['id'] === $id) {
            sendResponse(["success" => true, "data" => $topic]);
        }
    }
    sendResponse(["success" => false, "message" => "Topic not found"], 404);
}

function createTopicJSON(&$topics, $topicsFile, $data) {
    if (empty($data['id']) || empty($data['subject']) || empty($data['message']) || empty($data['author'])) {
        sendResponse(["success" => false, "message" => "Missing required fields"], 400);
    }

    // check duplicate
    foreach ($topics as $topic) {
        if ($topic['id'] === $data['id']) {
            sendResponse(["success" => false, "message" => "Topic ID already exists"], 409);
        }
    }

    $newTopic = [
        "id" => sanitizeInput($data['id']),
        "subject" => sanitizeInput($data['subject']),
        "message" => sanitizeInput($data['message']),
        "author" => sanitizeInput($data['author']),
        "date" => date('Y-m-d')
    ];

    $topics[] = $newTopic;
    file_put_contents($topicsFile, json_encode($topics, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Topic created", "data" => $newTopic], 201);
}

function deleteTopicJSON(&$topics, $topicsFile, &$comments, $commentsFile, $id) {
    $found = false;
    foreach ($topics as $index => $topic) {
        if ($topic['id'] === $id) {
            array_splice($topics, $index, 1);
            $found = true;
            break;
        }
    }

    if (!$found) sendResponse(["success" => false, "message" => "Topic not found"], 404);

    // delete comments
    if (isset($comments[$id])) unset($comments[$id]);

    file_put_contents($topicsFile, json_encode($topics, JSON_PRETTY_PRINT));
    file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Topic deleted"]);
}

// ============================
// Replies Functions
// ============================

function getRepliesByTopicIdJSON($comments, $topicId) {
    $replies = $comments[$topicId] ?? [];
    sendResponse(["success" => true, "data" => $replies]);
}

function createReplyJSON(&$comments, $commentsFile, $data) {
    if (empty($data['topic_id']) || empty($data['id']) || empty($data['author']) || empty($data['text'])) {
        sendResponse(["success" => false, "message" => "Missing required fields"], 400);
    }

    $reply = [
        "id" => sanitizeInput($data['id']),
        "author" => sanitizeInput($data['author']),
        "date" => date('Y-m-d'),
        "text" => sanitizeInput($data['text'])
    ];

    if (!isset($comments[$data['topic_id']])) $comments[$data['topic_id']] = [];

    // check duplicate reply_id
    foreach ($comments[$data['topic_id']] as $r) {
        if ($r['id'] === $data['id']) {
            sendResponse(["success" => false, "message" => "Reply ID already exists"], 409);
        }
    }

    $comments[$data['topic_id']][] = $reply;
    file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Reply added", "data" => $reply], 201);
}

function deleteReplyJSON(&$comments, $commentsFile, $replyId, $topicId) {
    if (!isset($comments[$topicId])) sendResponse(["success" => false, "message" => "Topic not found"], 404);

    $found = false;
    foreach ($comments[$topicId] as $index => $reply) {
        if ($reply['id'] === $replyId) {
            array_splice($comments[$topicId], $index, 1);
            $found = true;
            break;
        }
    }

    if (!$found) sendResponse(["success" => false, "message" => "Reply not found"], 404);

    file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Reply deleted"]);
}

// ============================
// Main Router
// ============================

$resource = $_GET['resource'] ?? '';
$id = $_GET['id'] ?? '';
$topic_id = $_GET['topic_id'] ?? '';

switch ($resource) {
    case 'topics':
        if ($method === 'GET') $id ? getTopicByIdJSON($topics, $id) : getAllTopicsJSON($topics);
        elseif ($method === 'POST') createTopicJSON($topics, $topicsFile, $input);
        elseif ($method === 'DELETE') deleteTopicJSON($topics, $topicsFile, $comments, $commentsFile, $id);
        else sendResponse(["success" => false, "message" => "Method not allowed"], 405);
        break;

    case 'replies':
        if ($method === 'GET') getRepliesByTopicIdJSON($comments, $topic_id);
        elseif ($method === 'POST') createReplyJSON($comments, $commentsFile, $input);
        elseif ($method === 'DELETE') deleteReplyJSON($comments, $commentsFile, $id, $topic_id);
        else sendResponse(["success" => false, "message" => "Method not allowed"], 405);
        break;

    default:
        sendResponse(["success" => false, "message" => "Invalid resource"], 400);
}
?>

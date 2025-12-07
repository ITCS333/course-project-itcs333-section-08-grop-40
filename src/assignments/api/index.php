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

$assignmentsFile = 'assignments.json';
$commentsFile = 'comments.json';

$assignments = json_decode(file_get_contents($assignmentsFile), true);
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
// Assignments Functions
// ============================

function getAllAssignmentsJSON($assignments) {
    sendResponse(["success" => true, "data" => $assignments]);
}

function getAssignmentByIdJSON($assignments, $id) {
    foreach ($assignments as $asg) {
        if ($asg['id'] === $id) {
            sendResponse(["success" => true, "data" => $asg]);
        }
    }
    sendResponse(["success" => false, "message" => "Assignment not found"], 404);
}

function createAssignmentJSON(&$assignments, $assignmentsFile, $data) {
    if (empty($data['id']) || empty($data['title']) || empty($data['description']) || empty($data['dueDate'])) {
        sendResponse(["success" => false, "message" => "Missing required fields"], 400);
    }

    // check duplicate
    foreach ($assignments as $asg) {
        if ($asg['id'] === $data['id']) {
            sendResponse(["success" => false, "message" => "Assignment ID already exists"], 409);
        }
    }

    $newAssignment = [
        "id" => sanitizeInput($data['id']),
        "title" => sanitizeInput($data['title']),
        "description" => sanitizeInput($data['description']),
        "dueDate" => sanitizeInput($data['dueDate']),
        "files" => isset($data['files']) ? array_map('sanitizeInput', $data['files']) : []
    ];

    $assignments[] = $newAssignment;
    file_put_contents($assignmentsFile, json_encode($assignments, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Assignment created", "data" => $newAssignment], 201);
}

function updateAssignmentJSON(&$assignments, $assignmentsFile, $data) {
    if (empty($data['id'])) sendResponse(["success" => false, "message" => "Assignment ID is required"], 400);

    $found = false;
    foreach ($assignments as &$asg) {
        if ($asg['id'] === $data['id']) {
            $found = true;
            if (isset($data['title'])) $asg['title'] = sanitizeInput($data['title']);
            if (isset($data['description'])) $asg['description'] = sanitizeInput($data['description']);
            if (isset($data['dueDate'])) $asg['dueDate'] = sanitizeInput($data['dueDate']);
            if (isset($data['files'])) $asg['files'] = array_map('sanitizeInput', $data['files']);
            break;
        }
    }

    if (!$found) sendResponse(["success" => false, "message" => "Assignment not found"], 404);

    file_put_contents($assignmentsFile, json_encode($assignments, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Assignment updated"]);
}

function deleteAssignmentJSON(&$assignments, $assignmentsFile, &$comments, $commentsFile, $id) {
    $found = false;
    foreach ($assignments as $index => $asg) {
        if ($asg['id'] === $id) {
            array_splice($assignments, $index, 1);
            $found = true;
            break;
        }
    }

    if (!$found) sendResponse(["success" => false, "message" => "Assignment not found"], 404);

    if (isset($comments[$id])) unset($comments[$id]);

    file_put_contents($assignmentsFile, json_encode($assignments, JSON_PRETTY_PRINT));
    file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Assignment deleted"]);
}

// ============================
// Comments Functions
// ============================

function getCommentsByAssignmentJSON($comments, $assignmentId) {
    $replies = $comments[$assignmentId] ?? [];
    sendResponse(["success" => true, "data" => $replies]);
}

function createCommentJSON(&$comments, $commentsFile, $data) {
    if (empty($data['assignment_id']) || empty($data['author']) || empty($data['text'])) {
        sendResponse(["success" => false, "message" => "Missing required fields"], 400);
    }

    $reply = [
        "author" => sanitizeInput($data['author']),
        "text" => sanitizeInput($data['text'])
    ];

    if (!isset($comments[$data['assignment_id']])) $comments[$data['assignment_id']] = [];

    $comments[$data['assignment_id']][] = $reply;
    file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Comment added", "data" => $reply], 201);
}

function deleteCommentJSON(&$comments, $commentsFile, $assignmentId, $index) {
    if (!isset($comments[$assignmentId])) sendResponse(["success" => false, "message" => "Assignment not found"], 404);

    if (!isset($comments[$assignmentId][$index])) sendResponse(["success" => false, "message" => "Comment not found"], 404);

    array_splice($comments[$assignmentId], $index, 1);
    file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
    sendResponse(["success" => true, "message" => "Comment deleted"]);
}

// ============================
// Router
// ============================

$resource = $_GET['resource'] ?? '';
$id = $_GET['id'] ?? '';
$assignment_id = $_GET['assignment_id'] ?? '';
$comment_index = $_GET['comment_index'] ?? '';

switch ($resource) {
    case 'assignments':
        if ($method === 'GET') $id ? getAssignmentByIdJSON($assignments, $id) : getAllAssignmentsJSON($assignments);
        elseif ($method === 'POST') createAssignmentJSON($assignments, $assignmentsFile, $input);
        elseif ($method === 'PUT') updateAssignmentJSON($assignments, $assignmentsFile, $input);
        elseif ($method === 'DELETE') deleteAssignmentJSON($assignments, $assignmentsFile, $comments, $commentsFile, $id);
        else sendResponse(["success" => false, "message" => "Method not allowed"], 405);
        break;

    case 'comments':
        if ($method === 'GET') getCommentsByAssignmentJSON($comments, $assignment_id);
        elseif ($method === 'POST') createCommentJSON($comments, $commentsFile, $input);
        elseif ($method === 'DELETE') deleteCommentJSON($comments, $commentsFile, $assignment_id, $comment_index);
        else sendResponse(["success" => false, "message" => "Method not allowed"], 405);
        break;

    default:
        sendResponse(["success" => false, "message" => "Invalid resource"], 400);
}
?>


?>

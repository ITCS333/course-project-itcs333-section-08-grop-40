<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

try {
$pdo = new PDO("mysql:host=localhost;dbname=test", "user", "pass");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = :id");
    $stmt->execute([':id' => 1]); 
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';


}catch(PDOException $e){
sendResponse(["success" => false, "message" => "Database error: " . $e->getMessage()], 500);

}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$assignmentsFile = 'assignments.json';
$commentsFile = 'comments.json';

try {
    $assignments = json_decode(file_get_contents($assignmentsFile), true);
    if ($assignments === null) $assignments = [];
    $comments = json_decode(file_get_contents($commentsFile), true);
    if ($comments === null) $comments = [];
} catch (Exception $e) {
    sendResponse(["success" => false, "message" => "Failed to read data files: ".$e->getMessage()], 500);
}

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
    try {
        sendResponse(["success" => true, "data" => $assignments]);
    } catch (Exception $e) {
        sendResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
}

function getAssignmentByIdJSON($assignments, $id) {
    try {
        foreach ($assignments as $asg) {
            if ($asg['id'] === $id) {
                sendResponse(["success" => true, "data" => $asg]);
            }
        }
        sendResponse(["success" => false, "message" => "Assignment not found"], 404);
    } catch (Exception $e) {
        sendResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
}

function createAssignmentJSON(&$assignments, $assignmentsFile, $data) {
    try {
        if (empty($data['id']) || empty($data['title']) || empty($data['description']) || empty($data['dueDate'])) {
            sendResponse(["success" => false, "message" => "Missing required fields"], 400);
        }

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

        if (file_put_contents($assignmentsFile, json_encode($assignments, JSON_PRETTY_PRINT)) === false) {
            throw new Exception("Failed to write assignments file");
        }

        sendResponse(["success" => true, "message" => "Assignment created", "data" => $newAssignment], 201);

    } catch (Exception $e) {
        sendResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
}

function updateAssignmentJSON(&$assignments, $assignmentsFile, $data) {
    try {
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

        if (file_put_contents($assignmentsFile, json_encode($assignments, JSON_PRETTY_PRINT)) === false) {
            throw new Exception("Failed to write assignments file");
        }

        sendResponse(["success" => true, "message" => "Assignment updated"]);

    } catch (Exception $e) {
        sendResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
}

function deleteAssignmentJSON(&$assignments, $assignmentsFile, &$comments, $commentsFile, $id) {
    try {
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

        if (file_put_contents($assignmentsFile, json_encode($assignments, JSON_PRETTY_PRINT)) === false ||
            file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT)) === false) {
            throw new Exception("Failed to update files");
        }

        sendResponse(["success" => true, "message" => "Assignment deleted"]);

    } catch (Exception $e) {
        sendResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
}

// ============================
// Comments Functions
// ============================

function getCommentsByAssignmentJSON($comments, $assignmentId) {
    try {
        $replies = $comments[$assignmentId] ?? [];
        sendResponse(["success" => true, "data" => $replies]);
    } catch (Exception $e) {
        sendResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
}

function createCommentJSON(&$comments, $commentsFile, $data) {
    try {
        if (empty($data['assignment_id']) || empty($data['author']) || empty($data['text'])) {
            sendResponse(["success" => false, "message" => "Missing required fields"], 400);
        }

        $reply = [
            "author" => sanitizeInput($data['author']),
            "text" => sanitizeInput($data['text'])
        ];

        if (!isset($comments[$data['assignment_id']])) $comments[$data['assignment_id']] = [];

        $comments[$data['assignment_id']][] = $reply;

        if (file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT)) === false) {
            throw new Exception("Failed to write comments file");
        }

        sendResponse(["success" => true, "message" => "Comment added", "data" => $reply], 201);

    } catch (Exception $e) {
        sendResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
}

function deleteCommentJSON(&$comments, $commentsFile, $assignmentId, $index) {
    try {
        if (!isset($comments[$assignmentId])) sendResponse(["success" => false, "message" => "Assignment not found"], 404);

        if (!isset($comments[$assignmentId][$index])) sendResponse(["success" => false, "message" => "Comment not found"], 404);

        array_splice($comments[$assignmentId], $index, 1);

        if (file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT)) === false) {
            throw new Exception("Failed to update comments file");
        }

        sendResponse(["success" => true, "message" => "Comment deleted"]);

    } catch (Exception $e) {
        sendResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
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


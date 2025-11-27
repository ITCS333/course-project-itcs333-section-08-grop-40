<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth.php';

requireAdmin();

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $stmt = $pdo->prepare("INSERT INTO users (student_id, name, email, password, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->execute([
            $_POST['student_id'],
            $_POST['name'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_DEFAULT)
        ]);
        $message = "Student added successfully!";
    }
    
    if ($action == 'update') {
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET student_id = ?, name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([
                $_POST['student_id'],
                $_POST['name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['id']
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET student_id = ?, name = ?, email = ? WHERE id = ?");
            $stmt->execute([
                $_POST['student_id'],
                $_POST['name'],
                $_POST['email'],
                $_POST['id']
            ]);
        }
        $message = "Student updated successfully!";
    }
    
    if ($action == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        $stmt->execute([$_POST['id']]);
        $message = "Student deleted successfully!";
    }
    
    if ($action == 'change_password') {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([
            password_hash($_POST['new_password'], PASSWORD_DEFAULT),
            $_SESSION['user_id']
        ]);
        $message = "Password changed successfully!";
    }
}

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ITCS333</title>
    <link rel="stylesheet" href="../../src/common/styles.css">
    <link rel="stylesheet" href="../../src/admin/admin.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="logo">ITCS333 Admin</h1>
                <div class="nav-links">
                    <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="../auth/logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1>Admin Dashboard</h1>
            
            <?php if (isset($message)): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="admin-section">
                <h2>Change Password</h2>
                <form method="POST" class="password-form">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <button type="submit" class="btn-primary">Update Password</button>
                </form>
            </div>

            <div class="admin-section">
                <h2>Add New Student</h2>
                <form method="POST" class="student-form">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Student ID</label>
                            <input type="text" name="student_id" required>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Password (default: student123)</label>
                            <input type="password" name="password" value="student123" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Add Student</button>
                </form>
            </div>

            <div class="admin-section">
                <h2>Manage Students</h2>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr id="row-<?php echo $student['id']; ?>">
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <button onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)" class="btn-edit">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                        <button type="button" class="btn-delete" onclick="confirmDelete(this.form, '<?php echo htmlspecialchars($student['name']); ?>')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Student</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit-id">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" id="edit-student-id" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="edit-name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-email" required>
                </div>
                <div class="form-group">
                    <label>New Password (leave empty to keep current)</label>
                    <input type="password" name="password" id="edit-password" placeholder="Leave empty to keep current password">
                </div>
                <button type="submit" class="btn-primary">Update Student</button>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content delete-modal">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete <strong id="deleteStudentName"></strong>?</p>
            <p class="warning-text">This action cannot be undone.</p>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn-delete">Delete Student</button>
            </div>
        </div>
    </div>

    <script>
        function editStudent(student) {
            document.getElementById('edit-id').value = student.id;
            document.getElementById('edit-student-id').value = student.student_id;
            document.getElementById('edit-name').value = student.name;
            document.getElementById('edit-email').value = student.email;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function confirmDelete(form, studentName) {
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('deleteStudentName').textContent = studentName;
            
            document.getElementById('confirmDeleteBtn').onclick = function() {
                form.submit();
            };
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var editModal = document.getElementById('editModal');
            var deleteModal = document.getElementById('deleteModal');
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

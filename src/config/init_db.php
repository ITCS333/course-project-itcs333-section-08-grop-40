<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'course_db';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'student') DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $studentPass = password_hash('student123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (student_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute(['ADMIN', 'Dr. Ahmed Ali', 'admin@uob.edu.bh', $adminPass, 'admin']);
    $stmt->execute(['202001234', 'Sara Mohammed', 'sara.m@stu.uob.bh', $studentPass, 'student']);
    $stmt->execute(['202001235', 'Khalid Hassan', 'khalid.h@stu.uob.bh', $studentPass, 'student']);
    $stmt->execute(['202001236', 'Fatima Yousif', 'fatima.y@stu.uob.bh', $studentPass, 'student']);
    $stmt->execute(['202001237', 'Ali Abdulla', 'ali.a@stu.uob.bh', $studentPass, 'student']);
    $stmt->execute(['202001238', 'Maryam Saleh', 'maryam.s@stu.uob.bh', $studentPass, 'student']);
    
    echo "Database and tables created successfully!<br>";
    echo "Admin account: admin@uob.edu.bh / admin123<br>";
    echo "Student accounts: Use any email above with password: student123<br>";
    echo "<a href='../auth/login.php'>Go to Login</a>";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

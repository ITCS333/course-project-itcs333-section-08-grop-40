<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITCS333 - Internet Software Development</title>
    <link rel="stylesheet" href="src/common/styles.css">
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="logo">ITCS333</h1>
                <div class="nav-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <a href="src/admin/dashboard.php" class="btn-login">Admin Dashboard</a>
                        <?php endif; ?>
                        <a href="src/auth/logout.php" class="btn-logout">Logout</a>
                    <?php else: ?>
                        <a href="src/auth/login.php" class="btn-login">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Internet Software Development</h1>
                <p class="course-code">ITCS333 - Section 08</p>
                <p class="description">
                    Learn to build modern web applications using HTML, CSS, JavaScript, PHP, and MySQL.
                    This course covers both front-end and back-end development skills.
                </p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="src/auth/login.php" class="btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2>Course Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <h3>Course Resources</h3>
                        <p>Access lecture notes, books, and study materials</p>
                    </div>
                    <div class="feature-card">
                        <h3>Weekly Content</h3>
                        <p>Follow structured weekly lessons and exercises</p>
                    </div>
                    <div class="feature-card">
                        <h3>Assignments</h3>
                        <p>Submit and track your course assignments</p>
                    </div>
                    <div class="feature-card">
                        <h3>Discussion Board</h3>
                        <p>Engage with classmates and instructors</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 ITCS333 - University of Bahrain</p>
        </div>
    </footer>
</body>
</html>

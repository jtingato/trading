<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monarch Trading Journal</title>
    <link rel="stylesheet" href="/assets/style.css"> <!-- Use absolute path -->
</head>
<body>
    <header>
        <img src="/assets/monarch_logo.png" alt="Monarch Traders Logo" class="logo">
        <nav>
            <ul>
                <li><a href="index.php?page=home">Home</a></li>
                <li><a href="index.php?page=journal">Journal</a></li>
                <li><a href="index.php?page=settings">Settings</a></li>
                <li><a href="index.php?page=register">Register</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="#" id="loginLink">Login</a></li>
                <?php else: ?>
                    <li><a href="index.php?page=logout">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        $page = $_GET['page'] ?? 'home';
        $protected_pages = ['journal', 'settings', 'profile'];
        if (in_array($page, $protected_pages) && !isset($_SESSION['user_id'])) {
            include 'pages/login.php';
        } else {
            $file = "pages/$page.php";
            if (file_exists($file)) {
                include $file;
            } else {
                echo "<h2>404 Page not found</h2>";
            }
        }
        ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Monarch Trading Journal</p>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form method="post" action="pages/login.php">
                <h2>Login</h2>
                <label>Username: <input type="text" name="username"></label><br>
                <label>Password: <input type="password" name="password"></label><br>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('loginLink').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('loginModal').style.display = 'block';
    });
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('loginModal').style.display = 'none';
    });
    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('loginModal')) {
            document.getElementById('loginModal').style.display = 'none';
        }
    });
    </script>
</body>
</html>

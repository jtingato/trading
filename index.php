<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monarch Trading Journal</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/journal.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <header>
        <img class=logo src="assets/monarch_logo.png" alt="Monarch Traders Logo" class="logo">
        <nav>
            <ul>
                <li><a href="index.php?page=home">Home</a></li>
                <li><a href="index.php?page=journal">Journal</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        $page = $_GET['page'] ?? 'home';

        $file = "pages/$page.php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<h2>404 Page not found</h2>";
        }
        ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Monarch Trading Journal</p>
    </footer>
</body>
</html>
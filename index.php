<?php 

require_once __DIR__ . "/navigation_router.php";
$navigationRouter = new NavigationRouter();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monarch Trading Journal</title>
    <link rel="stylesheet" href="css/style.css?v=1">
    <link rel="stylesheet" href="pages/journal/journal.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <header>
        <a href="index.php?page=home">
        <img class=logo src="assets/monarch_logo.png" alt="Monarch Traders Logo" class="logo">
        </a>
        <nav>
            <ul>
                <li><a href="index.php?page=journal">Journal</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        $page = $_GET['page'] . ".php" ?? 'home.php';
        include $navigationRouter->pathForFileNamed($page);
        ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Monarch Trading Journal</p>
    </footer>
</body>
</html>
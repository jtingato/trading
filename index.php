<?php
    declare(strict_types=1);

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    // ✔ Composer autoload (loads Monarch\Routing\NavigationRouter, ViewModels, Models, etc.)
    require __DIR__ . '/vendor/autoload.php';

    use Monarch\Routing\NavigationRouter;

    // ✔ Router now loads pages (views), not PHP classes anymore
    $navigationRouter = new NavigationRouter();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Monarch Trading Journal</title>

        <link rel="stylesheet" href="css/style.css?v=1">
        <link rel="stylesheet" href="pages/journal/journal.css?v=1">
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </head>

    <body>

    <header>
        <a href="index.php?page=home">
            <img class="logo" src="assets/monarch_logo.png" alt="Monarch Traders Logo">
        </a>
        <nav>
            <ul>
                <li><a href="index.php?page=journal">Journal</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        // ✔ Render pages through the router
        $page = $_GET['page'] ?? 'home';
        $pageFile = $page . '.php';
        // var_dump($pageFile);
        // var_dump($navigationRouter->pathForFileNamed($pageFile));

        include $navigationRouter->pathForFileNamed($pageFile);
        ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Monarch Trading Journal</p>
    </footer>

    </body>
</html>

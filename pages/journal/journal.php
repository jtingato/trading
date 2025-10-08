<?php 
    declare(strict_types=1);
    require_once $navigationRouter->pathForFileNamed('journalViewModel.php');

    // $viewModel = new NavigationRouter()->pathForFileNamed("journalViewModel.php")
    $viewModel = new JournalViewModel();
    $action = $_GET['action'] ?? '';
?>
    
<h2>Trading Journal</h2>

<div class="journal-menu-wrapper">
        <div class="journal-menu">
            <ul>
                <li><a href="?page=journal&action=add_new"><i class="fas fa-pen"></i> Add New Entry</a></li>
                <li><a href="?page=journal&action=import-csv"><i class="fas fa-database"></i> Import CSV</a></li>
                <li><a href="?page=journal&action=select_fields"><i class="fas fa-sliders-h"></i> Select Fields</a></li>
            </ul>
        </div>
    </div>

<div class="journal-layout">
    <?php $viewModel->updateJournalEntries(); ?>

    <!-- Right-hand journal content -->
    <div class="journal-container">
        <?php 
            if (!empty($viewModel->errorMsg)):
                echo "<div class='error-box'> $viewModel->errorMsg; ?></div>";
            endif;

            if ($action === 'select_fields'):
                include $navigationRouter->pathForFileNamed("journalSelectFields.php");
            elseif($action === 'save_fields'):
                // Redirect to journal view
                header("Location: ?page=journal");
                exit;
            else:
                include $navigationRouter->pathForFileNamed("journalTable.php");
            endif; 
        ?>
    </div>
</div>

<?php 
    declare(strict_types=1);
    require_once $navigationRouter->pathForFileNamed('journalViewModel.php');
    require_once $navigationRouter->pathForFileNamed('JournalField.php');

    $viewModel = new JournalViewModel();
    $action = $_GET['action'] ?? '';

    include $navigationRouter->pathForFileNamed("journalSelectFields.php");
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
    <?php $viewModel->getJournalEntries(); ?>

    <!-- Right-hand journal content -->
    <div class="journal-container">
        <?php 
            if (!empty($viewModel->errorMsg)):
                echo "<div class='error-box'> $viewModel->errorMsg; ?></div>";
            endif;

            if($action === 'save_fields'):
                // Update the JournalFields property in the viewModel
                $viewModel->updateJournalFieldNamesAndVisibility();

                $viewModel = new JournalViewModel();
                
                // Re-render the journal table immediately
                echo '<div id="journalTableWrapper">';
                include $navigationRouter->pathForFileNamed("journalTable.php");
                echo '</div>';
            else: 
                // Always show the journal table
                echo '<div id="journalTableWrapper">';
                include $navigationRouter->pathForFileNamed("journalTable.php");
                echo '</div>';
                // Always include the modal markup (hidden by default)
                include $navigationRouter->pathForFileNamed("journalSelectFields.php");
            endif; 
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('selectFieldsModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const tableWrapper = document.getElementById('journalTableWrapper');

    document.querySelector('a[href*="action=select_fields"]').addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'block';
        tableWrapper.classList.add('blurred'); // apply blur
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        tableWrapper.classList.remove('blurred'); // remove blur
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            tableWrapper.classList.remove('blurred');
        }
    });
});
</script>

<?php
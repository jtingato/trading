<?php
declare(strict_types=1);

use Monarch\ViewModels\JournalViewModel;

// No requires needed if using Composer autoload in index.php
$viewModel = new JournalViewModel();

$action = $_GET['action'] ?? '';
?>

<link rel="stylesheet" href="/pages/journal/journal.css">

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

    <div class="journal-container">

        <?php if (!empty($viewModel->errorMsg)): ?>
            <div class="error-box">
                <?= htmlspecialchars($viewModel->errorMsg) ?>
            </div>
        <?php endif; ?>

        <?php
        if ($action === 'save_fields'):

            // Save updated field visibility and display names
            $viewModel->updateJournalFieldNamesAndVisibility();

            // Reload view model to refresh state
            $viewModel = new JournalViewModel();
        endif;
        ?>

        <!-- Render the journal table -->
        <div id="journalTableWrapper">
            <?php include __DIR__ . '/journalTable.php'; ?>
        </div>

        <!-- Modal markup (hidden by default) -->
        <?php include __DIR__ . '/journalSelectFields.php'; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal        = document.getElementById('selectFieldsModal');
    const closeBtn     = document.getElementById('closeModalBtn');
    const tableWrapper = document.getElementById('journalTableWrapper');

    const selectFieldsLink = document.querySelector('a[href*="action=select_fields"]');

    if (selectFieldsLink) {
        selectFieldsLink.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'block';
            tableWrapper.classList.add('blurred');
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            tableWrapper.classList.remove('blurred');
        });
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            tableWrapper.classList.remove('blurred');
        }
    });
});
</script>

<?php

// Read JSON from JS
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["columnOrder"])) {
    echo "No order received";
    exit;
}

$order = $data["columnOrder"];

// Call your function
updateSorting($order);

echo "Order updated successfully";


// ---- Your update function ----
function updateSorting($order) {
    echo "Inside updateSorting\n";
    print_r($order);

    try {
        require_once 'NavigationRouter.php';
        require_once 'data/JournalDataManager.php';

        JournalDataManager::shared()->updateColumnOrdering($order);
        
    } catch (Throwable $e) {
            echo "Failed to include JournalDataManager: " . $e->getMessage() . "\n";
        return;
    }
}
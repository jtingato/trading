<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Monarch\Data\JournalDataManager;

// Read JSON from client
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['columnOrder']) || !is_array($data['columnOrder'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid column order']);
    exit;
}

$orderedFieldNames = $data['columnOrder'];

try {
    JournalDataManager::shared()->updateColumnOrdering($orderedFieldNames);

    echo json_encode(['status' => 'success']);
} catch (Throwable $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
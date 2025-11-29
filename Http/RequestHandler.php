<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Monarch\Data\JournalDataManager;

// Read JSON from client
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['updateWidth'])) {
    $field = $data['updateWidth']['field'];
    $width = (int)$data['updateWidth']['width'];
    JournalDataManager::shared()->updateColumnWidth($field, $width);
    echo json_encode(['status' => 'ok']);
    exit;
}

if (isset($data['columnOrder']) && is_array($data['columnOrder'])) {
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
}
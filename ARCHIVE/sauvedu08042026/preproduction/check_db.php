<?php
require_once 'config/db.php';
try {
    $stmt = $pdo->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

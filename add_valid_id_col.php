<?php
require 'config/database.php';
try {
    $pdo->exec("ALTER TABLE residents ADD COLUMN valid_id_path VARCHAR(255) NULL");
    echo 'Success';
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo 'Already exists';
    } else {
        echo $e->getMessage();
    }
}

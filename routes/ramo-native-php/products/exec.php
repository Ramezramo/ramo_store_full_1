<?php
include 'connectfile.php';
$sql = "";

try {
    $stmt = $con->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "executed successfully: ";
    print_r($results);
    return $results;
} catch(PDOException $e) {
    echo "Error executing : " . $e->getMessage();
}
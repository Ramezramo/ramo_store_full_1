<?php
function updateUsageTimes($linkName)
{
    global $con;
    
    // Check if the link name exists
    $sql = "SELECT usage_times FROM link_access_logs WHERE link_name = :link_name";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':link_name', $linkName);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // If the link name exists, update the usage times
        $sql = "UPDATE link_access_logs SET usage_times = usage_times + 1 WHERE link_name = :link_name";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':link_name', $linkName);
        $stmt->execute();
    } else {
        // If the link name does not exist, insert a new record
        $sql = "INSERT INTO link_access_logs (link_name, usage_times) VALUES (:link_name, 1)";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':link_name', $linkName);
        $stmt->execute();
    }
}
?>

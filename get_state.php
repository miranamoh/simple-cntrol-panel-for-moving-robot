<?php
header('Content-Type: application/json');
include "db.php";

$result = $conn->query("SELECT command, updated_at FROM robot_state WHERE id = 1");
$row = $result->fetch_assoc();

echo json_encode($row);

$conn->close();
?>

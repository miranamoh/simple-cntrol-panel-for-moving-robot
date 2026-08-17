<?php
header('Content-Type: application/json');
include "db.php";

$text = isset($_POST['text']) ? trim($_POST['text']) : '';

if ($text === '') {
    echo json_encode(["status" => "error", "message" => "النص فارغ"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO voice_texts (text_content) VALUES (?)");
$stmt->bind_param("s", $text);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "id" => $stmt->insert_id,
        "text" => $text
    ]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>

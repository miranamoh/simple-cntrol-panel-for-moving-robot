<?php
header('Content-Type: application/json');
include "db.php";

// خريطة تحويل اسم الزرار إلى الحرف المطلوب تخزينه
$map = [
    "forward"  => "f",
    "backward" => "b",
    "left"     => "l",
    "right"    => "r",
    "stop"     => "S"
];

$button = isset($_POST['command']) ? $_POST['command'] : '';

if (!array_key_exists($button, $map)) {
    echo json_encode(["status" => "error", "message" => "أمر غير معروف"]);
    exit;
}

$letter = $map[$button];

// تحديث الصف الوحيد (id = 1) بدل إضافة صف جديد
$stmt = $conn->prepare("UPDATE robot_state SET command = ? WHERE id = 1");
$stmt->bind_param("s", $letter);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "button" => $button, "stored_as" => $letter]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>

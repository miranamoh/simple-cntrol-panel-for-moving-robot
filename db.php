<?php
// بيانات الاتصال بقاعدة البيانات - غيّرها ببياناتك من InfinityFree
$host = "sqlXXX.infinityfree.com";      // اسم السيرفر (Hostname)
$user = "epiz_XXXXXXXX";                // اسم المستخدم
$pass = "your_password_here";           // كلمة المرور
$dbname = "epiz_XXXXXXXX_control_db";   // اسم قاعدة البيانات

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "فشل الاتصال: " . $conn->connect_error]));
}
?>

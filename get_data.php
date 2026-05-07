<?php
include "db.php";

// Получаем последние данные
$result = mysqli_query($db, "SELECT * FROM Samog2 ORDER BY datetime DESC LIMIT 1");
$data = mysqli_fetch_assoc($result);

// Корректируем время
$data['datetime'] = date('Y-m-d H:i:s', strtotime($data['datetime']) + 5 * 3600);

// Возвращаем данные в формате JSON
header('Content-Type: application/json');
echo json_encode($data);

mysqli_close($db);
?>

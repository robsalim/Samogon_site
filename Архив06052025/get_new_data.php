<?php
include "db.php";

header('Content-Type: application/json');

// Получаем последний известный клиенту timestamp
$lastTime = $_GET['last'] ?? null;

// Запрос новых данных
$query = "SELECT datetime, t1, t2, pd FROM Samog1 ";
if ($lastTime) {
    $query .= "WHERE datetime > '".mysqli_real_escape_string($db, $lastTime)."' ";
}
$query .= "ORDER BY datetime ASC LIMIT 100";

$result = mysqli_query($db, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
mysqli_close($db);
?>
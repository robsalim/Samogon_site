<?php
// Подключение к базе данных
include "db.php";

// Проверяем соединение
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Запрос данных за последние 24 часа для графиков
$last24hours = date('Y-m-d H:i:s', strtotime('-4800 hours'));
$result_graph = mysqli_query($db, "SELECT datetime, t1, t2, pd FROM Samog1 
                                 WHERE datetime >= '$last24hours' 
                                 ORDER BY datetime ASC");

if (!$result_graph) {
    die("Query failed: " . mysqli_error($db));
}

$graph_data = array();
while ($row = mysqli_fetch_assoc($result_graph)) {
    // Корректируем время (+5 часов)
    $corrected_time = date('Y-m-d H:i:s', strtotime($row['datetime']) + 0 * 3600);
    $row['datetime'] = $corrected_time;
    
    if (is_numeric($row['t1']) && is_numeric($row['t2']) && is_numeric($row['pd'])) {
        $graph_data[] = $row;
    }
}

// Запрос последней записи для таблицы
$result_last = mysqli_query($db, "SELECT * FROM Samog2 ORDER BY datetime DESC LIMIT 1");
$last_data = mysqli_fetch_assoc($result_last);
// Корректируем время для последней записи
$last_data['datetime'] = date('Y-m-d H:i:s', strtotime($last_data['datetime']) + 5 * 3600);

// Закрываем соединение
mysqli_close($db);

// Подключаем HTML шаблон
include 'template.html';
?>
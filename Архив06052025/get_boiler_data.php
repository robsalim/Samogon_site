<?php
//котел

$db = new mysqli('localhost', 'phpmyadmin', 'Nwm92n9v_', 'phpmyadmin');


header('Content-Type: application/json');
header('Cache-Control: no-cache');
header('Connection: keep-alive');


// Если нет подключения - демо-данные
if ($db->connect_error) {
    echo json_encode([
        'temp1' => round(30 + rand(0, 50)),
        'temp2' => round(40 + rand(0, 40)),
        'pump1' => round(40 + rand(0, 40)),
        'pump2' => round(40 + rand(0, 40)),
        'fan' => rand(0, 1),
        'valve' => rand(0, 1)
    ]);
    exit;
}

// Получаем последние данные
//$result = $db->query("SELECT * FROM `Samog2` LIMIT 1");
$result = $db->query("SELECT * FROM `Samog2` ORDER BY `increm` DESC LIMIT 1");

$data = $result->fetch_assoc();

echo json_encode([
    'temp1' => $data['t1'] ?? 0,
    'temp2' => $data['t2'] ?? 0,
    'pump1' => $data['p1'] ?? 0,
    'pump2' => $data['p2'] ?? 0,
    'fan' => (bool)($data['imode'] ?? false),
    'valve' => (bool)($data['imenu'] ?? false),
    'dateT' => $data['DateTime'] ?? 0,
]);

$db->close();
?>
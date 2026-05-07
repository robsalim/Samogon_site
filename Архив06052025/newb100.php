<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo 'new.php';

include "db.php";

// Установка временной зоны и обработка даты
date_default_timezone_set('Asia/Yekaterinburg');
$date = isset($_REQUEST['date']) ? strtotime($_REQUEST['date']) : time();
$date = date("Y-m-d H:i:s", $date);

// Получение и проверка параметров
$pr = intval($_REQUEST['pr'] ?? 0);
$t1 = floatval($_REQUEST['t1'] ?? 0);
$t2 = floatval($_REQUEST['t2'] ?? 0);
$t3 = floatval($_REQUEST['t3'] ?? 0);
$tr = floatval($_REQUEST['t4'] ?? 0);
$hc = floatval($_REQUEST['hc'] ?? 0);
$pd = intval($_REQUEST['pw1'] ?? 0);
$p1 = intval($_REQUEST['pw2'] ?? 0);
$p2 = intval($_REQUEST['pw3'] ?? 0);
$cm = intval($_REQUEST['cm'] ?? 0);
$imode = intval($_REQUEST['imode'] ?? 0);
$imenu = intval($_REQUEST['imenu'] ?? 0);
$phase = intval($_REQUEST['phase'] ?? 0);
$spnt = intval($_REQUEST['spnt'] ?? 0);
$k_int = floatval($_REQUEST['k_int'] ?? 0);
$k_der = floatval($_REQUEST['k_der'] ?? 0);
$kp = floatval($_REQUEST['kp'] ?? 0);
$ki = floatval($_REQUEST['ki'] ?? 0);
$kd = floatval($_REQUEST['kd'] ?? 0);

if ($pr == 100) {
    try {
        // Запись для графика (используем числовые плейсхолдеры)
        $stmt1 = $db->prepare("INSERT INTO Samog1 SET 
            t1 = ?,
            t2 = ?,
            pr = ?,
            pd = ?,
            DateTime = ?");
        
        $stmt1->execute([$t1, $t2, $pr, $pd, $date]);

        // Обновление всех данных
        $stmt2 = $db->prepare("UPDATE Samog2 SET 
            t1 = ?,
            t2 = ?,
            t3 = ?,
            pr = ?,
            tr = ?,
            hc = ?,
            pd = ?,
            p1 = ?,
            p2 = ?,
            cm = ?,
            imode = ?,
            imenu = ?,
            phase = ?,
            spoint = ?,
            k_integ = ?,
            k_deriv = ?,
            kp = ?,
            ki = ?,
            kd = ?,
            DateTime = ?");
        
        $stmt2->execute([
            $t1, $t2, $t3, $pr, $tr, $hc, $pd, $p1, $p2, 
            $cm, $imode, $imenu, $phase, $spnt, 
            $k_int, $k_der, $kp, $ki, $kd, $date
        ]);

        echo '# end SQL#';
        
    } catch (PDOException $e) {
        error_log("Ошибка БД: " . $e->getMessage());
        die("Ошибка выполнения запроса: " . $e->getMessage());
    }
}

// Закрытие соединения
$db = null;
?>
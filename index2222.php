<?php
// Подключение к базе данных
include "db.php";

// Проверяем соединение
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Запрос данных за последние 24 часа для графиков
$last24hours = date('Y-m-d H:i:s', strtotime('-4800 hours'));
$result_graph = mysqli_query($db, "SELECT datetime, t1, t2, p2 FROM Samog1 
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
    
    if (is_numeric($row['t1']) && is_numeric($row['t2']) && is_numeric($row['p2'])) {
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

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Samog1 Data Viewer</title>
    <script src="jquery-3.2.1.min.js"></script>
    <script src="code/highstock.js"></script>
    <script src="code/themes/grid-light.js"></script>
    <script src="code/modules/exporting.js"></script>
    
    <style>
        :root {
            --main-font-size: 16px;
            --header-font-size: 24px;
            --highlight-font-size: 32px;
            --highlight-label-size: 18px;
            --chart-height: 800px;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            margin: 10px;
            font-size: var(--main-font-size);
            line-height: 1.5;
        }
        
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-bottom: 20px; 
            font-size: var(--main-font-size);
        }
        
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        
        th { 
            background-color: #f2f2f2; 
        }
        
        .chart-container { 
            height: var(--chart-height); 
            width: 100%; 
            min-width: 100%; 
            margin-bottom: 20px; 
        }
        
        .info { 
            margin: 15px 0; 
            padding: 12px; 
            background: #f8f9fa; 
            border-radius: 5px; 
        }
        
        #debug { 
            color: red; 
            margin: 15px 0; 
            font-size: var(--main-font-size);
        }
        
        .data-table { 
            width: 100%; 
            margin: 15px 0; 
        }
        
        .data-table td:first-child { 
            font-weight: bold; 
            width: 120px; 
        }
        
        .highlight { 
            font-size: var(--highlight-font-size);
            font-weight: bold;
            color: #2c3e50;
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-radius: 5px;
            text-align: center;
        }
        
        .highlight-label {
            font-size: var(--highlight-label-size);
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .highlight-container {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        
        .highlight-item {
            flex: 1 1 120px;
            min-width: 120px;
            text-align: center;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header-wrapper {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        
        .main-header {
            margin: 0 0 10px 0;
            text-align: center;
            font-size: var(--header-font-size);
            width: 100%;
        }
        
        .last-update {
            margin: 0;
            text-align: center;
            font-size: var(--main-font-size);
            color: red;
        }
        
        /* Адаптация для мобильных */
        @media (max-width: 768px) {
            :root {
                --main-font-size: 18px;
                --header-font-size: 28px;
                --highlight-font-size: 40px;
                --highlight-label-size: 20px;
                --chart-height: 500px;
            }
            
            body {
                margin: 8px;
            }
            
            th, td {
                padding: 8px;
            }
            
            .highlight-container {
                gap: 10px;
            }
            
            .highlight-item {
                flex: 1 1 100%;
                margin-bottom: 10px;
            }
            
            .chart-container {
                height: 600px;
            }
        }
        
        @media (max-width: 480px) {
            :root {
                --main-font-size: 16px;
                --header-font-size: 22px;
                --highlight-font-size: 36px;
                --highlight-label-size: 18px;
            }
            
            .chart-container {
                height: 600px;
            }
        }
        .table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Плавная прокрутка на iOS */
        margin: 15px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .data-table {
        width: auto;
        min-width: 100%;
        margin: 0;
        white-space: nowrap;
    }
    
    .data-table th {
        position: sticky;
        top: 0;
        background-color: #f2f2f2;
        z-index: 10;
    }
    
    @media (min-width: 768px) {
        .table-wrapper {
            overflow-x: visible;
        }
        
        .data-table {
            width: 100%;
            white-space: normal;
        }
    }

    </style>
</head>
<body>
    <div class="header-wrapper">
        <h1 class="main-header">Данные котла ПВК</h1>
        <div class="last-update">
            Последние показания (<?php echo htmlspecialchars($last_data['DateTime']); ?>)

        </div>
        <h2><a href="http://46.48.86.153/dom17/index.html" class="data-link">Перейти к схеме</a></h2>
    </div>
    
    <!-- Крупные выделенные значения -->
    <div class="highlight-container">
        <div class="highlight-item">
            <div class="highlight-label">Температура колонны</div>
            <div class="highlight"><?php echo htmlspecialchars($last_data['t1']); ?></div>
        </div>
        <div class="highlight-item">
            <div class="highlight-label">Температура бака</div>
            <div class="highlight"><?php echo htmlspecialchars($last_data['t2']); ?></div>
        </div>
        <div class="highlight-item">
            <div class="highlight-label">Мощность %</div>
            <div class="highlight"><?php echo htmlspecialchars($last_data['p2']); ?></div>
        </div>
    </div>
    
    <div id="combined-chart" class="chart-container"></div>
    
    <h2>Все параметры</h2>
   <div class="table-wrapper">
    <table class="data-table">
        <tr>
            <td>t1</td>
            <td><?php echo htmlspecialchars($last_data['t1']); ?></td>
        </tr>
        <tr>
            <td>t2</td>
            <td><?php echo htmlspecialchars($last_data['t2']); ?></td>
        </tr>
        <tr>
            <td>t3</td>
            <td><?php echo htmlspecialchars($last_data['t3']); ?></td>
        </tr>
        <tr>
            <td>pr</td>
            <td><?php echo htmlspecialchars($last_data['pr']); ?></td>
        </tr>
        <tr>
            <td>tr</td>
            <td><?php echo htmlspecialchars($last_data['tr']); ?></td>
        </tr>
        <tr>
            <td>hc</td>
            <td><?php echo htmlspecialchars($last_data['hc']); ?></td>
        </tr>
        <tr>
            <td>pd</td>
            <td><?php echo htmlspecialchars($last_data['pd']); ?></td>
        </tr>
        <tr>
            <td>p1</td>
            <td><?php echo htmlspecialchars($last_data['p1']); ?></td>
        </tr>
        <tr>
            <td>p2</td>
            <td><?php echo htmlspecialchars($last_data['p2']); ?></td>
        </tr>
        <tr>
            <td>cm</td>
            <td><?php echo htmlspecialchars($last_data['cm']); ?></td>
        </tr>
        <tr>
            <td>imode</td>
            <td><?php echo htmlspecialchars($last_data['imode']); ?></td>
        </tr>
        <tr>
            <td>imenu</td>
            <td><?php echo htmlspecialchars($last_data['imenu']); ?></td>
        </tr>
        <tr>
            <td>phase</td>
            <td><?php echo htmlspecialchars($last_data['phase']); ?></td>
        </tr>
        <tr>
            <td>spoint</td>
            <td><?php echo htmlspecialchars($last_data['spoint']); ?></td>
        </tr>
        <tr>
            <td>k_integ</td>
            <td><?php echo htmlspecialchars($last_data['k_integ']); ?></td>
        </tr>
        <tr>
            <td>k_deriv</td>
            <td><?php echo htmlspecialchars($last_data['k_deriv']); ?></td>
        </tr>
        <tr>
            <td>kp</td>
            <td><?php echo htmlspecialchars($last_data['kp']); ?></td>
        </tr>
        <tr>
            <td>ki</td>
            <td><?php echo htmlspecialchars($last_data['ki']); ?></td>
        </tr>
        <tr>
            <td>kd</td>
            <td><?php echo htmlspecialchars($last_data['kd']); ?></td>
        </tr>
    </table>
    </div>
    
<script>
$(document).ready(function() {
    try {
        // 1. Проверка загрузки Highcharts
        if (!window.Highcharts || !Highcharts.stockChart) {
            throw new Error('Библиотека Highcharts не загрузилась');
        }

        // 2. Получение данных
        const graphData = <?php echo json_encode($graph_data); ?>;
        
        // 3. Проверка данных
        if (!Array.isArray(graphData)) {
            throw new Error('Данные должны быть массивом');
        }
        
        console.log('Первые 3 точки:', graphData.slice(0, 3));
        console.log('Всего точек:', graphData.length);

        // 4. Функция подготовки чистой серии
        function prepareCleanSeries(fieldName) {
            return graphData
                .filter(item => !isNaN(item[fieldName]))
                .map(item => {
                const date = new Date(item.datetime);
                const timestamp = date.getTime();
            
            // Если нужно добавить еще 3 часа (например, для коррекции часового пояса)
            
            // timestamp += 3 * 3600 * 1000;
            
            return [timestamp, parseFloat(item[fieldName])];
            
            })
            
            .sort((a, b) => a[0] - b[0]);
        
        }

        Highcharts.setOptions({
            time: {
                useUTC: false
            }
        });
        // 5. Настройка графика
        Highcharts.stockChart('combined-chart', {
                       chart: {
                type: "line",
                zoomType: "x",
            },
            rangeSelector: {
                buttons: [
                    {
                        type: "minute",
                        count: 15,
                        text: "15m",
                    },
                    {
                        type: "hour",
                        count: 1,
                        text: "1h",
                    },
                    {
                        type: "hour",
                        count: 6,
                        text: "6h",
                    },
                                        {
                        type: "all",
                        text: "all",
                    },
                ],
                inputEnabled: false, // Скрываем поля ввода дат
                selected: 2, // Выбираем "1ч" по умолчанию
            },
            navigator: {
                enabled: true,
                series: {
                    dataGrouping: {
                        units: [
                            ["second", [1]], // По секундам
                            ["minute", [1, 5, 15]], // По минутам
                            ["hour", [1, 6]], // По часам
                        ],
                    },
                },
            },
            scrollbar: {
                enabled: true,
            },
            title: {
                text: 'Параметры котла ПВК',
                style: { fontSize: '18px' }
            },
            //time: { useUTC: true },
            xAxis: {
                type: 'datetime',
                title: { text: 'Время' }
            },
            yAxis: [
                {   // Верхняя ось для температур
                    title: { text: 'Температура (°C)', align: 'middle' },
                    height: '70%', // Ось занимает 70% высоты
                    offset: 0
                },
                {   // Нижняя ось для мощности
                    title: { text: 'Мощность (%)', align: 'middle' },
                    top: '70%',
                    height: '30%',
                    opposite: true,
                    min: 0,
                    max: 1000
                }
            ],
            series: [
                {   // Температура колонны t1
                    name: 'Температура колонны (t1)',
                    data: prepareCleanSeries('t1'),
                    yAxis: 0,
                    color: '#FF0000',
                    lineWidth: 2
                },
                {   // Температура бака t2
                    name: 'Температура бака (t2)',
                    data: prepareCleanSeries('t2'),
                    yAxis: 0,
                    color: '#00AA00',
                    lineWidth: 2
                },
                {   // Мощность pd
                    name: 'Мощность (p2)',
                    data: prepareCleanSeries('p2'),
                    yAxis: 1,
                    color: '#0000FF',
                    lineWidth: 2
                }
            ],
            plotOptions: {
                series: {
                    marker: { enabled: false },
                    states: {
                        hover: {
                            halo: { size: 0 }
                        }
                    }
                }
            }
        });

    } catch (e) {
        console.error('Ошибка:', e);
        $('#debug').html('<div class="error">' + e.message + '</div>');
    }
});
</script>

    <div id="debug"></div>
</body>
</html>
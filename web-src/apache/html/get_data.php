<?php

// Set the content type header to indicate that this script returns JSON.
header('Content-Type: application/json');

// --- CONFIGURATION ---
$influx_url   = getenv('INFLUXDB_URL');
$influx_token = getenv('INFLUXDB_TOKEN');
$influx_org   = getenv('INFLUXDB_ORG');
$influx_bucket= getenv('INFLUXDB_BUCKET');


function getChartData($url, $token, $org, $bucket) {
    // Initialize the new data structure to hold multiple data series.
    $chartData = [
        'labels' => [],
        'data' => [
            'temperature' => [],
            'pressure' => [],
            'humidity' => []
        ],
        'error' => null
    ];

    $window = $_GET['window'];
    $aggregate = '1m';
    
    switch($window) {
        case '24h':
            $aggregate = '15m';
            break;
        case '12h':
            $aggregate = '10m';
            break;
        case '6h':
            $aggregate = '5m';
            break;
        case '1h':
            $aggregate = '1m';
            break;
            
    }
    
    if (empty($url) || empty($token) || empty($org) || empty($bucket)) {
        $chartData['error'] = "Configuration error: InfluxDB environment variables are not set.";
        return $chartData;
    }

    $flux_query = "
        from(bucket: \"{$bucket}\")
          |> range(start: -{$window})
          |> filter(fn: (r) => r[\"_measurement\"] == \"measurements\")
          |> filter(fn: (r) => r[\"_field\"] == \"temperature\" or r[\"_field\"] == \"pressure\" or r[\"_field\"] == \"humidity\")
          |> aggregateWindow(every: {$aggregate}, fn: mean, createEmpty: false)
          |> pivot(rowKey:[\"_time\"], columnKey: [\"_field\"], valueColumn: \"_value\")
          |> yield(name: \"mean\")
    ";

    $ch = curl_init("{$url}/api/v2/query?org={$org}");

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => $flux_query,
        CURLOPT_ENCODING       => "",
        CURLOPT_HTTPHEADER     => [
            "Authorization: Token {$token}",
            "Content-Type: application/vnd.flux",
            "Accept: application/csv"
        ],
        CURLOPT_TIMEOUT        => 20,
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $chartData['error'] = 'cURL Error: ' . curl_error($ch);
    } elseif ($http_code !== 200) {
        $chartData['error'] = "InfluxDB API Error (HTTP {$http_code}): " . substr($response, 0, 500);
    } else {
        // --- UPDATED DATA PARSING ---
        $lines = explode("\n", trim($response));
        $is_data_line = false;
        
        // Column indices
        $indices = [];

        foreach ($lines as $line) {
            if (empty(trim($line)) || $line[0] === '#') continue;

            if (!$is_data_line) {
                $headers = str_getcsv($line);
                // Find the index of each required column from the header row.
                $indices = [
                    'time' => array_search('_time', $headers),
                    'temp' => array_search('temperature', $headers),
                    'pres' => array_search('pressure', $headers),
                    'hum'  => array_search('humidity', $headers)
                ];
                $is_data_line = true;
                continue;
            }

            $row = str_getcsv($line);
            
            // Process the timestamp (it's common for all data points in the row)
            if (isset($row[$indices['time']])) {
                $timestamp = new DateTime($row[$indices['time']]);
                $timestamp->setTimezone(new DateTimeZone('America/New_York'));
                $chartData['labels'][] = $timestamp->format('g:i A');

                // For each measurement, check if data exists and push it.
                // If data is missing for a timestamp, push `null` to create a gap in the chart.
                $chartData['data']['temperature'][] = isset($row[$indices['temp']]) && is_numeric($row[$indices['temp']]) ? (float)$row[$indices['temp']] : null;
                $chartData['data']['pressure'][] = isset($row[$indices['pres']]) && is_numeric($row[$indices['pres']]) ? (float)$row[$indices['pres']] / 1000 : null;
                $chartData['data']['humidity'][] = isset($row[$indices['hum']]) && is_numeric($row[$indices['hum']]) ? (float)$row[$indices['hum']] : null;
            }
        }
        
        if (empty($chartData['labels'])) {
             $chartData['error'] = 'No data found in the last 24 hours for the specified measurements.';
        }
    }

    curl_close($ch);
    return $chartData;
}

// Fetch the data and output it as a JSON string.
echo json_encode(getChartData($influx_url, $influx_token, $influx_org, $influx_bucket));

?>

<?php
// src/connect-test.php

// These variables will be set as Fly Secrets
$influx_url  = getenv('INFLUXDB_URL');
$influx_token = getenv('INFLUXDB_TOKEN');

if (!$influx_url || !$influx_token) {
    die("<h1>Configuration Error</h1><p>INFLUXDB_URL or INFLUXDB_TOKEN environment variables not set.</p>");
}

// The health check endpoint for InfluxDB
$health_check_url = $influx_url . "/health";

// Use cURL to make the request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $health_check_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Token {$influx_token}"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>InfluxDB Connection Test</h1>";
echo "<p>Pinging InfluxDB at: <strong>{$health_check_url}</strong></p>";

if ($http_code == 200) {
    echo "<p style='color:green; font-weight:bold;'>Success! Connection established. InfluxDB is healthy.</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
} else {
    echo "<p style='color:red; font-weight:bold;'>Failure! Could not connect to InfluxDB.</p>";
    echo "<p>HTTP Status Code: {$http_code}</p>";
    echo "<p>Response:</p><pre>" . htmlspecialchars($response) . "</pre>";
}
?>
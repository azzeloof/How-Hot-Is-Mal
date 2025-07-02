How Hot Is Mal

This project is a complete environmental monitoring solution that captures sensor data using an ESP32 microcontroller and visualizes it on a live web dashboard.
Architectural Overview

The project is split into two main components: the firmware that runs on the physical sensor device and the web application that stores and displays the data.
1. Firmware (/firmware-src)

    Device: An ESP32 development board connected to a BME280 sensor.

    Function: The firmware, built with PlatformIO, reads temperature, pressure, and humidity data from the sensor. It then connects to Wi-Fi and sends the data directly to the InfluxDB API endpoint. To conserve power, the device uses deep sleep between readings.

    Technology: C++, PlatformIO

2. Web Application (/web-src)

The web application is a multi-service stack deployed on Fly.io.

    Database (howhotismal-influxdb): An InfluxDB instance that serves as the time-series database. It receives data from the ESP32 firmware and exposes a public API for the web server to query.

    Web Server (howhotismal-apache): An Apache/PHP server that hosts the public-facing dashboard. It contains a PHP API to query the InfluxDB database and a front-end that uses Chart.js to render the data into live, auto-updating charts.

    Technology: PHP, Docker, InfluxDB, Chart.js

Setup and Deployment

Detailed instructions for setting up and deploying each component are located within their respective directories.

    Firmware: For instructions on configuring and flashing the ESP32, see the **firmware README**.

    Web Application: For instructions on deploying the InfluxDB and Apache/PHP services to Fly.io, see the **web application README**.
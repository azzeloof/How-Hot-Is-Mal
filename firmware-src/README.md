HowHotIsMal ESP32 Firmware

This directory contains the source code for the ESP32 microcontroller that reads environmental data from a BME280 sensor and sends it to an InfluxDB database.

The project is built using the PlatformIO IDE.
Overview

The firmware performs the following actions:

    Initializes and reads temperature, humidity, and pressure data from an attached BME280 sensor.

    Connects to a specified Wi-Fi network.

    Formats the sensor data into InfluxDB's Line Protocol format.

    Sends the data to the InfluxDB server via an HTTP POST request.

    Enters a deep sleep mode for 5 minutes to conserve power before repeating the cycle.

Configuration

Before flashing the firmware, you must provide your specific credentials for Wi-Fi and InfluxDB.

    Navigate to the firmware-src/howhotismal-firmware/src/ directory.

    Create a new file named config.h.

    Copy the contents below into config.h and replace the placeholder values with your own.

// src/config.h

#pragma once

// -- Wi-Fi Credentials --
#define WIFI_SSID "YOUR_WIFI_SSID"
#define WIFI_PASS "YOUR_WIFI_PASSWORD"

// -- InfluxDB Configuration --
// Example: "[https://influx.howhotismal.com](https://influx.howhotismal.com)"
#define INFLUXDB_URL "YOUR_INFLUXDB_URL" 

// The token you generated for your InfluxDB bucket
#define INFLUXDB_TOKEN "YOUR_INFLUXDB_TOKEN"

// Your InfluxDB organization and bucket names
#define INFLUXDB_ORG "YOUR_INFLUXDB_ORG"
#define INFLUXDB_BUCKET "YOUR_INFLUXDB_BUCKET"

Note: The config.h file is intentionally not included in the git repository to keep your secrets private.
Flashing Instructions (PlatformIO)

These instructions assume you have Visual Studio Code and the PlatformIO IDE extension installed.

    Open the Project:

        Open Visual Studio Code.

        Click the PlatformIO icon on the left-hand sidebar.

        Under "PIO Home", click "Open".

        Select "Open Project..." and navigate to and select the firmware-src/howhotismal-firmware folder.

    Connect the Board:

        Connect your ESP32 development board to your computer via USB.

    Build & Upload:

        Once the project is open, click the Upload button in the PlatformIO toolbar at the bottom of the VS Code window (it looks like a right-pointing arrow).

        PlatformIO will automatically compile the code, install the necessary libraries (like the Adafruit BME280 library), and upload the firmware to your ESP32.

    Monitor Output (Optional):

        To view the serial output from the device (e.g., connection status, sensor readings), click the Serial Monitor button in the PlatformIO toolbar (it looks like a power plug).
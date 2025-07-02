# ESP32 Firmware

This directory contains the source code for the ESP32 microcontroller that reads environmental data from a BME280 sensor and sends it to an InfluxDB database.

The project is built using the [PlatformIO IDE](https://platformio.org/).

## Overview

The firmware performs the following actions:
1.  Initializes an attached BME280 sensor.
2.  Connects to a specified Wi-Fi network.
3.  Reads temperature, humidity, and pressure data
5.  Sends the data to the InfluxDB server via an HTTP POST request.
6.  Waits for 30 seconds before repeating the read-write cycle.

## Configuration

Before flashing the firmware, you must provide your specific credentials for Wi-Fi and InfluxDB.

1.  Navigate to the `firmware-src/howhotismal-firmware/include` directory.
2.  Create a new file named `secrets.h`.
3.  Copy the contents below into `secrets.h` and replace the placeholder values with your own.

```cpp
// include/secrets.h

#ifndef _SECRETS_H_
#define _SECRETS_H_

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
#define TZ_INFO "EST5EDT"

#endif
```

**Note:** The `secrets.h` file is intentionally not included in the git repository to keep your secrets private.

## Flashing Instructions (PlatformIO)

The project is configured for an ESP32-S3 (2MB PSRAM) Feather board from Adafruit, but it is easy to configure the `platformio.ini` file to build for other boards.

These instructions assume you have [Visual Studio Code](https://code.visualstudio.com/) and the [PlatformIO IDE extension](https://platformio.org/install/ide?install=vscode) installed.

1.  **Open the Project:**
    * Open Visual Studio Code.
    * Click the PlatformIO icon on the left-hand sidebar.
    * Under "PIO Home", click "Open".
    * Select "Open Project..." and navigate to and select the `firmware-src/howhotismal-firmware` folder.

2.  **Connect the Board:**
    * Connect your ESP32 development board to your computer via USB.

3.  **Build & Upload:**
    * Once the project is open, click the **Upload** button in the PlatformIO toolbar at the bottom of the VS Code window (it looks like a right-pointing arrow).
    * PlatformIO will automatically compile the code, install the necessary libraries (like the Adafruit BME280 library), and upload the firmware to your ESP32.

4.  **Monitor Output (Optional):**
    * To view the serial output from the device (e.g., connection status, sensor readings), click the **Serial Monitor** button in the PlatformIO toolbar (it looks like a power plug).

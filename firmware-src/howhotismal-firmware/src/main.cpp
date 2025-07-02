#include <Arduino.h>
#include <Wire.h>
#include <SparkFunBME280.h>
#include <WiFiMulti.h>
#include <InfluxDbClient.h>
#include <InfluxDbCloud.h>
#include "secrets.h"

WiFiMulti wifi;

InfluxDBClient client(INFLUXDB_URL, INFLUXDB_ORG, INFLUXDB_BUCKET, INFLUXDB_TOKEN, InfluxDbCloud2CACert);
Point readings("measurements");

BME280 bme280;

float temperature;
float humidity;
float pressure;


void setup() {
  Serial.begin(115200);
  delay(5000);
  Serial.println("Starting...");
  delay(1000);
  Wire.begin();
  if (bme280.beginI2C() == false) {
    Serial.println("The sensor did not respond. Please check wiring.");
    while(1); //Freeze
  }
  Serial.println("Connecting to Wifi...");
  wifi.addAP(WIFI_SSID, WIFI_PASSWORD);
  while (wifi.run() != WL_CONNECTED) {
    Serial.print(".");
    delay(500);
  }
  Serial.println("Wifi Connected!");
  timeSync(TZ_INFO, "pool.ntp.org", "time.nis.gov");
  if (client.validateConnection()) {
    Serial.print("Connected to InfluxDB: ");
    Serial.println(client.getServerUrl());
  } else {
    Serial.print("InfluxDB connection failed: ");
    Serial.println(client.getLastErrorMessage());
  }
}

void loop() {
  readings.clearFields();
  temperature = bme280.readTempF();
  humidity = bme280.readFloatHumidity();
  pressure = bme280.readFloatPressure();
  readings.addField("temperature", temperature);
  readings.addField("humidity", humidity);
  readings.addField("pressure", pressure);
  Serial.print("Writing: ");
  Serial.println(client.pointToLineProtocol(readings));
  if (!client.writePoint(readings)) {
    Serial.print("InfluxDB write failed: ");
    Serial.println(client.getLastErrorMessage());
  }
  if (wifi.run() != WL_CONNECTED) {
    Serial.println("Wifi connection lost");
  }
  delay(30000);
}

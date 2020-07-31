#ifndef BREATHSENSE_CONFIG_H
#define BREATHSENSE_CONFIG_H




// Configurazioni generali
#define CONFIG_SERIAL_BAUD  115200
#define CONFIG_DEVICE_NAME  "testcfg"

// Configurazione connessione rete di servizio
#define CONFIG_SERVICENET_SSID "E.T. Telefono Casa"
#define CONFIG_SERVICENET_PASS "49WT6MEK6E"

// Configurazione client MQTT
#define CONFIG_MQTT_ENDPOINT "a2pdc6g0m5k5kq-ats.iot.eu-central-1.amazonaws.com"
#define CONFIG_MQTT_PORT 8883

// Configurazione campionatore battiti
#define CONFIG_HR_SAMPLING_TIME  30
#define CONFIG_HR_SAMPLES        200




#endif

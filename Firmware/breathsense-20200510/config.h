#ifndef BREATHSENSE_CONFIG_H
#define BREATHSENSE_CONFIG_H




// General configurations
#define CONFIG_SERIAL_BAUD  115200
#define CONFIG_DEVICE_NAME  "testcfg"

// Wifi Network
#define CONFIG_SERVICENET_SSID "YOUR WIFI NETWORK HERE"
#define CONFIG_SERVICENET_PASS "YOUR WIFI PASSWORD HERE"

// MQTT Client configuration                
#define CONFIG_MQTT_ENDPOINT "xxxxxxxxxxxxxx-ats.iot.eu-central-1.amazonaws.com" // Enter your endpoint here
#define CONFIG_MQTT_PORT 8883

// Pace sampler configuration
#define CONFIG_HR_SAMPLING_TIME  30
#define CONFIG_HR_SAMPLES        200




#endif

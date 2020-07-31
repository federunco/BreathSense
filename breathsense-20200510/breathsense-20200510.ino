#include "config.h"
#include "secrets.h"
#include "BreathSense_MAX30100.h"
#include "utils.h"

#include <WiFi.h>          
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

WiFiClientSecure net = WiFiClientSecure();
BreathSense_MAX30100 ppgSensor = BreathSense_MAX30100();
PubSubClient client(net);
BreathSenseUtils utils = BreathSenseUtils();

float hrSamples = 0;
uint8_t hrCount = 0, oldFingerStatus = 0;
int hr, nextSampler;
uint8_t enablePPGTransmission = 0;

void callback(char* topic, byte* payload, unsigned int length) {
  String stPayload;
  for (int i=0;i<length;i++) {
    stPayload += String((char) payload[i]);
  }

  StaticJsonDocument<200> response;
  DeserializationError inParseError = deserializeJson(response, stPayload);
  if (inParseError) {
    //Lettura payload fallita
    return;
  }
  
  if (!strcmp(response["command"], "pingRequest")) {
    publishString(utils.getDeviceTopic(), "command", String("acknowledge"));
    if (ppgSensor.isFingerAttached())
      publishString(utils.getDeviceTopic(), "command", String("acknowledgeOkFinger"));
    else 
      publishString(utils.getDeviceTopic(), "command", String("acknowledgeNoFinger"));
  }
    
  if (!strcmp(response["command"], "enablePPG")) 
    enablePPGTransmission = 1;

  if (!strcmp(response["command"], "disablePPG")) 
    enablePPGTransmission = 0;
}


void setup() {
  Serial.begin(CONFIG_SERIAL_BAUD);
  ppgSensor.begin();
  utils.begin();

  Serial.println("Connecting to " CONFIG_SERVICENET_SSID);
  WiFi.begin(CONFIG_SERVICENET_SSID, CONFIG_SERVICENET_PASS);

  while (WiFi.status() != WL_CONNECTED) {
    Serial.print("."); 
    delay(500);
  }
  
  net.setCACert(SEC_AWS_ROOT_CA);
  net.setCertificate(SEC_DEVICE_KEY_PUBLIC);
  net.setPrivateKey(SEC_DEVICE_KEY_PRIVATE);

  client.setServer(CONFIG_MQTT_ENDPOINT, CONFIG_MQTT_PORT);
  client.setCallback(callback);

  ppgSensor.startMonitoring();
}


void loop() {  
  if (millis() > nextSampler && ppgSensor.isFingerAttached()){
    if (enablePPGTransmission){
      publishFloat(utils.getPPGTopic(), "ppg", ppgSensor.getRawWaveform());
    }
    
    hrSamples += ppgSensor.getRate();
    if (++hrCount == CONFIG_HR_SAMPLES){
      hr = (int) (hrSamples / CONFIG_HR_SAMPLES);
      hrSamples = 0,
      hrCount = 0;
      publishInt(utils.getBPMTopic(), "bpm", hr);
    }    
    nextSampler = millis() + CONFIG_HR_SAMPLING_TIME;
  }

  if (ppgSensor.isFingerAttached() != oldFingerStatus){
    Serial.println(ppgSensor.isFingerAttached() ? "Dito connesso" : "dito disconnesso");
    publishString(utils.getDeviceTopic(), "command", ppgSensor.isFingerAttached() ? "fingerAttached" : "fingerDetached");
    oldFingerStatus = ppgSensor.isFingerAttached();
  }

  if (ppgSensor.isSPO2Ready() && ppgSensor.isFingerAttached()){
    publishFloat(utils.getSPO2Topic(), "spo2", ppgSensor.getSPO2());
  }
  
  if (!client.connected()) reconnect();
  
  client.loop();
  ppgSensor.loop();
  
  delay(10);
}

void reconnect(){
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    if (client.connect(utils.getDeviceIdentifier())) {
      Serial.println("connected");
      if (!client.subscribe(utils.getDeviceTopic())) Serial.println("errore sub");
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" retrying in 5 seconds");
      delay(5000);
    }
  }
}

void publishData(const char* topic, StaticJsonDocument<200> doc){
  char jsonBuffer[512];
  serializeJson(doc, jsonBuffer);
  
  if (!client.beginPublish(topic, strlen(jsonBuffer), false)) Serial.println("NO PUBLISH");
  if (!client.print(jsonBuffer)) Serial.println("NO PUBLISH");
  client.endPublish();
}

void publishString(const char* topic, const char* header, String data){
  StaticJsonDocument<200> doc;
  doc[header] = data;
  
  publishData(topic, doc);
}

void publishInt(const char* topic, const char* header, int data){
  StaticJsonDocument<200> doc;
  doc[header] = data;
  
  publishData(topic, doc);
}

void publishFloat(const char* topic, const char* header, float data){
  StaticJsonDocument<200> doc;
  doc[header] = data;
  
  publishData(topic, doc);
}

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
    utils.logError("Malformed payload, aborting");
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
  ppgSensor.begin();
  utils.begin();

  utils.logNeutral("\n           s##m    @#    ###m,\n");
  utils.logNeutral("        ,#M\"  ^#   @#   #b   7@m\n");
  utils.logNeutral("      ,#b      @#  @#  @b      \"@p\n");
  utils.logNeutral("     @#`        #b @# ]#         %#\n");
  utils.logNeutral("    @#          @Qs##m@#          7#\n");
  utils.logNeutral("   @#           '|`  |^|           @#\n");
  utils.logNeutral("  ]#            ,s####m,            @Q\n");
  utils.logNeutral("  #b            @#    @#            j#\n");
  utils.logNeutral(" j#             @#    @#             @b\n");
  utils.logNeutral(" @#             @#    @#             @#\n");
  utils.logNeutral(" @b             @#    @#             @#\n");
  utils.logNeutral(" @b             #b    ^#             j#\n");
  utils.logNeutral(" #b            @#      @#            j#\n");
  utils.logNeutral(" #b           ##.       @#,          j#\n");
  utils.logNeutral(" @b    ,s####W^          ^\"5WW#MQ    @#\n");
  utils.logNeutral(" j#m,##W`                       ^%#m##^\n");
  utils.logNeutral("   ||~\n\n");

  utils.logNeutral("BreathSense v1\n");
  utils.logNeutral("2020 (c) Federico Runco\n");
  utils.logNeutral("FW Compiled on " __DATE__ "\n");

  utils.logNeutral("----------\n");
  utils.logNeutral("Device Identifier: ");
  Serial.println(utils.getDeviceIdentifier());
  utils.logNeutral("SPO2 Sensor Part ID: ");
  Serial.println(ppgSensor.getSensorPartID());
  utils.logNeutral("SPO2 Sensor Revision ID: ");
  Serial.println(ppgSensor.getSensorRevisionID());
  utils.logNeutral("SPO2 Sensor die temperature: ");
  Serial.println(ppgSensor.getSensorTemperature());
  utils.logNeutral("----------\n\nINIT!\n\n");

  utils.logInfo("Connecting to WiFi (" CONFIG_SERVICENET_SSID ")");
  WiFi.begin(CONFIG_SERVICENET_SSID, CONFIG_SERVICENET_PASS);

  while (WiFi.status() != WL_CONNECTED) delay(100);

  utils.logInfo("Setting up System Certificates");
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
    utils.logInfo("Attempting MQTT connection");

    if (client.connect(utils.getDeviceIdentifier())) {
      utils.logInfo("Device connected to broker");
      if (!client.subscribe(utils.getDeviceTopic())) utils.logError("Cannot subscribe to service topic, device may not work properly");
    } else {
      utils.logInfo("MQTT Connection failed retrying in 5 seconds");
      //Serial.print("failed, rc=");
      //Serial.print(client.state());
      //Serial.println(" retrying in 5 seconds");
      delay(5000);
    }
  }
}

void publishData(const char* topic, StaticJsonDocument<200> doc){
  char jsonBuffer[512];
  serializeJson(doc, jsonBuffer);
  
  if (!client.beginPublish(topic, strlen(jsonBuffer), false)) utils.logError("Cannot publish data to broker");
  if (!client.print(jsonBuffer)) utils.logError("Cannot publish data to broker");
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

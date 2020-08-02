#include "utils.h"
#include <WiFi.h>
#include <stdint.h>

BreathSenseUtils::BreathSenseUtils(){}

bool BreathSenseUtils::begin(){
  Serial.begin(CONFIG_DEFAULT_BAUD);
  
  byte mac[6];
  WiFi.macAddress(mac);
  sprintf(deviceIdentifier, "%02x%02x%02x%02x%02x%02x", mac[0], mac[1], mac[2], mac[3], mac[4], mac[5]);

  strcat(deviceTopic, deviceIdentifier);
  strcat(deviceTopic, CONFIG_DEVICE_TOPIC);

  strcat(spo2Topic, deviceIdentifier);
  strcat(spo2Topic, CONFIG_SPO2_TOPIC);

  strcat(ppgTopic, deviceIdentifier);
  strcat(ppgTopic, CONFIG_PPG_TOPIC);

  strcat(bpmTopic, deviceIdentifier);
  strcat(bpmTopic, CONFIG_BPM_TOPIC);

  strcat(deviceHostname, deviceIdentifier);

  return true;
}

void BreathSenseUtils::logNeutral(String s){
  Serial.print(s);
  
}

void BreathSenseUtils::logError(String s){
  Serial.println(CONFIG_LOG_ERROR + s);
  
}

void BreathSenseUtils::logInfo(String s){
  Serial.println(CONFIG_LOG_INFO + s);
}

char *BreathSenseUtils::getDeviceIdentifier(){
  return deviceIdentifier;
}

char *BreathSenseUtils::getDeviceHostname(){
  return deviceHostname;
}

char *BreathSenseUtils::getDeviceTopic() {
  return deviceTopic;
}

char *BreathSenseUtils::getBPMTopic() {
  return bpmTopic;
}


char *BreathSenseUtils::getPPGTopic() {
  return ppgTopic;
}


char *BreathSenseUtils::getSPO2Topic() {
  return spo2Topic;
}

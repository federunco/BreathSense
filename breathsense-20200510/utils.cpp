#include "utils.h"
#include <WiFi.h>
#include <stdint.h>

BreathSenseUtils::BreathSenseUtils(){}

bool BreathSenseUtils::begin(){
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

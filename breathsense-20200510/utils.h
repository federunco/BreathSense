#ifndef BREATHSENSE_UTILS_H
#define BREATHSENSE_UTILS_H

#define CONFIG_BREATHSENSE_PREFIX "breathsense/"
#define CONFIG_BREATHSENSE_PREFIX_US "breathsense_"
#define CONFIG_SPO2_TOPIC "/spo2"
#define CONFIG_DEVICE_TOPIC "/device"
#define CONFIG_PPG_TOPIC "/ppg"
#define CONFIG_BPM_TOPIC "/bpm"

class BreathSenseUtils{
  public:
    BreathSenseUtils();
    bool begin();
    char *getDeviceIdentifier();
    char *getDeviceHostname();
    char *getBPMTopic();
    char *getPPGTopic();
    char *getDeviceTopic();
    char *getSPO2Topic();
  private:
    char deviceIdentifier[64];
    char deviceHostname[100] = CONFIG_BREATHSENSE_PREFIX_US;
    char deviceTopic[100] = CONFIG_BREATHSENSE_PREFIX;
    char spo2Topic[100] = CONFIG_BREATHSENSE_PREFIX;
    char ppgTopic[100] = CONFIG_BREATHSENSE_PREFIX;
    char bpmTopic[100] = CONFIG_BREATHSENSE_PREFIX;
};

#endif

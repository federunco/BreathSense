#include <Wire.h>

#include "BreathSense_MAX30100.h"

#ifndef min
#define min(a,b) \
   ({ __typeof__ (a) _a = (a); \
       __typeof__ (b) _b = (b); \
     _a < _b ? _a : _b; })
#endif

BreathSense_MAX30100::BreathSense_MAX30100(){
}

bool BreathSense_MAX30100::begin(){
  Wire.begin();
  
  // Reset della NVRAM del sensore
  writeRegister(0x06, 0x40);
  
  delay(100);

  diffFilter.index = 0;
  diffFilter.sum = 0;
  diffFilter.count = 0;
  return true;
}


bool BreathSense_MAX30100::startMonitoring(){
  // Salva i vecchi valori dei registri
  uint8_t regOriginalMode = readRegister(0x06);
  uint8_t regOriginalSpO2 = readRegister(0x07);

  // Configurazione del sensore
  writeRegister(0x06, (regOriginalMode & 0xF8) | DEFAULT_SENSOR_MODE);
  writeRegister(0x07, (regOriginalSpO2 & 0xE0) | (0x01 << 2) | 0x03);
  writeRegister(0x09, (DEFAULT_RED_LED_CURRENT << 4) | DEFAULT_IR_LED_CURRENT);

  return true;
}

void BreathSense_MAX30100::loop(){
  RawData readBuffer = readFIFO();
  irLPF = dcRemover(readBuffer.IR, irLPF.w, DEFAULT_IIR_FILTER_ALPHA);
  redLPF = dcRemover(readBuffer.red, redLPF.w, DEFAULT_IIR_FILTER_ALPHA);

  checkForBeat(irLPF.output);
  
  redRMS += redLPF.output * redLPF.output;
  irRMS += irLPF.output * redLPF.output;
  irDC += irLPF.w;
  redDC += redLPF.w;
  samples++;

  

  fingerStatus = readBuffer.IR > DEFAULT_FINGERATT_THRESHOLD;

  if (samples > DEFAULT_SPO2_SAMPLES){
    redRMS = sqrt(redRMS/DEFAULT_SPO2_SAMPLES);
    irRMS = sqrt(irRMS/DEFAULT_SPO2_SAMPLES);
    irDC /= DEFAULT_SPO2_SAMPLES;
    redDC /= DEFAULT_SPO2_SAMPLES;
    
    float ratio = (redRMS / redDC) / (irRMS / irDC);
    calculatedSPO2 = 110 - 25 * ratio;
    spo2Ready = 1;
    
    samples = 0;
    redRMS = 0;
    irRMS = 0;
    redDC = 0;
    irDC = 0;
  }
}

uint8_t BreathSense_MAX30100::getSensorTemperature(){
  return readRegister(0x16);
}

uint8_t BreathSense_MAX30100::getSensorRevisionID(){
  return readRegister(0xFE);
}

uint8_t BreathSense_MAX30100::getSensorPartID(){
  return readRegister(0xFF);
}

bool BreathSense_MAX30100::isSPO2Ready(){
  return spo2Ready;
}

uint8_t BreathSense_MAX30100::isFingerAttached(){
  return fingerStatus;
}

float BreathSense_MAX30100::getRawWaveform(){
  return irLPF.output;
}

float BreathSense_MAX30100::getSPO2(){
  spo2Ready = 0;
  return calculatedSPO2;
}

RawData BreathSense_MAX30100::readFIFO(){
  RawData result;

  uint8_t buffer[4];
  readFrom(0x05, 4, buffer);
  result.IR = (buffer[0] << 8) | buffer[1];
  result.red = (buffer[2] << 8) | buffer[3];

  return result;
}


FIRFilter BreathSense_MAX30100::dcRemover(float x, float prev_w, float alpha){
  FIRFilter filtered;
  filtered.w = x + alpha * prev_w;
  filtered.output = filtered.w - prev_w;

  return filtered;
}


float BreathSense_MAX30100::meanDiff(float M, MeanDiffFilter *filterValues){
  filterValues->sum -= filterValues->values[filterValues->index];
  filterValues->values[filterValues->index] = M;
  filterValues->sum += filterValues->values[filterValues->index];

  filterValues->index++;
  if (filterValues->index == MDIFF_VALUES) filterValues->index = 0;

  if(filterValues->count < MDIFF_VALUES)
    filterValues->count++;

  float avg = filterValues->sum / filterValues->count;
  return avg - M;
}

uint8_t BreathSense_MAX30100::readRegister(uint8_t address)
{
  Wire.beginTransmission(MAX30100_DEVICE_ADDRESS);
  Wire.write(address);
  Wire.endTransmission(false);
  Wire.requestFrom(MAX30100_DEVICE_ADDRESS, 1);

  return Wire.read();
}

void BreathSense_MAX30100::writeRegister(uint8_t address, uint8_t val)
{
  Wire.beginTransmission(MAX30100_DEVICE_ADDRESS);
  Wire.write(address); 
  Wire.write(val);
  Wire.endTransmission(); 
}

void BreathSense_MAX30100::readFrom(uint8_t address, int num, uint8_t _buff[])
{
  Wire.beginTransmission(MAX30100_DEVICE_ADDRESS); 
  Wire.write(address); 
  Wire.endTransmission(false); 

  Wire.requestFrom(MAX30100_DEVICE_ADDRESS, num); 

  int i = 0;
  while(Wire.available()) 
  {
    _buff[i++] = Wire.read(); 
  }

  Wire.endTransmission();
}

bool BreathSense_MAX30100::checkForBeat(float x)
{
    float sample = -x;
    bool beatDetected = false;

    switch (state) {
        case BEATDETECTOR_STATE_INIT:
            if (millis() > BEATDETECTOR_INIT_HOLDOFF) {
                state = BEATDETECTOR_STATE_WAITING;
            }
            break;

        case BEATDETECTOR_STATE_WAITING:
            if (sample > threshold) {
                threshold = min(sample, BEATDETECTOR_MAX_THRESHOLD);
                state = BEATDETECTOR_STATE_FOLLOWING_SLOPE;
            }

            // Tracking lost, resetting
            if (millis() - tsLastBeat > BEATDETECTOR_INVALID_READOUT_DELAY) {
                beatPeriod = 0;
                lastMaxValue = 0;
            }

            decreaseThreshold();
            break;

        case BEATDETECTOR_STATE_FOLLOWING_SLOPE:
            if (sample < threshold) {
                state = BEATDETECTOR_STATE_MAYBE_DETECTED;
            } else {
                threshold = min(sample, BEATDETECTOR_MAX_THRESHOLD);
            }
            break;

        case BEATDETECTOR_STATE_MAYBE_DETECTED:
            if (sample + BEATDETECTOR_STEP_RESILIENCY < threshold) {
                // Found a beat
                beatDetected = true;
                lastMaxValue = sample;
                state = BEATDETECTOR_STATE_MASKING;
                float delta = millis() - tsLastBeat;
                if (delta) {
                    beatPeriod = BEATDETECTOR_BPFILTER_ALPHA * delta +
                            (1 - BEATDETECTOR_BPFILTER_ALPHA) * beatPeriod;
                }

                tsLastBeat = millis();            
            } else {
                state = BEATDETECTOR_STATE_FOLLOWING_SLOPE;
            }
            break;

        case BEATDETECTOR_STATE_MASKING:
            if (millis() - tsLastBeat > BEATDETECTOR_MASKING_HOLDOFF) {
                state = BEATDETECTOR_STATE_WAITING;
            }
            decreaseThreshold();
            break;
    }

    return beatDetected;
}

void BreathSense_MAX30100::decreaseThreshold()
{
    if (lastMaxValue > 0 && beatPeriod > 0) {
        threshold -= lastMaxValue * (1 - BEATDETECTOR_THRESHOLD_FALLOFF_TARGET) /
                (beatPeriod / BEATDETECTOR_SAMPLES_PERIOD);
    } else {
        threshold *= BEATDETECTOR_THRESHOLD_DECAY_FACTOR;
    }

    if (threshold < BEATDETECTOR_MIN_THRESHOLD) {
        threshold = BEATDETECTOR_MIN_THRESHOLD;
    }
}

bool BreathSense_MAX30100::isHeartReateReady(){
  return hrStatus;
}

int BreathSense_MAX30100::getHeartRate(){
  hrStatus = 0;
  return extimatedHR;
}

float BreathSense_MAX30100::getRate()
{
    if (beatPeriod != 0) {
        return 1 / beatPeriod * 1000 * 60;
    } else {
        return -1;
    }
}

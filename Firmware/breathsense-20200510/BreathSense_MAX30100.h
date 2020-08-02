#ifndef BREATHSENSE_MAX30100_H
#define BREATHSENSE_MAX30100_H

#define MAX30100_DEVICE_ADDRESS 0x57
#define MAX30100_HRONLY 0x02
#define MAX30100_SP02HR 0x03

#define LED_CURRENT_000 0x00
#define LED_CURRENT_044 0x01
#define LED_CURRENT_076 0x02
#define LED_CURRENT_110 0x03
#define LED_CURRENT_142 0x04
#define LED_CURRENT_271 0x08
#define LED_CURRENT_500 0x0F

// ----------
// Configurazione libreria
// ----------

#define DEFAULT_RED_LED_CURRENT     LED_CURRENT_271
#define DEFAULT_IR_LED_CURRENT      LED_CURRENT_110

#define DEFAULT_SENSOR_MODE         MAX30100_SP02HR
#define DEFAULT_SPO2_SAMPLES        200
#define DEFAULT_IIR_FILTER_ALPHA    0.95
#define DEFAULT_FINGERATT_THRESHOLD 2000
#define DEFAULT_HRSAMPLES           5

#define MDIFF_VALUES 10

#define BEATDETECTOR_INIT_HOLDOFF                2000    // in ms, how long to wait before counting
#define BEATDETECTOR_MASKING_HOLDOFF             200     // in ms, non-retriggerable window after beat detection
#define BEATDETECTOR_BPFILTER_ALPHA              0.6     // EMA factor for the beat period value
#define BEATDETECTOR_MIN_THRESHOLD               20      // minimum threshold (filtered) value
#define BEATDETECTOR_MAX_THRESHOLD               800     // maximum threshold (filtered) value
#define BEATDETECTOR_STEP_RESILIENCY             30      // maximum negative jump that triggers the beat edge
#define BEATDETECTOR_THRESHOLD_FALLOFF_TARGET    0.3     // thr chasing factor of the max value when beat
#define BEATDETECTOR_THRESHOLD_DECAY_FACTOR      0.99    // thr chasing factor when no beat
#define BEATDETECTOR_INVALID_READOUT_DELAY       2000    // in ms, no-beat time to cause a reset
#define BEATDETECTOR_SAMPLES_PERIOD              10      // in ms, 1/Fs

typedef struct rawData_t {
  uint16_t IR;
  uint16_t red;
} RawData;

typedef struct firFilter_t {
  float w;
  float output;
} FIRFilter;

typedef struct meanDiffFilter_t {
  float values[MDIFF_VALUES];
  uint8_t index;
  float sum;
  uint8_t count;
} MeanDiffFilter;

typedef enum BeatDetectorState {
    BEATDETECTOR_STATE_INIT,
    BEATDETECTOR_STATE_WAITING,
    BEATDETECTOR_STATE_FOLLOWING_SLOPE,
    BEATDETECTOR_STATE_MAYBE_DETECTED,
    BEATDETECTOR_STATE_MASKING
} BeatDetectorState;

class BreathSense_MAX30100 {
  public:
    BreathSense_MAX30100();
    bool begin();
    bool startMonitoring();
    void loop();
    float getRawWaveform();
    int getHeartRate();
    float getSPO2();
    bool isSPO2Ready();
    uint8_t isFingerAttached();
    bool isHeartReateReady();
    float getRate();
    uint8_t getSensorTemperature();
    uint8_t getSensorRevisionID();
    uint8_t getSensorPartID();
    
  private:
    MeanDiffFilter diffFilter;
    FIRFilter irLPF, redLPF;
    float redRMS = 0, redDC = 0;
    float irRMS = 0, irDC = 0;
    uint8_t samples;
    float calculatedSPO2 = -1;
    uint8_t spo2Ready = 0, fingerStatus = 0, filteredPPG = 0, hrStatus = 0;
    BeatDetectorState state = BEATDETECTOR_STATE_INIT;
    float threshold = BEATDETECTOR_MIN_THRESHOLD;
    float beatPeriod = 0;
    float lastMaxValue = 0;
    uint32_t tsLastBeat = 0;
    uint8_t hrSamples = 0;
    float hrSum = 0;
    int extimatedHR;
    
    uint8_t readRegister(uint8_t address);
    void writeRegister(uint8_t address, uint8_t val);
    void readFrom(uint8_t address, int num, uint8_t _buff[]);
    FIRFilter dcRemover(float x, float prev_w, float alpha);
    float meanDiff(float M, MeanDiffFilter *filterValues);
    RawData readFIFO();
    void decreaseThreshold();
    bool checkForBeat(float x);
    
};

#endif

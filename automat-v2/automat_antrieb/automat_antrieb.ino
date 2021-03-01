#include <Servo.h>
#include <EEPROM.h>

Servo myservo;

enum states {STANDBY, REDEEM_START, REDEEM_PROGRESS, REDEEM_FINISH, ERRORSTATE};

#define CONFIG_ROTATION_PIN 5
#define CONFIG_ID_PIN0 3
#define CONFIG_ID_PIN1 4
#define CONFIG_MOTOR_PIN 9
#define CONFIG_REED_CONTACT_PIN 2

states act_state = STANDBY;
states next_state = STANDBY;
states last_state = STANDBY;
long state_duration_ms = 0; //how long the FSM already stayed in this state in ms
long last_state_duration_ms = 0; // on state change, copy long_state_duration_ms -> last_...
byte config_rotating_direction = 0;
byte config_id = 0;
byte motor_turning = 0; //is 1, in case the motor is intended to run
unsigned int redeem_count = 0;

// helper functions to store unsigned int into EEPROM
void writeUnsignedIntIntoEEPROM(int address, unsigned int number) { 
  EEPROM.write(address, number >> 8);
  EEPROM.write(address + 1, number & 0xFF);
}
unsigned int readUnsignedIntFromEEPROM(int address) {
  byte byte0 = EEPROM.read(address);
  byte byte1 = EEPROM.read(address + 1);
  return (byte0 << 8) + byte1;
}

//helper functions for motor
void start_motor() {
  motor_turning = 1;
  myservo.attach(CONFIG_MOTOR_PIN);
  if (config_rotating_direction) {
    myservo.write(10);
  } else {
    myservo.write(170);
  }
}
void stop_motor() {
  motor_turning = 0;
  myservo.detach();
}


void print_actual_state() {
  Serial.print("{");
  Serial.print("\"act_state\": "); Serial.print(act_state);
  Serial.print(", \"last_state\": "); Serial.print(last_state);
  Serial.print(", \"state_duration_ms\": "); Serial.print(state_duration_ms);
  Serial.print(", \"last_state_duration_ms\": "); Serial.print(last_state_duration_ms);
  Serial.print(", \"config_rotating_direction\": "); Serial.print(config_rotating_direction);
  Serial.print(", \"config_id\": "); Serial.print(config_id);
  Serial.print(", \"motor_turning\": "); Serial.print(motor_turning);
  Serial.print(", \"redeem_count\": "); Serial.print(redeem_count);
  Serial.print(", \"reed_contact_pin_state\": "); Serial.print(digitalRead(CONFIG_REED_CONTACT_PIN));
  Serial.println("}");
}

void setup() {
  Serial.begin(115200);
  redeem_count = readUnsignedIntFromEEPROM(0);
  
  pinMode(CONFIG_ROTATION_PIN, INPUT);
  digitalWrite(CONFIG_ROTATION_PIN, HIGH); //pull high
  pinMode(CONFIG_ID_PIN0, INPUT);
  digitalWrite(CONFIG_ID_PIN0, HIGH); //pull high
  pinMode(CONFIG_ID_PIN1, INPUT);
  digitalWrite(CONFIG_ID_PIN1, HIGH); //pull high

  pinMode(CONFIG_REED_CONTACT_PIN, INPUT);
  digitalWrite(CONFIG_REED_CONTACT_PIN, HIGH); //pull high
  
  //wait for levels to settle
  delay(100);
  config_rotating_direction = digitalRead(CONFIG_ROTATION_PIN);
  config_id = digitalRead(CONFIG_ID_PIN0) + digitalRead(CONFIG_ID_PIN1)*2;

  stop_motor();
}


void loop() {
  //FSM: switch to next state?
  if (next_state != act_state) {
    last_state_duration_ms = state_duration_ms;
    state_duration_ms = 0;
    last_state = act_state;
    act_state = next_state;
    print_actual_state();
  }
  
  //read from Serial
  byte incomingByte = 0; 
  if (Serial.available() > 0) {
    incomingByte = Serial.read();
    if (incomingByte == '0') {
      redeem_count = 0;
      writeUnsignedIntIntoEEPROM(0, redeem_count);
    }
  }

  //FSM: execute AND next state
  next_state = act_state;
  switch (act_state) {
    case STANDBY:
      if (incomingByte == '+') {
        next_state = REDEEM_START;
        start_motor();
      }
      break;
    case REDEEM_START:
      //after 500ms in this state, step to next
      //500ms are approx a half turn
      //this also avoids contact bouncing
      if (state_duration_ms > 500) next_state = REDEEM_PROGRESS;
      break;
    case REDEEM_PROGRESS:
      if (digitalRead(CONFIG_REED_CONTACT_PIN) == 0) next_state = REDEEM_FINISH;
      //if approx longer than 2 turns:
      if (state_duration_ms > 2500) next_state = ERRORSTATE;
      break;
    case REDEEM_FINISH:
      stop_motor();
      redeem_count++;
      writeUnsignedIntIntoEEPROM(0, redeem_count);
      next_state = STANDBY;
      break;
    case ERRORSTATE:
      stop_motor();
      next_state = ERRORSTATE;
      break;
    default: next_state = ERRORSTATE;
  }

  //every second some info 
  if (state_duration_ms % 1000 == 0) print_actual_state();

  state_duration_ms++;
  delay(1);
}

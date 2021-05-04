#include <Adafruit_NeoPixel.h>
#include <ArduinoJson.h>

// Which pin on the Arduino is connected to the NeoPixels?
#define PIN        6

// How many NeoPixels are attached to the Arduino?
#define NUMPIXELS 30

// When setting up the NeoPixel library, we tell it how many pixels,
// and which pin to use to send signals. Note that for older NeoPixel
// strips you might need to change the third parameter -- see the
// strandtest example for more information on possible values.
Adafruit_NeoPixel pixels(NUMPIXELS, PIN, NEO_GRB + NEO_KHZ800);

void setup() {
  Serial.begin(115200);
  while (!Serial) continue;

  pixels.begin(); // INITIALIZE NeoPixel strip object (REQUIRED)
}

void loop() {
   //read from Serial
    if (Serial.available()) 
  {
    // Allocate the JSON document
    // This one must be bigger than for the sender because it must store the strings
    StaticJsonDocument<1024> doc; //Size calculated here: https://arduinojson.org/v6/assistant/#

    // Read the JSON document from the "link" serial port
    DeserializationError err = deserializeJson(doc, Serial);

    if (err == DeserializationError::Ok) 
    {
      // extract the values
      JsonArray array = doc.as<JsonArray>();
      byte i = 0;
      for(JsonVariant v : array) {
          pixels.setPixelColor(i, pixels.Color(v[0].as<byte>(), v[1].as<byte>(), v[2].as<byte>()));
          i++;
      }
      pixels.show(); 
    } 
    else 
    {
      // Print error to the "debug" serial port
      Serial.print("deserializeJson() returned ");
      Serial.println(err.c_str());
  
      // Flush all bytes in the "link" serial port buffer
      while (Serial.available() > 0)
        Serial.read();
    }
  }
}

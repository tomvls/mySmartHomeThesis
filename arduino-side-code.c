#include <SPI.h>
#include <Ethernet.h>
#include <dht.h>
#include <ArduinoJson.h>

dht DHT;

// 
const int loopInterval = 5000;

// define testing senario
enum senarioTypes { NONE, FIRE_SENARIO, GAS_SENARIO, FIRE_AND_GAS_SENARIO, HIGH_TEMP,  LOW_TEMP, LIGHTS , ROBBERY, RAIN };
senarioTypes senario = NONE;

// Define Pins for Sensors and Devices

// LivingRoom 
// sensors
#define DHT11_DPIN_LR 44
#define LIGHT_APIN_LR A9
#define PIR_DPIN_LR 39
#define REED_APIN_LR 33
 // devices 
 #define LAMP_LR 43
 #define HEATING_LR 41
 #define AC_LR 42


// Kitchen
// sensors
// #define LM35_K A13
#define DHT11_DPIN_K 48
#define LIGHT_APIN_K A15
#define FLAME_DPIN_K 46
#define GAS_APIN_K A8
// // devices
 #define LAMP_K 40

// Outside
// sensors
#define DHT11_DPIN_O 7
#define LIGHT_APIN_O A11
#define RAIN_APIN_O A5
// devices
#define LAMP_O 45
#define TENTS 47

// Thresholds
const int insideLightThreshold = 512;
const int outsideLightThreshold = 512;
const int gasThreshold = 300;
const int rainThreshold = 400;

struct CommandData {
   const char* room;
   int id;
   const char* lampSwitch;
   const char* heatingSwitch;
   const char* acSwitch;
   const char* tentsSwitch;
};
// #define COMMANDDATA_JSON_SIZE (JSON_OBJECT_SIZE(6));

CommandData curCommand,lastCommand; 

struct HomeInfo {
  const char* mode;
  int desiredTempLivingRoom;
  int desiredTempKitchen;
};
HomeInfo homeInfo;

// Living Room Variables
// sensorings
int temperatureLivingRoom;
int humidityLivingRoom;
int lightLivingRoom;
bool motionDetectedLivingRoom;
bool doorIsOpen;      // used in alarm operation
// devices statuses
char heatingStatusLivingRoom[] = "NONE";
char acStatusLivingRoom[] = "NONE";
char lampStatusLivingRoom[] = "NONE";


// Kitchen Variables
int temperatureKitchen;
int humidityKitchen;
int lightKitchen;
bool gasInKitchen;
bool flameInKitchen;
// devices statuses
char lampStatusKitchen[] = "NONE";
// kitchen status (used in fire and gas alarm)
enum kitchenStatus { SAFE, FIRE, GAS, FIRE_AND_GAS};
kitchenStatus kitchenCurrStatus = SAFE;

// Outside Variables
int humidityOutside;
int temperatureOutside;
int lightOutside;
bool rainOutside;
char lampStatusOutside[] = "NONE";
char tentsStatusOutside[] = "NONE";  // ON=open , OFF=closed

String rcv=""; //Variable in which the server response is recorded.
StaticJsonDocument<200> doc;

byte mac[] = {
  0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
 
// IP address for Arduino
IPAddress ip(192,168,1,160);

char server[] = "192.168.1.5"; 

// Initialize the Ethernet server library
EthernetClient client;

void setup() {
  analogReference(INTERNAL1V1);
  
  // Declare INPUT Pins
  pinMode(LIGHT_APIN_LR,INPUT);
  pinMode(PIR_DPIN_LR, INPUT);
  pinMode(REED_APIN_LR, INPUT_PULLUP);
  pinMode(LIGHT_APIN_K,INPUT);
  pinMode(FLAME_DPIN_K, INPUT);
  pinMode(GAS_APIN_K, INPUT);
  pinMode(LIGHT_APIN_O,INPUT);

  // Declare OUTPUT Pins
  pinMode(LAMP_LR, OUTPUT);
  pinMode(HEATING_LR, OUTPUT);
  pinMode(AC_LR, OUTPUT);
  pinMode(LAMP_K, OUTPUT);
  pinMode(LAMP_O, OUTPUT);

  lastCommand.id = -1; 
  // Serial.begin starts the serial connection between computer and Arduino
  Serial.begin(9600);
 
  // start the Ethernet connection
  Ethernet.begin(mac, ip);
    
}

void loop() {
   

  // Get sensorings from Living Room
  // Get temperature and humidity from DHT11 sensor
  int metric1 = DHT.read11(DHT11_DPIN_LR);
  temperatureLivingRoom = DHT.temperature;
  humidityLivingRoom = DHT.humidity;
  lightLivingRoom = getLightDetection(LIGHT_APIN_LR);
  motionDetectedLivingRoom = motionDetected(PIR_DPIN_LR);
  doorIsOpen = magneticReedisOpen(REED_APIN_LR);

  // Get sensorings from Kitchen
  // Get temperature and humidity from DHT11 sensor
  int metric2 = DHT.read11(DHT11_DPIN_K);
  temperatureKitchen = DHT.temperature;
  humidityKitchen = DHT.humidity;
  lightKitchen = getLightDetection(LIGHT_APIN_K);
  flameInKitchen = flameDetected(FLAME_DPIN_K);
  gasInKitchen = gasDetected(GAS_APIN_K);

  // // Get Sensorings from Outside
  // Get temperature and humidity from DHT11 sensor
  int metric3 = DHT.read11(DHT11_DPIN_O);
  temperatureOutside = DHT.temperature;
  humidityOutside = DHT.humidity;
  lightOutside = getLightDetection(LIGHT_APIN_O);
  rainOutside = rainingOutside(RAIN_APIN_O); 

  // Display Smart Home Sensorings
  Serial.println("===========  Living Room  ===========");
  Serial.print("Temperature: ");
  Serial.print(temperatureLivingRoom);
  Serial.println(" °C");
  Serial.print("Humidity: ");
  Serial.println(humidityLivingRoom);
  Serial.print("Light: ");
  Serial.println(lightLivingRoom);
  Serial.print("Motion detected: ");
  Serial.println(motionDetectedLivingRoom);
  Serial.print("Main Door is open: ");
  Serial.println(doorIsOpen);
  Serial.print("Heating: ");
  Serial.println(heatingStatusLivingRoom);
  Serial.print("AC: ");
  Serial.println(acStatusLivingRoom);
  Serial.print("Lamp: ");
  Serial.println(lampStatusLivingRoom);
  Serial.println("===========  Kitchen  ===========");
  Serial.print("Temperature: ");
  Serial.print(temperatureKitchen);
  Serial.println(" °C");
  Serial.print("Humidity: ");
  Serial.println(humidityKitchen);
  Serial.print("Light: ");
  Serial.println(lightKitchen);
  Serial.print("Flame detected: ");
  Serial.println(flameInKitchen);
  Serial.print("Gas detected: ");
  Serial.println(gasInKitchen);
  Serial.print("Lamp: ");
  Serial.println(lampStatusKitchen);
  Serial.println("===========  Outside  ===========");
  Serial.print("Temperature: ");
  Serial.print(temperatureOutside);
  Serial.println(" °C");
  Serial.print("Light: ");
  Serial.println(lightOutside);
  Serial.print("Humidity: ");
  Serial.println(humidityOutside);
  Serial.print("Rain detected: ");
  Serial.println(rainOutside);
  Serial.print("Lamp: ");
  Serial.println(lampStatusOutside);
  Serial.print("Tents: ");
  Serial.println(tentsStatusOutside);

  // check if a senario should be executed
  if (senario != NONE) {
  //   // Simulation Senario execution
  //   // Save sensor and devices data on db
    saveDataOnDB(); 

    runSenario(senario);
  }else {
    // Normal execution
    // Get desired temprature for every room
    homeInfo = getHomeInfo();
    Serial.print("Smart Home mode: ");
    Serial.println(homeInfo.mode);
    // Serial.print("Desired temperature on Living Room: ");
    // Serial.println(homeInfo.desiredTempLivingRoom);
    // Serial.print("Desired temperature on Kitchen: ");
    // Serial.println(homeInfo.desiredTempKitchen);
    
    // Kitchen alarms (fire and gas). Execute in every mode
    kitchenAlarms();

    // skip if in Auto mode
    if ( (strcmp(homeInfo.mode,"MANUAL") == 0) || (strcmp(homeInfo.mode,"AWAY") == 0) ) {

      if (strcmp(homeInfo.mode,"AWAY") == 0) {
        // Check for open door
        homeInvasionAlarm();
      }
      
      // Get user control command (switch on/off etc)
      getUserLastCommand();
      
    }else if ( (strcmp(homeInfo.mode,"AUTO") == 0) || (strcmp(homeInfo.mode,"POWER-SAVING") == 0) ) {
      // Auto of Power-Saving mode
      if (homeInfo.desiredTempLivingRoom != -1) {
        Serial.println("Starting Automations...");
        makeSmartDecisions();
      }
    }
    
    // Save sensor and devices data on db
    saveDataOnDB();
  }
  
  // Wait before the next execution.
  delay(loopInterval);
}

void saveDataOnDB(){
  if (client.connect(server,80)) {
    Serial.println("Saving Data from Sensors on Database...");
    client.print("GET /smartHome/saveSensoringsOnDb.php?"); // This
    client.print("tempLR="); 
    client.print(temperatureLivingRoom); 
    client.print("&");
    client.print("humidityLR="); 
    client.print(humidityOutside); 
    client.print("&");
    client.print("lightLR=");
    client.print(lightLivingRoom);
    client.print("&"); 
    client.print("motion=");
    client.print(motionDetectedLivingRoom);
    client.print("&");
    client.print("heating=");
    client.print(heatingStatusLivingRoom);
    client.print("&");
    client.print("ac=");
    client.print(acStatusLivingRoom);
    client.print("&");
    client.print("lampLR=");
    client.print(lampStatusLivingRoom);
    client.print("&");
    client.print("door=");
    client.print(doorIsOpen);
    client.print("&");
    client.print("tempK="); 
    client.print(temperatureKitchen); 
    client.print("&");
    client.print("humidityK="); 
    client.print(humidityKitchen); 
    client.print("&");
    client.print("lightK=");
    client.print(lightKitchen);
    client.print("&");
    client.print("gas="); 
    client.print(gasInKitchen); 
    client.print("&");
    client.print("flame=");
    client.print(flameInKitchen);
    client.print("&");
    client.print("lampK=");
    client.print(lampStatusKitchen);
    client.print("&");
    client.print("tempO="); 
    client.print(temperatureOutside); 
    client.print("&");
    client.print("lightO=");
    client.print(lightOutside);
    client.print("&");
    client.print("humidity="); 
    client.print(humidityOutside); 
    client.print("&");
    client.print("rain=");
    client.print(rainOutside);
    client.print("&");
    client.print("lampO=");
    client.print(lampStatusOutside);
    client.print("&");
    client.print("tents=");
    client.println(tentsStatusOutside);
    client.println(" HTTP/1.1"); 
    client.print("Host: "); 
    client.println(server);
    client.println("Connection: close"); 
    client.println(); 
    client.println(); 
    client.stop();
  }else {
    Serial.println("Connection failed while saving Data from Sensors on Database...");
  }
}


// Get latest document from db table "Commands" and if it is a new one, execute it 
void getUserLastCommand() {
  if (client.connect(server,80)) {
    Serial.println("Getting User last command from Database...");

    client.print(String("GET ") + "/smartHome/getUserLastCommand.php/" + " HTTP/1.1\r\n" + "Host: " + server + "\r\n" + "Connection: close\r\n\r\n"); //GET request for server response.
    unsigned long timeout = millis();
    while (client.available() == 0) {
      if (millis() - timeout > 5000) {  //If nothing is available on server for 25 seconds, close the connection.
        return;
      }
    }
    while(client.available()) {
      String line = client.readStringUntil('\r'); //Read the server response line by line and store it in rcv var.
      rcv+=line; 
    }
    client.stop(); // Close the connection.

    // get command from server response
    int commandStartIndex = rcv.indexOf("{");
    int commandEndIndex = rcv.indexOf("}");
    String commandDataStr = rcv.substring(commandStartIndex,commandEndIndex+1);
    
    // convert command to json
    DeserializationError error = deserializeJson(doc,commandDataStr);
    if (error) {
      Serial.print("deserializeJson() failed with code ");
      Serial.println(error.c_str());
    }

    curCommand = setCurrentCommandObject(curCommand,doc);

    // Skip command if it has been already executed
    if (curCommand.id == lastCommand.id) {
        Serial.print("Already executed command with id:");
        Serial.println(curCommand.id);
    }else {
        Serial.print("New command with id:");
        Serial.println(curCommand.id);

        lastCommand.id = curCommand.id;
        Serial.print("Room: ");
        Serial.println(curCommand.room);

        // Command is about Living Room
        if (strcmp(curCommand.room,"LivingRoom") == 0) {

            // Check if command device status is nul
            if (!(strcmp(curCommand.lampSwitch,"nul") == 0)) {

                // turn ON/OFF lamp on Living room
                Serial.print("Lamp-LR current switch status: ");
                Serial.println(lampStatusLivingRoom);

                // Check if command's lamp status is different from device current status
                if (!(strcmp(curCommand.lampSwitch,lampStatusLivingRoom) == 0)) {
                    
                    if (strcmp(curCommand.lampSwitch,"ON") == 0) {
                        Serial.println("TURNING ON LAMP");
                        digitalWrite(LAMP_LR,HIGH);
                        strcpy(lampStatusLivingRoom,"ON");
                    }else if (strcmp(curCommand.lampSwitch,"OFF") == 0) {
                        Serial.println("TURNING OFF LAMP");
                        digitalWrite(LAMP_LR,LOW);
                        strcpy(lampStatusLivingRoom,"OFF");
                    }

                }else {
                    Serial.println("Lamp-LR switch status has not change!");
                }

            }else {
                // Serial.println("Lamp-LR switch status is null!");
            }

            if (!(strcmp(curCommand.acSwitch,"nul") == 0)) {
                
                // turn ON/OFF ac on Living room
                Serial.print("AC-LR current switch status: ");
                Serial.println(acStatusLivingRoom);

                // Check if command's ac status is different from ac current status
                if (!(strcmp(curCommand.acSwitch,acStatusLivingRoom) == 0)) {
                    
                    if (strcmp(curCommand.acSwitch,"ON") == 0) {
                        Serial.println("TURNING ON ac");
                        digitalWrite(AC_LR,HIGH);
                        strcpy(acStatusLivingRoom,"ON");
                    }else if (strcmp(curCommand.acSwitch,"OFF") == 0) {
                        Serial.println("TURNING OFF ac");
                        digitalWrite(AC_LR,LOW);
                        strcpy(acStatusLivingRoom,"OFF");
                    }

                }else {
                    Serial.println("AC-LR switch status has not change!");
                }

            }else {
                // Serial.println("AC-LR switch status is null!");
            }

            if (!(strcmp(curCommand.heatingSwitch,"nul") == 0)) {
                
                // turn ON/OFF ac on Living room
                Serial.print("HEATING-LR current switch status: ");
                Serial.println(heatingStatusLivingRoom);

                // Check if command's ac status is different from ac current status
                if (!(strcmp(curCommand.heatingSwitch,heatingStatusLivingRoom) == 0)) {
                    
                    if (strcmp(curCommand.heatingSwitch,"ON") == 0) {
                        Serial.println("TURNING ON HEATING");
                        digitalWrite(HEATING_LR,HIGH);
                        strcpy(heatingStatusLivingRoom,"ON");
                    }else if (strcmp(curCommand.heatingSwitch,"OFF") == 0) {
                        Serial.println("TURNING OFF heating");
                        digitalWrite(HEATING_LR,LOW);
                        strcpy(heatingStatusLivingRoom,"OFF");
                    }

                }else {
                    Serial.println("HEATING-LR switch status has not change!");
                }

            }else {
                // Serial.println("HEATING-LR switch status is null!");
            }
        
        // Command is about Kitchen
        }else if (strcmp(curCommand.room,"Kitchen") == 0) {
            // turn ON/OFF lamp on Kitchen
            Serial.print("Lamp-K current switch status: ");
            Serial.println(lampStatusKitchen);
            // Check if command device status is nul
            if (!(strcmp(curCommand.lampSwitch,"nul") == 0)) {
                // Check if command device status is different from device current status
                if (!(strcmp(curCommand.lampSwitch,lampStatusKitchen) == 0)) {
                    
                    if (strcmp(curCommand.lampSwitch,"ON") == 0) {
                        Serial.println("TURNING ON LAMP");
                        digitalWrite(LAMP_K,HIGH);
                        strcpy(lampStatusKitchen,"ON");
                    }else if (strcmp(curCommand.lampSwitch,"OFF") == 0) {
                        Serial.println("TURNING OFF LAMP");
                        digitalWrite(LAMP_K,LOW);
                        strcpy(lampStatusKitchen,"OFF");
                    }

                }else {
                    Serial.println("Lamp-K switch status has not change!");
                }

            }else {
                Serial.println("Lamp-K switch status is null!");
            }
        
        // Command is about Outside
        }else if (strcmp(curCommand.room,"Outside") == 0) {
            // turn ON/OFF lamp on Outside
            Serial.print("Lamp-O current switch status: ");
            Serial.println(lampStatusOutside);
            // Check if command device status is nul
            if (!(strcmp(curCommand.lampSwitch,"nul") == 0)) {
                // Check if command device status is different from device current status
                if (!(strcmp(curCommand.lampSwitch,lampStatusOutside) == 0)) {
                    
                    if (strcmp(curCommand.lampSwitch,"ON") == 0) {
                        Serial.println("TURNING ON LAMP");
                        digitalWrite(LAMP_O,HIGH);
                        strcpy(lampStatusOutside,"ON");
                    }else if (strcmp(curCommand.lampSwitch,"OFF") == 0) {
                        Serial.println("TURNING OFF LAMP");
                        digitalWrite(LAMP_O,LOW);
                        strcpy(lampStatusOutside,"OFF");
                    }

                }else {
                    Serial.println("Lamp-O switch status has not change!");
                }

            }else {
                Serial.println("Lamp-O switch status is null!");
            }
                
            // open/close tents on Outside
            Serial.print("tents current switch status: ");
            Serial.println(tentsStatusOutside);
            // Check if command device status is nul
            if (!(strcmp(curCommand.tentsSwitch,"nul") == 0)) {
                // Check if command device status is different from device current status
                if (!(strcmp(curCommand.tentsSwitch,tentsStatusOutside) == 0)) {
                    
                    if (strcmp(curCommand.tentsSwitch,"ON") == 0) {
                        Serial.println("Opening tents");
                        digitalWrite(TENTS,HIGH);
                        strcpy(tentsStatusOutside,"ON");
                    }else if (strcmp(curCommand.tentsSwitch,"OFF") == 0) {
                        Serial.println("Closing tents");
                        digitalWrite(TENTS,LOW);
                        strcpy(tentsStatusOutside,"OFF");
                    }

                }else {
                    Serial.println("tents switch status has not change!");
                }

            }else {
                Serial.println("tents switch status is null!");
            }

        }else {
            Serial.println("Different room from Living room,Kitchen or Outside...");
        }
      
    }
    // reset rcv
    rcv = "";
    return;
  }else {
    Serial.println("Connection failed while getting Last User Command...");
  }
}

CommandData setCurrentCommandObject(CommandData commandObj, StaticJsonDocument<200> jsonObj) {
    commandObj.id = jsonObj["id"];
    commandObj.room = jsonObj["room"];
    commandObj.lampSwitch = jsonObj["lampSwitch"];
    
    if (strcmp(commandObj.room,"LivingRoom") == 0) {
      commandObj.acSwitch = jsonObj["acSwitch"];
      commandObj.heatingSwitch = jsonObj["heatingSwitch"];
    }else if (strcmp(commandObj.room,"Outside") == 0) {
      commandObj.tentsSwitch = jsonObj["tentsSwitch"];
    }else {
      Serial.print("HERE: ");
      Serial.println(commandObj.room);
    }
    return commandObj;
}

HomeInfo getHomeInfo() {
  if (client.connect(server,80)) {
    Serial.println("Getting Home Mode and Desired Temperatures from Database...");

    client.print(String("GET ") + "/smartHome/getHomeInfo.php/" + " HTTP/1.1\r\n" + "Host: " + server + "\r\n" + "Connection: close\r\n\r\n"); //GET request for server response.
    unsigned long timeout = millis();
    while (client.available() == 0) {
      if (millis() - timeout > 5000) {  //If nothing is available on server for 25 seconds, close the connection.
        return;
      }
    }
    while(client.available()) {
      String line = client.readStringUntil('\r'); //Read the server response line by line and store it in rcv.
      rcv+=line; 
    }
    client.stop(); // Close the connection.

    // get mode and desired temperatures from server response
    int homeInfoStartIndex = rcv.indexOf("{");
    int homeInfoEndIndex = rcv.indexOf("}");
    String homeInfoStr = rcv.substring(homeInfoStartIndex,homeInfoEndIndex+1);
    
    // convert homeInfoStr to json document
    DeserializationError error = deserializeJson(doc,homeInfoStr);
    if (error) {
      Serial.print("deserializeJson() failed with code ");
      Serial.println(error.c_str());
    }

    const char* mode = doc["mode"];
    int desiredTempLivingRoom = atoi(doc["desiredTempLivingRoom"]);
    int desiredTempKitchen = atoi(doc["desiredTempKitchen"]);

    // reset rcv
    rcv = "";
    return {mode,desiredTempLivingRoom,desiredTempKitchen};
  }else {
    Serial.println("Connection failed while getting Home Mode and Desired Temperatures from Database...");
    return {"",-1,-1};
  }
}

void makeSmartDecisions() {
  
  // Temperature automation for Living Room
  temperatureAutomation("Living Room",homeInfo.mode,homeInfo.desiredTempLivingRoom,temperatureLivingRoom,motionDetectedLivingRoom,heatingStatusLivingRoom,HEATING_LR,acStatusLivingRoom,AC_LR);
  // Call the same function with different parameters for any other room that has the required sensors and devices

  // Lights automation for Living Room
  lightsAutomation("Living Room",lightLivingRoom,insideLightThreshold,motionDetectedLivingRoom,lampStatusLivingRoom,LAMP_LR);
  // Call the same function with different parameters for any other room that has the required sensors and devices

  // Rain Automation
  rainAutomation();

}

// Automations based on Temperature
void temperatureAutomation(char roomName[], const char* mode,int desiredTemp,int currentTemp,bool motionDetection,char heatingStatus[],int heatingPin,char acStatus[],int acPin) {
  bool powerSavingMode = false;
  if (strcmp(mode,"POWER-SAVING") == 0) {
    Serial.print("POWER-SAVING mode is enabled in ");
    Serial.println(roomName);
    powerSavingMode = true;
  }

  // Room is warmer than the desired Temperature
  if (currentTemp > desiredTemp) {
    Serial.print(roomName);
    Serial.println(" is warmer than desired");
    // Turn off Heating if it's ON
    if (strcmp(heatingStatus,"ON") == 0 || strcmp(heatingStatus,"NONE") == 0) {
      // turn off heating
      //
      Serial.print("Turnning off heating in ");
      Serial.println(roomName);
      digitalWrite(heatingPin,LOW);
      strcpy(heatingStatus,"OFF");
    }

    // On power-saving mode turn on ac only if there is someone in the room (motion detected)
    if (powerSavingMode) {
      // Motion detected
      if (motionDetection == HIGH) {
        // Turn on AC
        Serial.print("Motion detected in ");
        Serial.println(roomName);
        if (strcmp(acStatus,"OFF") == 0 || strcmp(acStatus,"NONE") == 0) {
          // turn on ac
          //
          Serial.print("Turnning on air condition in ");
          Serial.println(roomName);
          digitalWrite(acPin,HIGH);
          strcpy(acStatus,"ON");
        }
      }else {
        // No motion in the room
        if (strcmp(acStatus,"ON") == 0 || strcmp(acStatus,"NONE") == 0) {
          // turn off ac
          Serial.print("Turnning off air condition in ");
          Serial.println(roomName);
          digitalWrite(acPin,LOW);
          strcpy(acStatus,"OFF");
        }
      }
    }else {
      // Turn on AC
      if (strcmp(acStatus,"OFF") == 0 || strcmp(acStatus,"NONE") == 0) {
        // turn on ac
        //
        Serial.print("Turnning on air condition in ");
        Serial.println(roomName);
        digitalWrite(acPin,HIGH);
        strcpy(acStatus,"ON");
      }
    }
  
  // Room is colder than the desired Temperature
  }else if (currentTemp < desiredTemp) {
    Serial.print(roomName);
    Serial.println(" is colder than desired");
    // Turn off AC if it's ON
    if (strcmp(acStatus,"ON") == 0 || strcmp(acStatus,"NONE") == 0) {
      // turn off ac
      //
      Serial.print("Turnning off air condition in ");
      Serial.println(roomName);
      digitalWrite(acPin,LOW);
      strcpy(acStatus,"OFF");
    }

    // On power-saving mode turn on heating only if there is someone in the room (motion detected)
    if (powerSavingMode) {
      // Motion detected
      if (motionDetection == HIGH) {
        Serial.print("Motion detected in ");
        Serial.println(roomName);
        // Turn on Heating
        if (strcmp(heatingStatus,"OFF") == 0 || strcmp(heatingStatus,"NONE") == 0) {
          // turn on led
          //
          Serial.print("Turnning on heating in ");
          Serial.println(roomName);
          digitalWrite(heatingPin,HIGH);
          strcpy(heatingStatus,"ON");
        }
      }else {
        // No motion in the room
        if (strcmp(heatingStatus,"ON") == 0 || strcmp(heatingStatus,"NONE") == 0) {
          // turn off Heating
          //
          Serial.print("Turnning off heating in ");
          Serial.println(roomName);
          digitalWrite(heatingPin,LOW);
          strcpy(heatingStatus,"OFF");
        }
      }
    }else {
      // Turn on Heating
      if (strcmp(heatingStatus,"OFF") == 0 || strcmp(heatingStatus,"NONE") == 0) {
        // turn on led
        //
        Serial.print("Turnning on heating in ");
        Serial.println(roomName);
        digitalWrite(heatingPin,HIGH);
        strcpy(heatingStatus,"ON");
      }
    }
  // Room has the desired temperature
  }else {
    Serial.print(roomName);
    Serial.print(" has the desired temperature: ");
    Serial.println(currentTemp);
  }
  
}

// Automations based on Light
void lightsAutomation(char roomName[],int curLight,int lightThreshold,bool motionDetection,char lampCurStatus[],int lampPin) {

  // Chech if there is someone in the room
  if (motionDetection == HIGH) {

    // Room is darker than the desired 
    if (curLight < lightThreshold) {
      Serial.print(roomName);
      Serial.println(" is darker than desired");

      // Lamp is off
      if (strcmp(lampCurStatus,"OFF") == 0 || strcmp(lampCurStatus,"NONE") == 0) {
        // turn on lamp
        Serial.print("Turnning on Lamp in ");
        Serial.println(roomName);
        digitalWrite(lampPin,HIGH);
        strcpy(lampCurStatus,"ON");
      }

    // There is enough light in the room
    }else if (curLight > lightThreshold) {
      Serial.print(" There is enough light in ");
      Serial.println(roomName);

    }else {
      Serial.print(roomName);
      Serial.print(" has the desired light: ");
      Serial.println(lightThreshold);
    }
  }else {
    Serial.print("No motion was detected in ");
    Serial.println(roomName);

    // Lamp is on
    if (strcmp(lampCurStatus,"ON") == 0 || strcmp(lampCurStatus,"NONE") == 0) {
      // turn off lamp
      Serial.print("Turnning off Lamp in ");
      Serial.println(roomName);
      digitalWrite(lampPin,LOW);
      strcpy(lampCurStatus,"OFF");
    }
  }
}

// Automations based on rain
void rainAutomation() {

  if (rainOutside) {
    // draw down tents if it's raining and they are pulled up
    Serial.println("It is raining outside!");
    if (strcmp(tentsStatusOutside,"OFF") == 0 || strcmp(tentsStatusOutside,"NONE") == 0) {
      Serial.println("Pulling down tents...");
      digitalWrite(TENTS,HIGH);
      strcpy(tentsStatusOutside,"ON");
    }else {
      Serial.println("Tents are already pulled down.");
    }

  }else {
    // pull up tents if it's not raining and they are drown down
    Serial.println("Not raining.");
    if (strcmp(tentsStatusOutside,"ON") == 0 || strcmp(tentsStatusOutside,"NONE") == 0) {
      Serial.println("Pulling up tents...");
      digitalWrite(TENTS,LOW);
      strcpy(tentsStatusOutside,"OFF");
    }else {
      Serial.println("Tents are already pulled up.");
    }
    
  }

}

// Alarms about fire and/or gas leakage in the Kitchen
void kitchenAlarms() { 
  kitchenStatus previousStatus = kitchenCurrStatus;
  char warningMessage[70];

  if (flameInKitchen && gasInKitchen) {
    if (kitchenCurrStatus == FIRE_AND_GAS) {
      Serial.println("There is still fire and gas leakage in the Kitchen.");
      return;
    }else {
      Serial.println("Fire and Gas Leakage were detected in the Kitchen!");
      strcpy(warningMessage,"Alarm!%20Fire%20and%20Gas%20Leakage%20were%20detected%20in%20the%20Kitchen.");
      kitchenCurrStatus = FIRE_AND_GAS;
    }
  }else if (flameInKitchen) {
    if (kitchenCurrStatus == FIRE) {
      Serial.println("There is still Fire in the Kitchen.");
      return;
    }else {
      Serial.println("Fire was detected in the Kitchen!");
      strcpy(warningMessage,"Alarm!%20Fire%20was%20detected%20in%20the%20Kitchen.");
      kitchenCurrStatus = FIRE;
    }
  } else if (gasInKitchen) {
    if (kitchenCurrStatus == GAS) {
      Serial.println("There is still Gas leakage in the Kitchen.");
      return;
    }else {
      Serial.println("Gas Leakage was detected in the Kitchen!");
      strcpy(warningMessage,"Alarm!%20Gas%20Leakage%20was%20detected%20in%20the%20Kitchen.");
      kitchenCurrStatus = GAS;
    }
  }else {
    if (kitchenCurrStatus == SAFE) {
      Serial.println("Kitchen is safe.");
      return;
    }else {
      Serial.println("Kitchen is Safe again!");
      strcpy(warningMessage,"Kitchen%20is%20Safe%20again%20:)");
      kitchenCurrStatus = SAFE;
    }
  }

  // inform home owner about kitchen status change 
  Serial.print("Kitchen status changed from ");
  Serial.print(getKitchenStatusName(previousStatus));
  Serial.print(" to ");
  Serial.println(getKitchenStatusName(kitchenCurrStatus));
  
  Serial.println("Informing home owner...");
  sendWarningMail(warningMessage);
}
  
void homeInvasionAlarm() {
  if (doorIsOpen) {
    Serial.println("Alarm! Someone broke into the house!");
    char warning[] = "Alarm!%20Someone%20broke%20into%20the%20house!";
    // inform home owner with email
    sendWarningMail(warning);
  }
}

// Returns light from LDR sensor
int getLightDetection(int pin) {
  int lightDetection = analogRead(pin);
  // int newVal = map(lightdetection,0,700,0,100);  // from smartHome bookmark
  //  lightDetection = constrain(lightDetection, 500,600); 
  //  int ledLevel = map(lightDetection, 500,600,255, 0);
  return lightDetection;
}

// Returns true if motion was detected. Else false
bool motionDetected(int pin) {
  int val = digitalRead(pin);
  if (val == HIGH) {
    return true;
  }else {
    return false;
  }
}

// Returns true if flame was detected. Else false
bool flameDetected(int pin) {
  int flameDetection = digitalRead(pin);
  if (flameDetection == HIGH) {
    return true;
  }else {
    return false;
  }
}

// Returns true if gas was detected. Else false
bool gasDetected(int pin) {
  int gasDetection = analogRead(pin);
  Serial.print("Gas analog val: ");
  Serial.println(gasDetection);
  // Checks if it has reached the threshold value
  if (gasDetection > gasThreshold){
    return true;
  }else{
    return false;
  }
}

// Returns true if rain was detected. Else false
bool rainingOutside(int pin) {
   int moistureVal = analogRead(pin);
  if (moistureVal < 800 && moistureVal > rainThreshold) {
    Serial.println("Warning for Rain");
    return true;
  }else if (moistureVal < rainThreshold) {
    Serial.println("Raining!");
    return true;
  }else {
    return false;
  }
}

bool magneticReedisOpen(int pin) {
  int state = digitalRead(pin);
  if (state == HIGH) {
    return true;
  }else {
    return false;
  }
}

// function that executed a simulation senario
void runSenario(senarioTypes senario) {
  // create fake sensorings inside the normal limits for the whole house
  setNormalSensorings();

  switch (senario)
  {
  case FIRE_SENARIO:
    Serial.print("Executing senario: ");
    Serial.println(getSenarioName(senario));
    flameInKitchen = true;
    gasInKitchen = false;
    break;
  case GAS_SENARIO:
    Serial.print("Executing senario: ");
    Serial.println(getSenarioName(senario));
    flameInKitchen = false;
    gasInKitchen = true;
    break;
  case FIRE_AND_GAS_SENARIO:
    Serial.print("Executing senario: ");
    Serial.println(getSenarioName(senario));
    flameInKitchen = true;
    gasInKitchen = true;
    break;
  case HIGH_TEMP:
    Serial.print("Executing senario: ");
    Serial.println(getSenarioName(senario));
    homeInfo.desiredTempLivingRoom = 20;
    temperatureLivingRoom = 22;
    strcpy(acStatusLivingRoom,"OFF");
    strcpy(heatingStatusLivingRoom,"ON");
    break;
  case LOW_TEMP:
    Serial.print("Executing senario: ");
    Serial.println(getSenarioName(senario));
    
    homeInfo.desiredTempLivingRoom = 20;
    temperatureLivingRoom = 18;
    strcpy(acStatusLivingRoom,"ON");
    strcpy(heatingStatusLivingRoom,"OFF");
    break;
  case LIGHTS:
    Serial.print("Executing senario: ");
    Serial.println(getSenarioName(senario)); 
    lightLivingRoom = 400;
    motionDetectedLivingRoom = true;
    strcpy(lampStatusLivingRoom,"OFF");
    break;
  case ROBBERY:
    Serial.print("Executing senario: ");
    Serial.println(getSenarioName(senario));
    doorIsOpen = true;
    break;
  case RAIN:
    Serial.print("Executing senario: ");
    Serial.println(getSenarioName(senario));
    rainOutside = true;
    // strcpy(tentsStatus,"OFF");
    break;
  default:
    Serial.print(senario);
    Serial.println(" is not a valid senario!");
    break;
  }

  // homeInvasionAlarm();
  kitchenAlarms();
  makeSmartDecisions();
}


// set fake values in order to not affect the executed senario
void setNormalSensorings() {
  // Living Room Variables
  // sensorings
  temperatureLivingRoom = homeInfo.desiredTempLivingRoom = 20;
  humidityLivingRoom = 10;
  lightLivingRoom = insideLightThreshold;
  motionDetectedLivingRoom = false;
  doorIsOpen = false;      // used in alarm operation
  // devices statuses
  strcmp(heatingStatusLivingRoom,"OFF");
  strcmp(acStatusLivingRoom,"OFF");
  strcmp(lampStatusLivingRoom,"OFF");


  // Kitchen Variables
  temperatureKitchen = homeInfo.desiredTempKitchen = 20;
  humidityKitchen = 10;
  lightKitchen = insideLightThreshold;
  gasInKitchen = false;
  flameInKitchen = false;
  // devices statuses
  strcmp(lampStatusKitchen,"OFF");
  // kitchen status (used in fire and gas alarm)
  kitchenCurrStatus = SAFE;

  // Outside Variables
  humidityOutside = 10;
  temperatureOutside = 20;
  lightOutside = outsideLightThreshold;
  rainOutside = false;
  strcmp(lampStatusOutside,"OFF");
  strcmp(tentsStatusOutside,"OFF");  // ON=open , OFF=closed

  return;
}

const char* getSenarioName(enum senarioTypes senario) 
{
   switch (senario) 
   {
      case NONE: return "NONE";
      case FIRE_SENARIO: return "FIRE";
      case GAS_SENARIO: return "GAS";
      case FIRE_AND_GAS_SENARIO: return "FIRE_AND_GAS";
      case HIGH_TEMP: return "HIGH_TEMP";
      case LOW_TEMP: return "LOW_TEMP";
      case LIGHTS: return "LIGHTS";
      case ROBBERY: return "ROBBERY";
      case RAIN: return "RAIN";
   }
}

const char* getKitchenStatusName(enum kitchenStatus status) 
{
   switch (status) 
   {
      case SAFE: return "SAFE";
      case FIRE: return "FIRE";
      case GAS: return "GAS";
      case FIRE_AND_GAS: return "FIRE_AND_GAS";
   }
}

void sendWarningMail(char msg[]) {
  if (client.connect(server,80)) {
    Serial.println("Sending Mail to home owner...");
    client.print("GET /smartHome/sendWarning.php?"); 
    client.print("warningMsg=");
    client.println(msg);
    client.println(" HTTP/1.1"); 
    client.print("Host: "); 
    client.println(server);
    client.println("Connection: close"); 
    client.println(); 
    client.println(); 
    client.stop();
  }else {
    Serial.println("Connection failed while sending warning email to home owner...");
  }
}
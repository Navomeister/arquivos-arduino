// https://api.github.com/
#include <SPI.h>
#include <arduino-timer.h>
#include <Ethernet.h>
#include <ArduinoUniqueID.h>

byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
char server[] = "http://10.105.72.132/";    
IPAddress ip(192, 168, 0, 177);
IPAddress myDns(192, 168, 0, 1);
EthernetClient client;

unsigned long beginMicros, endMicros;
unsigned long byteCount = 0;
bool printWebData = true; 

void setup(){
  Serial.begin(9600);

  if (Ethernet.begin(mac) == 0) {
    Serial.println("Failed to configure Ethernet using DHCP");
    // Check for Ethernet hardware present
    if (Ethernet.hardwareStatus() == EthernetNoHardware) {
      Serial.println("Ethernet shield was not found.  Sorry, can't run without hardware. :(");
      while (true) {
        delay(1); // do nothing, no point running without Ethernet hardware
      }
    }
    if (Ethernet.linkStatus() == LinkOFF) {
      Serial.println("Ethernet cable is not connected.");
    }
    // try to congifure using IP address instead of DHCP:
    Ethernet.begin(mac, ip, myDns);
  } else {
    Serial.print("  DHCP assigned IP ");
    Serial.println(Ethernet.localIP());
  }


  // if you get a connection, report back via serial:
  if (client.connect(server, 80)) {
    // Make a HTTP request:
    client.println("GET /api-arduino/api.php?usuario=123&endpoint=salas HTTP/1.0");
    client.println("Host: http://10.105.72.132");
    client.println("Connection: close");
    client.println();
  } else {
    // if you didn't get a connection to the server:
    Serial.println("connection failed");
  }
  beginMicros = micros();

}

void loop(){
  int len = client.available();

    byte buffer[80];
    if (len > 80) len = 80;
    client.read(buffer, len);
  
      Serial.write(buffer, len); // show in the serial monitor (slows some boards)
    

  // if the server's disconnected, stop the client:
  if (!client.connected()) {
    endMicros = micros();

    client.stop();


    while (true) {
      delay(1);
    }
  }
}
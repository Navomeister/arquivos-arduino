#include <SPI.h>
#include <Ethernet.h>

byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress server(10,105,75,2); // ip do servidor da API
// 20,201,28,148

IPAddress ip(10,105,72,177); // ip fixo para caso não conecte por DHCP

EthernetClient client;

void setup() {
 Serial.begin(9600);
  while (!Serial) {
   ; // wait for serial port to connect. Needed for Leonardo only
 }

 // start the Ethernet connection:
 if (Ethernet.begin(mac) == 0) {
   Serial.println("Failed to configure Ethernet using DHCP");
   // no point in carrying on, so do nothing forevermore:
   // try to congifure using IP address instead of DHCP:
   Ethernet.begin(mac, ip);
 }
 // give the Ethernet shield a second to initialize:
 delay(1000);
 Serial.println("connecting...");
//  Serial.println("https://api.github.com/");
 Serial.println("http://10.105.75.2/api-arduino/api.php?usuario=123&endpoint=salas");
 Serial.println("QWRtaW46MDEyMzQ1");

  // if you get a connection, report back via serial:
  if (client.connect(server, 80)) { // 80=http 443=https
    // Make a HTTP request:
    client.println("GET /apiArduino/api.php?usuario=123&endpoint=salas HTTP/1.0");
    client.println("Host: 10.105.75.2");
    client.println("Connection: close");
    client.println();
  } else {
    // if you didn't get a connection to the server:
    Serial.println("connection failed");
  }
}

void loop()
{
 // if there are incoming bytes available 
 // from the server, read them and print them:
 if (client.available()) {
   char c = client.read();
   Serial.print(c);
 }

 // if the server's disconnected, stop the client:
 if (!client.connected()) {
   Serial.println();
   Serial.println("disconnecting.");
   client.stop();

   // do nothing forevermore:
   while(true);
 }
}

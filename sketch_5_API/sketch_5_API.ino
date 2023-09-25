#include <ESP8266WiFi.h>
#include <WiFiClientSecure.h>
#include <arduino-timer.h>

#ifndef STASSID
#define STASSID "AAPM"
#define STAPSK "alunosenai"
#endif

// dados do wifi
const char* ssid = STASSID;
const char* password = STAPSK;

// id do nodemcu
const uint32_t id = ESP.getChipId();

// host da API
String host = "apenasumtestezinho.000webhostapp.com";

// Pinos que está conectado o sensor
#define echoPin 5
#define trigPin 6
float distancia;

// timer para confirmação de atividade
auto ativo = timer_create_default();


void setup() {
  // conexão
  Serial.begin(115200);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());

  // String url = "/api/api.php?endpoint=salas&usuario=1234";
  request("/api/api.php?endpoint=cadastro&usuario=" + id);

  // seta input e output do sensor
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  // seta a função para confirmar atividade para cada 30 minutos
  ativo.every(1800000, triggSala);
}

void loop() {
  // passa o tempo do timer
  ativo.tick(); 

  // checa distancia do sensor ultrassonico
  digitalWrite(trigPin, LOW);
  delay(0005);
  digitalWrite(trigPin, HIGH);
  delay(0010);
  digitalWrite(trigPin, LOW);

  // calcula a distância
  distancia = pulseIn(echoPin, HIGH);
  distancia = distancia / 58;

  // caso detecte menos de 10 cm de distancia, roda o request de sala
  if (distancia < 10){
    request("/api/api.php?endpoint=salas&usuario=" + id);
    delay(60000);
  }

}

bool triggSala(void *){
  request("/api/api.php?endpoint=ativo&usuario=" + id);
  return true;
}

void request(String url){
  // Use WiFiClientSecure class to create TLS connection
  WiFiClientSecure client;
  Serial.print("Connecting to ");
  Serial.println(host);

  // setInsecure() para não usar certificado no request
  client.setInsecure();

  if (!client.connect(host, 443)) {
    Serial.println("Connection failed");
    return;
  }

  Serial.print("Requesting URL: ");
  Serial.println(url);

  client.print(String("GET ") + url + " HTTP/1.1\r\n" + "Host: " + host + "\r\n" + "Connection: close\r\n\r\n");

  Serial.println("Request sent");
  while (client.available()) {
    String line = client.readStringUntil('\n');
    if (line == "\r") {
      Serial.println("Headers received");
      break;
    }
  }
  String line = client.readStringUntil('\n');
  Serial.println("Reply was:");
  Serial.println("==========");
  Serial.println(line);
  Serial.println("==========");
  Serial.println("Closing connection");
}
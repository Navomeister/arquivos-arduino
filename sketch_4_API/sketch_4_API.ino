#include <Ethernet.h>
#include <SPI.h>

#include <Fetch.h>
#include <arduino-timer.h>
// #include <WiFiClient.h>
// #include <ESP8266WiFi.h>
// #include <WiFiClientSecure.h>
#include <ArduinoUniqueID.h>

// timer para confirmação de atividade
auto ativo = timer_create_default();

String id;

// informações da rede
char ssid[] = "AAPM";
char password[] = "";

// cria o cliente HTTP
// WiFiClient client;

// the media access control (ethernet hardware) address for the shield:
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };  
//the IP address for the shield:
byte ip[] = { 10, 0, 0, 177 };    

// cliente Fetch
FetchClient clientF;

// Pinos que está conectado o sensor
#define echoPin 5
#define trigPin 6
float distancia;


void setup() {
  // put your setup code here, to run once:

  Ethernet.begin(mac);

  // pega ID do arduino
  for (size_t i = 0; i < UniqueIDsize; i++)
	{
		if (UniqueID[i] < 0x10)
			id += String("0");
		id += String(UniqueID[i], HEX);
	}

  // seta input e output do sensor
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  Serial.begin(115200);
  Serial.println(id);

  // // configuração p/ conectar?
  // WiFi.mode(WIFI_STA);
  // WiFi.disconnect();
  // delay(100);

  // // tenta conectar à rede
  // conectar();

  // // faz o request para cadastrar
  // request("cadastro");

  // seta a função para confirmar atividade para cada 30 minutos
  ativo.every(1800000, triggSala);
  

}


void loop() {
  // put your main code here, to run repeatedly:

  // checa conexão e tenta reconectar
  // if(WiFi.status() != WL_CONNECTED){
  //   conectar();
  // }

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
    request("salas");
    delay(60000);
  }
}

// void conectar(){
//   Serial.print("Conectando à: ");
//   Serial.println(ssid);
//   WiFi.begin(ssid, password);
//   while (WiFi.status() != WL_CONNECTED){
//     Serial.print(".");
//     delay(500);
//   }
//   Serial.println("");
//   Serial.println("Conectado");
//   Serial.println("Endereço de IP: ");
//   IPAddress ip = WiFi.localIP();
//   Serial.println(ip);
// }


// função ativada por tempo para confirmar a atividade
bool triggSala(void *){
  request("ativo");
  return true;
}


// função padrão para requests
void request(String endpoint){
  RequestOptions options;
  options.method = "GET";

  String urlAPI = String("");

  clientF = fetch("https://api.github.com/", options, handleResponse);
  // client = fetch("{url}?endpoint="+ endpoint +"&usuario="+ id, options, handleResponse);
}

// função para fazer algo com a resposta
void handleResponse(Response response) {
    Serial.println("Response received:");
    // Printing response body as plain text.
    Serial.println();
    Serial.println(response.text());
}

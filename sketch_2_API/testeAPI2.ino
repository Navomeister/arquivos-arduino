#include <arduino-timer.h>
#include <WiFiClient.h>
#include <ESP8266WiFi.h>
#include <WiFiClientSecure.h>
#include <ArduinoUniqueID.h>

String id;

// timer para confirmação de atividade
auto ativo = timer_create_default();


// informações da rede
char ssid[] = "AAPM";
char password[] = "";

// cria o cliente HTTP
WiFiClient client;

// URL da API
#define HOST "10.105.75.2"
#define echoPin 5
#define trigPin 6

float distancia;

void setup() {
  // put your setup code here, to run once:
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  Serial.begin(115200);

  // configuração p/ conectar?
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
  delay(100);

  // tenta conectar à rede
  Serial.print("Conectando à: ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED){
    Serial.print(".");
    delay(500);
  }
  Serial.println("");
  Serial.println("Conectado");
  Serial.println("Endereço de IP: ");
  IPAddress ip = WiFi.localIP();
  Serial.println(ip);

  // seta o cliente para HTTP
  // client.setInsecure();

  // faz o request para cadastrar
  request("cadastro");

  // seta a função para confirmar atividade para cada 30 minutos
  ativo.every(1800000, triggSala);

}

void loop() {
  // put your main code here, to run repeatedly:

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

bool triggSala(void *){
  request("ativo");
  return true;
}

void request(String endpoint){

  // pega ID do arduino
  for (size_t i = 0; i < UniqueIDsize; i++)
	{
		if (UniqueID[i] < 0x10)
			id += String("0");
		id += String(UniqueID[i], HEX);
	}

  // Abre conexão ao servidor (porta 80 para HTTP)
  if (!client.connect(HOST, 80)){
    Serial.println(F("Falha na conexão"));
    return;
  }

  // dá uma pausa pro ESP
  yield();

  // manda o request HTTP
  client.print(F("GET "));
  // Segunda parte do request (endpoint e variáveis)
  client.print("/api-arduino/api.php?endpoint="+ endpoint +"&usuario=" + id);

  // pede a resposta em HTTP 1.0
  // Tratamento para ambos caso haja resposta em 1.1
  client.println(F(" HTTP/1.0"));

  // Headers
  client.print(F("Host: "));
  client.println(HOST);

  client.println(F("Cache-Control: no-cache"));

  if(client.println() == 0){
    Serial.println(F("Failed to send request"));
    return;
  }

  // checa o status do pedido
  char status[32] = {0};
  client.readBytesUntil('\r', status, sizeof(status));

  // checa se respondeu "OK" (200) em 1.0 ou 1.1
  if(strcmp(status, "HTTP/1.0 200 OK") != 0 && strcmp(status, "HTTP/1.1 200 OK") != 0){
    {
      Serial.print(F("Resposta inesperada: "));
      Serial.println(status);
      return;
    }
  }

  // pula os headers HTTP?
  char endOfHeaders[] = "\r\n\r\n";
  if(!client.find(endOfHeaders)){
    Serial.println(F("Resposta inválida."));
    return;
  }

  // Para APIs que respondem com HTTP/1.1 é preciso remover
  // alguma coisa no início do body não entendi direito
  // 
  // peek() vai olhar para o caractere, mas não tirá-lo do queue
  while(client.available() && client.peek() != '{' && client.peek() != '['){
    char c = 0;
    client.readBytes(&c, 1);
    Serial.print(c);
    Serial.println("BAD");
  }

  // Enquanto o cliente ainda é acessível, ler cada byte
  // e printar no monitor
  while(client.available()){
    char c = 0;
    client.readBytes(&c, 1);
    Serial.print(c);
  }
}

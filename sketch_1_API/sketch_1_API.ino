#include <WiFi.h>
#include <Fetch.h>

// dados conexão WiFi
char ssid[] = "AAPM";         // nome da rede
int status = WL_IDLE_STATUS;  // o status da conexão (acho)
const idArduino = 'algo';
const numSala = 4; //**INSIRA O NÚMERO DA SALA AQUI**

// opções fetch
/**
    {
        method: "GET" || "POST" || "HEAD" || "PUT" || "DELETE",
        headers: { "Content-Type": "application/x-www-form-urlencoded", "Content-Length": Automatic, "Host: FromURL, "User-Agent": "arduino-fetch", "Cookie": "", "Accept": "* /*", "Connection": "close", "Transfer-Encoding": "chunked" },
        body: "",
        redirect: "follow" || "manual", "error",
        follow: Integer,

    }
*/

ResponseOptions options;
options.method = "GET";
options.headers["Content-Type"] = "application/json";
options.headers["Connection"] = "keep-alive";
// options.body = "usuario=". idArduino ."&numero_sala=". numSala;

const int echo = 5;  //PINO DIGITAL UTILIZADO PELO HC-SR04 ECHO(RECEBE)
const int trig = 6;  //PINO DIGITAL UTILIZADO PELO HC-SR04 TRIG(ENVIA)
int led = 4;
int buzzer = 2;

float distancia;


void setup() {
  pinMode(trig, OUTPUT);
  pinMode(echo, INPUT);
  pinMode(led, OUTPUT);
  Serial.begin(9600);

  // conecta
  Serial.begin(9600);
  while (!Serial) {
    ;
  }
  // checa presença do shield
  if (WiFi.status() == WL_NO_SHIELD) {

    Serial.println("WiFi shield not present");

    // não continua

    while (true)
      ;
  }

  while (status != WL_CONNECTED) {
    Serial.print("Tentando se conectar à rede ");
    Serial.print(ssid);
    status = WiFi.begin(ssid);

    // espera 10 segundos pra tentar de novo
    delay(10000);
  }

  Serial.print("Conectado com sucesso.");
  Serial.print("SSID: ");
  Serial.println(WiFi.SSID());
}

void loop() {
  // checa conexão a cada loop
  if (!WiFi.SSID()) {
    while (!WiFi.SSID()) {
      Serial.print("Erro de Conexão.");
      Serial.print("Reconectando...");
      status = WiFi.begin(ssid);
      delay(10000);
    }
    serial.Print("Conexão reestabelecida.");
  }
  Serial.print("SSID: ");
  Serial.println(WiFi.SSID());

  // request
  // Response response = fetch('http://localhost/api-arduino/api.php/?endpoint=salas&usuario=' + idArduino + '&numero_sala=' + numSala, options);
  Response response = fetch("https://api.github.com/users/xiaotian/repos", options);
  Serial.println(response);

  digitalWrite(trig, LOW);
  delay(0005);
  digitalWrite(trig, HIGH);
  delay(0010);
  digitalWrite(trig, LOW);

  distancia = pulseIn(echo, HIGH);
  distancia = distancia / 58;
  Serial.println(distancia);
  if (distancia < 50) {
    pisca();
  }
  delay(2000);
}

void pisca() {
  digitalWrite(led, HIGH);
  delay(500);  // Wait for 500 millisecond(s)
  Serial.println("muito perto");
  digitalWrite(led, LOW);
  delay(500);  // Wait for 500 millisecond(s)
}
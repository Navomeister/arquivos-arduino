
const int echo = 5; //PINO DIGITAL UTILIZADO PELO HC-SR04 ECHO(RECEBE)
const int trig = 6; //PINO DIGITAL UTILIZADO PELO HC-SR04 TRIG(ENVIA)
int led = 13;
// int buzzer = 2;

float distancia;  


void setup()  
{
  pinMode (trig, OUTPUT);
  pinMode (echo,INPUT);
  pinMode (led, OUTPUT);
  // pinMode (buzzer, OUTPUT);
  Serial.begin (9600);
}

void loop()
{
  digitalWrite(trig, LOW);
  delay(0005);
  digitalWrite(trig, HIGH);
  delay(0010);
  digitalWrite(trig, LOW);
  
  distancia = pulseIn (echo, HIGH);
  distancia = distancia/58;
    Serial.println (distancia);
  if (distancia < 50){
    pisca();
  }
  delay(2000);
}

void pisca(){
  digitalWrite(led, HIGH);
  // tone(buzzer, 440, 500);
  Serial.println ("muito perto");
  delay(500); // Wait for 500 millisecond(s)
  digitalWrite(led, LOW);
  delay(500); // Wait for 500 millisecond(s)
}
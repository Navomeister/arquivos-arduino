#include <ArduinoUniqueID.h>

  String id;

void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600);
  for (size_t i = 0; i < UniqueIDsize; i++)
	{
		if (UniqueID[i] < 0x10)
			id += String("0");
		id += String(UniqueID[i], HEX);
	}
}

void loop() {
  // put your main code here, to run repeatedly:
  Serial.println(id);
  Serial.println("UniqueID: ");
  for (size_t i = 0; i < UniqueIDsize; i++)
	{
		if (UniqueID[i] < 0x10)
			Serial.print("0");
		Serial.print(UniqueID[i], HEX);
		Serial.print(" ");
	}
  delay(9999999999);
}

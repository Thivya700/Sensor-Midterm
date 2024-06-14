#include <ESP8266WiFi.h>      
#include <WiFiClient.h>       
#include <ESP8266HTTPClient.h> 
#include <DHT.h>              

#define DHTPIN D2      // GPIO pin connected to DHT sensor
#define DHTTYPE DHT22  // DHT type (DHT11 or DHT22)
#define ldr_pin A0     // Define the LDR pin (analog pin)

const char* ssid = "Yahweh";         // WiFi SSID
const char* password = "77770000";   // WiFi password
const char* serverUrl = "http://192.168.62.28/data_handler.php"; // Server URL for handling data

DHT dht(DHTPIN, DHTTYPE); // Create a DHT object

void setup() {
  Serial.begin(115200); // Start serial communication for debugging
  WiFi.begin(ssid, password); // Connect to WiFi network

  // Wait until connected to WiFi
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }

  Serial.println("Connected to WiFi");
  dht.begin(); // Initialize the DHT sensor
}

void loop() {
  float temperature = dht.readTemperature(); // Read temperature from DHT sensor
  int ldrValue = analogRead(ldr_pin);  // Read the analog value from the LDR
  float humidity = dht.readHumidity();   // Read humidity from DHT sensor

  // Check if sensor readings are valid
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }

  sendDataToServer(temperature, humidity, ldrValue); // Send sensor data to server
  delay(10000); // Delay for 10 seconds before next reading
}

void sendDataToServer(float temperature, float humidity, int light) {
  if (WiFi.status() == WL_CONNECTED) { // Check if WiFi is connected
    WiFiClient client; // Create a WiFi client object
    HTTPClient http;   // Create an HTTP client object

    Serial.print("Connecting to server: ");
    Serial.println(serverUrl);

    http.begin(client, serverUrl); // Begin HTTP connection to server
    http.addHeader("Content-Type", "application/x-www-form-urlencoded"); // Set content type for HTTP POST

    // Format data to send as POST request
    String postData = "temperature=" + String(temperature) +
                      "&humidity=" + String(humidity) +
                      "&light=" + String(light);

    // Send POST request with formatted data
    int httpResponseCode = http.POST(postData);

    // Check HTTP response code
    if (httpResponseCode > 0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      String response = http.getString(); // Get response from server
      Serial.println("Server response:");
      Serial.println(response); // Print server response to Serial Monitor
    } else {
      Serial.print("Error occurred while sending HTTP POST: ");
      Serial.println(httpResponseCode);
    }

    http.end(); // Close HTTP connection
  } else {
    Serial.println("WiFi not connected!");
  }
}

#include <WiFi.h>
#include <HTTPClient.h>

String server = "http://your-server.com/hardware_api.php";
int tenantID = 1;

void loop() {
  if ((WiFi.status() == WL_CONNECTED)) {
    HTTPClient http;
    http.begin(server + "?tenant_id=" + String(tenantID));
    int httpCode = http.GET();

    if (httpCode == 200) {
      String payload = http.getString();
      DynamicJsonDocument doc(1024);
      deserializeJson(doc, payload);
      float balance = doc["balance"];
      
      if (balance <= 0) {
        digitalWrite(RELAY_PIN, LOW); // Cut power
        sendSMS("Tenant " + String(tenantID) + " power disconnected.");
        updatePowerStatus(0);
      } else {
        digitalWrite(RELAY_PIN, HIGH); // Ensure power is ON
        updatePowerStatus(1);
      }
    }

    http.end();
  }

  delay(30000); // Check every 30 seconds
}

void updatePowerStatus(int status) {
  HTTPClient http;
  http.begin(server);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  String postData = "tenant_id=" + String(tenantID) + "&status=" + String(status);
  http.POST(postData);
  http.end();
}

void sendSMS(String message) {
  Serial.println("Sending SMS: " + message);
  // Send AT commands to GSM module here...
}

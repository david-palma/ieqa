/**
 * Copyright (c) 2019 David Palma licensed under the MIT license
 * Title: IEQA - Indoor Environmental Quality Analyzer
 * Author:  David Palma
 */

 /*
  * The circuit (Vcc = 5V):
  * Note: with Wire.h SDA=pin 2, SCL=pin 3
  *       I2C devices: TSL2561 (0x39), HTU21DF (0x40)
  * - TSL2561 SDA pin to digital pin SDA
  * - TSL2561 SCL pin to digital pin SCL
  * - HTU21DF SDA pin to digital pin SDA
  * - HTU21DF SCL pin to digital pin SCL
  * - ACTUATOR to digital pin 6
  * - LCD
  *    1. Vss (GND)
  *    2. Vdd (5V)
  *    3. Vo (contrast adjustment)
  *    4. RS pin to digital pin 7
  *    5. GND (write mode)
  *    6. Enable pin to digital pin 8
  *   11. D4 pin to digital pin 9
  *   12. D5 pin to digital pin 10
  *   13. D6 pin to digital pin 11
  *   14. D7 pin to digital pin 12
  *   15. Vdd (Vcc)
  *   16. GND
  */

#include <avr/pgmspace.h>  // Used to store constants into program memory (flash)
#include <Bridge.h>        // Simplified communication between ATmega32U4 and AR9331
#include <Console.h>       // Communication with the network monitor
#include <FileIO.h>        // Read/write files on the microSD card
#include <HttpClient.h>    // HTTP client on Linux
#include <Mailbox.h>       // Communication between Linux and Arduino
#include <Process.h>       // Launch processes on the Linux processor
#include <string.h>
#include <YunClient.h>     // Arduino based HTTP client
#include <YunServer.h>     // Arduino based HTTP server
#include <Wire.h>          // Communication with I2C devices

  /** Libraries */
#include "TSL2561.h"       // Luminosity sensor
#include "HTU21DF.h"       // Humidity and temperature sensor
#include <LiquidCrystal.h> // LCD 16x4 compatible with Hitachi HD44780 driver

/** Constants */
const short LCD_WIDTH  PROGMEM = 16;   // Maximum characters per line
const short LCD_HEIGHT PROGMEM =  4;   // Maximum height
const short SENSORS    PROGMEM =  3;   // Number of sensors
const short ITER       PROGMEM =  5;   // Number of iterations
const short ACTUATOR   PROGMEM =  6;   // Pin which is connected to the actuator
const short LEDr       PROGMEM = 13;   // On-board LED
const short BOARD_ID   PROGMEM =  0;   // Board ID

/* Default threshold values and sampling time */
const short TX_TIME  PROGMEM = 15000;  // Interval between loop routines
const short TEMP_MAX PROGMEM =    27;  // Maximum temperature
const short TEMP_MIN PROGMEM =    18;  // Minimum temperature
const short RH_MAX   PROGMEM =    70;  // Maximum humidity
const short LUX_MIN  PROGMEM =   100;  // Minimum lux

// For more details see /etc/config/network on the boards (the other board is "192.168.43.200")
#define IP_BOARD "192.168.43.100"
#define FILENAME "/mnt/sd/datalog.txt"

/* Prototypes */
bool* alarm(float s[SENSORS]);              // Alarm activation based on the thresholds
void data_acquisition(short N);             // Acquisition of the mean value of N data
void display_alarm(bool a, bool b, bool c); // Alarm function
void display_error(void);                   // Show on LCD an error message
void display_I2C_status(short status);      // Show informations about the I2C devices
void display_network_init(short status);    // Show informations about the network
void display_values(short status);          // Show informations about acquired data
void display_welcome(short s);              // Load set up for the LCD display
void get_data(bool b, String f);            // Get data from server making HTTP GET request
String get_time(void);                      // Get time from linino (unix format)
short I2C_devices_init(void);               // Initialization of sensors
short I2C_devices_test(void);               // Test communication on I2C bus
bool network_connection(IPAddress ip);      // Tempt a connection to the server
bool network_test(void);                    // Test communication with the server
void post_data(String data, String method); // Post data to server making HTTP POST request
void send_from_microSD(void);               // Send data (if any) stored into the microSD
void send_to_server(String data);           // Send data to the server
void set_clock(void);                       // Synchronization of clock
int set_data_from_server(void);             // Get data from server and set the data
void store_into_microSD(String data);       // Store data into microSD card

/* Global variables */
float s[SENSORS];                           // Data vector
HTU21DF htu = HTU21DF();                    // Create a HTU21DF object
TSL2561 tsl(TSL2561_ADDR_FLOAT);            // Create a TSL2561 object
LiquidCrystal lcd(7, 8, 9, 10, 11, 12);     // Initialize the library with the numbers of the interface pins
IPAddress server(192, 168, 43, 100);        // IP address of the server on which there is the WS
YunClient client;                           // Cunstructor for the client
bool connection;                            // Status of the connection
short config;                               // Configuration status of the sensors
byte p[8] ={ 0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F };

/* Threshold values and sampling time */
unsigned long threshold[5] ={ TEMP_MIN, TEMP_MAX, RH_MAX, LUX_MIN, TX_TIME };

/*
 * The setup routine runs once when reset button is pressed
 */
void setup()
{
    // Initialization of the LCD
    display_welcome(0);

    pinMode(LEDr, OUTPUT);
    digitalWrite(LEDr, HIGH);   // Turn ON

    pinMode(ACTUATOR, OUTPUT);
    digitalWrite(ACTUATOR, LOW); // Turn OFF

    // Initialize communication between microprocessors
    display_welcome(1);
    Bridge.begin();

    // Initialize microSD card
    display_welcome(2);
    FileSystem.begin();

    // Configuration of sensors
    display_welcome(3);
    config = I2C_devices_init();

    while (config != 0)
    {
        // Show an error occurred on LCD
        display_error();

        // Configuration of sensors
        config = I2C_devices_init();
        display_I2C_status(config);
    }

    // Network connection
    display_welcome(4);
    connection = network_connection(server);
    while (connection != 1)
    {
        display_network_init(connection);
        connection = network_connection(server);
    }

    // Network connection
    display_welcome(5);
    set_clock();

    // Set up completed
    display_welcome(6);
    digitalWrite(LEDr, LOW);  // Turn OFF
}

/*
 * The loop routine runs over and over again forever
 */
void loop()
{
    // Test I2C devices connection
    config = I2C_devices_test();

    while (config != 0)
    {
        // Turn ON the LED
        digitalWrite(LEDr, HIGH);

        // Show an error occurred on LCD
        display_error();

        // Configuration of sensors
        config = I2C_devices_init();
        display_I2C_status(config);
    }

    // Turn OFF the LED
    digitalWrite(LEDr, LOW);

    unsigned long start = millis();  // To compansate the transmission time

    // Data acquisition
    data_acquisition(ITER);
    String data = "";  // Format the output data = "s0=xx.xx&s1=xx...board=X"

    for (short i = 0; i < SENSORS; i++)
        data += "s" + String(i) + "=" + String(s[i]) + "&";

    data += "board=" + String(BOARD_ID) + "&" + "time=" + get_time();

    // Alarm function
    bool *t;
    t = alarm(s);
    if (t[0] || t[1] || t[2])
        display_alarm(t[0], t[1], t[2]);

    // Try connecting to the server
    connection = network_connection(server);

    // Show and update acquired values on LCD
    display_values(connection);

    if (connection && network_test()) // If connected
    {
        send_from_microSD();   // Send data (if any) from SD
        send_to_server(data);  // Send data to the server
    }
    else
    {
        store_into_microSD(data); // store data into the microSD
    }

    // Try connecting to the server
    connection = network_connection(server);

    if (connection && network_test()) // If connected
    {
        // Get data from server and set the thresholds
        start = (set_data_from_server() == 0) ? 0 : start;
    }

    // Wait for the remaining time
    while (threshold[4] > (millis() - start));
}

/*
 * Alarm activation (with hysteresis)
 */
bool* alarm(float s[SENSORS])
{
    bool temp[3];

    temp[0] = (temp[0] == 1) ? ((s[0] < 1.1*threshold[0]) || (s[0] > 0.9*threshold[1])) : ((s[0] < threshold[0]) || (s[0] > threshold[1]));
    temp[1] = (temp[1] == 1) ? (s[1] > 0.9*threshold[2]) : (s[1] > threshold[2]);
    temp[2] = (temp[2] == 1) ? (s[2] < 1.1*threshold[3]) : (s[2] < threshold[3]);

    if (temp[0] || temp[1] || temp[2])
        digitalWrite(ACTUATOR, HIGH);   // Turn ON the ACTUATOR
    else
        digitalWrite(ACTUATOR, LOW);    // Turn OFF the ACTUATOR

    return temp;
}

/*
 * For each sensor, store the average of the N consecutive values acquired
 */
void data_acquisition(short N)
{
    // Initialize data vector
    for (short i = 0; i < SENSORS; i++)
        s[i] = 0;

    for (short i = 0; i < N; i++)
    {
        // Temperature and humidity
        s[0] += htu.readTemperature();
        s[1] += htu.readHumidity();

        // Luminosity/Brightness
        // Reads 32 bits with top 16 bits IR, bottom 16 bits full spectrum
        uint32_t lum = tsl.getFullLuminosity();
        uint16_t ir, full;
        ir = lum >> 16;
        full = lum & 0xFFFF;
        s[2] += (float)tsl.calculateLux(full, ir);

        // Delay in between reads for stability
        delay(10);  // 10ms (total time = 10ms x 10 iterations)
    }

    // Store the average values
    for (short i = 0; i < SENSORS; i++)
        s[i] /= N;
}

/*
 * Display alarm page on LCD
 */
void display_alarm(bool a, bool b, bool c)
{
    lcd.clear();
    lcd.setCursor(2, 0);
    lcd.print("Alarm status");

    // Temperature
    lcd.setCursor(0, 1);
    lcd.print("T:");
    lcd.setCursor(6, 1);
    if (a)
        lcd.print("ENABLED");
    else
        lcd.print("DISABLED");

    // Relative humidity
    lcd.setCursor(0, 2);
    lcd.print("RH:");
    lcd.setCursor(6, 2);
    if (b)
        lcd.print("ENABLED");
    else
        lcd.print("DISABLED");

    // Luminosity/Brightness
    lcd.setCursor(0, 3);
    lcd.print("Lux:");
    lcd.setCursor(6, 3);
    if (c)
        lcd.print("ENABLED");
    else
        lcd.print("DISABLED");

    delay(3000);
}

/*
 * Display error page on LCD
 */
void display_error()
{
    lcd.clear();
    lcd.setCursor(4, 1);
    lcd.print("An error");
    lcd.setCursor(4, 2);
    lcd.print("occurred");
    delay(3000);
}

/*
 * Show on LCD informations about the sensors
 */
void display_I2C_status(short status)
{
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Sensors setup");

    switch (status)
    {
        case 0: // I2C devices found
            lcd.setCursor(0, 1);
            lcd.print("HTU21DF: OK");
            lcd.setCursor(0, 2);
            lcd.print("TSL2561: OK");
            lcd.setCursor(0, 3);
            lcd.print("I2C bus set");
            delay(3000);
            break;
        case 1: // device TSL2561 not found
            lcd.setCursor(0, 1);
            lcd.print("HTU21DF: OK");
            lcd.setCursor(0, 2);
            lcd.print("TSL2561: fail");
            lcd.setCursor(0, 3);
            lcd.print("Restarting...");
            delay(3000);
            break;
        case 2: // device HTU21DF not found
            lcd.setCursor(0, 1);
            lcd.print("HTU21DF: fail");
            lcd.setCursor(0, 2);
            lcd.print("TSL2561: OK");
            lcd.setCursor(0, 3);
            lcd.print("Restarting...");
            delay(3000);
            break;
        case 3: // No I2C devices found
            lcd.setCursor(0, 1);
            lcd.print("HTU21DF: fail");
            lcd.setCursor(0, 2);
            lcd.print("TSL2561: fail");
            lcd.setCursor(0, 3);
            lcd.print("Restarting...");
            delay(3000);
            break;
    }
}

/*
 * Show on LCD the initial network setup
 */
void display_network_init(short status)
{
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Board IP address");
    lcd.setCursor(0, 1);
    lcd.print(IP_BOARD);
    lcd.setCursor(0, 2);
    lcd.print("wait");

    for (short i = 4; i < LCD_WIDTH; i++)
    {
        lcd.setCursor(i, 2);
        lcd.print(".");
        delay(250);
    }

    lcd.setCursor(0, 3);

    if (status)
        lcd.print("STATUS: ON-LINE");
    else
        lcd.print("STATUS: OFF-LINE");

    delay(3000);
}

/*
 * Show on LCD informations about sensors and connection
 */
void display_values(short status)
{
    // Clear the LCD screen
    lcd.clear();

    lcd.setCursor(0, 0);
    lcd.print("I.E.Q.A.   ID: ");
    lcd.print(BOARD_ID);

    // Print data on LCD display
    lcd.setCursor(0, 1);
    lcd.print("T:");
    lcd.print((float)s[0], 1);
    lcd.print("C ");

    lcd.print("RH:");
    lcd.print((float)s[1], 1);
    lcd.print("%");

    lcd.setCursor(0, 2);
    lcd.print("Lux:");
    lcd.print((float)s[2], 1);
    lcd.print("Lx");

    lcd.setCursor(0, 3);

    if (status == 1)
        lcd.print("Status: ON-LINE");
    else
        lcd.print("Status: OFF-LINE");
}

/*
 * Set up the LCD and show a welcome message
 */
void display_welcome(short s)
{
    switch (s)
    {
        case 0:  // Initialize LCD and display a welcome message

        // Create custom char to display progress in setup phase
            lcd.createChar(0, p);
            // Set up the LCD with LCD_WIDTH columns and LCD_HEIGHT rows
            lcd.begin(LCD_WIDTH, LCD_HEIGHT);

            // Welcome message on the LCD
            lcd.print(" >> IEQA System");
            lcd.setCursor(0, 1);
            lcd.print(" ICT Laboratory");
            lcd.setCursor(0, 2);
            lcd.print("    License:");
            lcd.setCursor(0, 3);
            lcd.print(" (CC BY-NC 4.0)");

            delay(3500);

            break;
        case 1:  // Initialize communication between microprocessors
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Initialize");
            lcd.setCursor(0, 1);
            lcd.print("communication");
            lcd.setCursor(0, 2);
            lcd.print("between uPs");

            for (short i = 0; i < 2; i++)
            {
                lcd.setCursor(i, 3);
                lcd.write((byte)0);
            }

            lcd.setCursor(13, 3);
            lcd.print("14%");

            delay(2000);
            break;
        case 2:  // Initialization of filesystem
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Initialization");
            lcd.setCursor(0, 1);
            lcd.print("filesystem and");
            lcd.setCursor(0, 2);
            lcd.print("mount of microSD");

            for (short i = 0; i < 7; i++)
            {
                lcd.setCursor(i, 3);
                lcd.write((byte)0);
            }

            lcd.setCursor(13, 3);
            lcd.print("53%");

            delay(2000);
            break;
        case 3:  // Configuration of I2C devices
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("I2C devices");
            lcd.setCursor(0, 1);
            lcd.print("configuration");

            for (short i = 0; i < 8; i++)
            {
                lcd.setCursor(i, 3);
                lcd.write((byte)0);
            }

            lcd.setCursor(13, 3);
            lcd.print("67%");

            delay(2000);
            break;
        case 4:  // Network connection
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Connection to");
            lcd.setCursor(0, 1);
            lcd.print("the server");
            lcd.setCursor(0, 2);
            lcd.print(server);

            for (short i = 0; i < 10; i++)
            {
                lcd.setCursor(i, 3);
                lcd.write((byte)0);
            }

            lcd.setCursor(13, 3);
            lcd.print("82%");

            delay(2000);
            break;
        case 5:  // Clock synchronization
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Synchronization");
            lcd.setCursor(0, 1);
            lcd.print("of clock with");
            lcd.setCursor(0, 2);
            lcd.print("the server");

            for (short i = 0; i < 12; i++)
            {
                lcd.setCursor(i, 3);
                lcd.write((byte)0);
            }

            lcd.setCursor(13, 3);
            lcd.print("99%");

            delay(2000);
            break;
        case 6:  // Setup completed
            lcd.clear();
            lcd.setCursor(1, 0);
            lcd.print("Setup has been");
            lcd.setCursor(2, 1);
            lcd.print("successfully");
            lcd.setCursor(0, 3);
            lcd.print("please wait.....");

            delay(2000);
            break;
    }
}

/*
 * Get data from server
 */
void get_data(bool b, String f)
{
    // Connected
    client.print("GET /ieqa/" + f);
    client.print(b);
    client.println(" HTTP/1.1");
    client.print("Host: ");
    client.println(server);
    client.println("User-Agent: Arduino/1.0");
    client.println("Connection: close");
    client.println();
}

/*
 * Get time from Linino (Linux) side
 */
String get_time(void)
{
    String result;
    Process time;
    time.runShellCommand("echo `date +%FT%T`");

    while (time.available() > 0)
    {
        char c = time.read();
        if (c != '\n')
            result += c;
    }

    return result;
}

/*
 * Initialization and test of I2C devices
 */
short I2C_devices_init(void)
{
    // Initialization of the communication on I2C bus
    tsl.begin();
    htu.begin();

    // Test if the communication works properly
    short test = I2C_devices_test();

    if (test == 0) // I2C communication works
    {
        // temperature and humidity sensors configured
        // luminosity sensor setup (16x gain for dim environment)
        tsl.setGain(TSL2561_GAIN_16X);
        // set medium integration time (medium light)
        tsl.setTiming(TSL2561_INTEGRATIONTIME_101MS);
    }

    return test;
}

/*
 * Test of the I2C devices on bus (the function returns 0 if the devices work properly,
 * otherwise returns 1 if TSL2561 doesn't work, 2 if HTU21DF doesn't work or 3 if both
 * sensors don't work properly)
 */
short I2C_devices_test(void)
{
    // TSL2561 I2C address: 0x39
    Wire.beginTransmission(TSL2561_ADDR_FLOAT);
    byte error1 = Wire.endTransmission();

    // HTU21DF I2C address: 0x40
    Wire.beginTransmission(HTU21DF_I2CADDR);
    byte error2 = Wire.endTransmission();

    if ((error1 == 0)&&(error2 == 0))
        return 0;   // I2C bus devices work
    else if (error1 != 0)
        return 1;   // TSL2561 not found
    else if (error2 != 0)
        return 2;   // HTU21DF not found
    else
        return 3;   // No I2C devices found
}

/*
 * Try to connect to the server
 */
bool network_connection(IPAddress ip)
{
    // Close the connection
    client.stop();

    // Try a connection
    if (client.connect(ip, 80)) // Connection successful
        return 1;
    else
        return 0;
}

/*
 * Network connection test
 */
bool network_test(void)
{
    // If the server is disconnected, stop the client
    if (client.connected())
        return 1;
    else
        return 0;
}

/*
 * Send data to server
 */
void post_data(String data, String method)
{
    // Connected
    client.println("POST /ieqa/insert_data HTTP/1.1");
    client.print("Host: ");
    client.println(server);
    client.println("User-Agent: Arduino/1.0");
    client.print("Connection: ");
    client.println(method);
    client.println("Content-Type: application/x-www-form-urlencoded;");
    client.print("Content-length: ");
    client.println(data.length());
    client.println();
    client.println(data);
}

void send_from_microSD(void)
{
    if (FileSystem.exists(FILENAME))  // send data stored into microSD
    {
        // Open the file in reading mode
        File dataFile = FileSystem.open(FILENAME, FILE_READ);

        char temp;
        String temp_data;

        while (dataFile.available())
        {
            temp_data = "";

            while ((temp = dataFile.read()) != '\n')
                temp_data += temp;

            if (connection && network_test())
                post_data(temp_data, "keep-alive"); // Connection: keep-alive
        }

        // Close and remove the file
        dataFile.close();
        FileSystem.remove(FILENAME);
    }
}

/*
 * Send data (acquired or stored into the microSD card) to server
 */
void send_to_server(String data)
{
    if (network_test())
        post_data(data, "close");  // Connection: close

     // Close the connection
    client.stop();
}

/*
 * Get data (clock) from server
 */
void set_clock(void)
{
    get_data(BOARD_ID, "get_time/");
    delay(1000);   // Mandatory to get the response

    String server_clock = "";

    while (client.available())
    {
        char c;
        while ((c = client.read()) != '{');
        while ((c = client.read()) != '}')
            server_clock += c;
    }

    String command = "date --set '" + server_clock + "'";
    Process Set;
    Set.runShellCommand(command);

    // Close the connection
    client.stop();
}

/*
 * Get data (thresholds or sampling time) from server
 */
int set_data_from_server(void)
{
    get_data(BOARD_ID, "get_threshold_values/");
    delay(1000);   // Mandatory to get the response

    String values = "";
    unsigned long v;
    bool flag = 1;

    while (client.available())
    {
        char c;
        while ((c = client.read()) != '{');
        while ((c = client.read()) != '}')
            values += c;
        values += ',';
    }

    for (short i = 0, j = 0, k = 0; k < 5; k++)
    {
        i = values.indexOf('t', i);
        i += 4;
        j = values.indexOf(',', i);
        v = values.substring(i, j).toInt();

        if (threshold[k] != v)
        {
            flag = 0;
            threshold[k] = v;
        }
    }

    for (short i = 0; i < 5; i++)
    {
        if (threshold[i] == 0)
        {
            threshold[0] = TEMP_MIN;
            threshold[1] = TEMP_MAX;
            threshold[2] = RH_MAX;
            threshold[3] = LUX_MIN;
            threshold[4] = TX_TIME;
            i = 5;
        }
    }

    // Close the connection
    client.stop();

    return flag;
}

/*
 * Store data (acquired) into the microSD
 */
void store_into_microSD(String data)
{
    // Open file "datalog.txt"
    File dataFile = FileSystem.open(FILENAME, FILE_APPEND);

    if (dataFile)   // permission to write into microSD
    {
        dataFile.println(data);
        dataFile.close();
    }
}

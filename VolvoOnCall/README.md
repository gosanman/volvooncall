# VolvoOnCall
Anbindung von Volvo Fahrezugen an IP-Symcon. Auslesen von Fahrzeugdaten möglich.
Modul befindet sich in der Entwicklung, ich versuche weitere Daten und Funktionen laufend hinzuzufügen.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Anbindung von Volvo Fahrezugen an IP-Symcon. Auslesen von Fahrzeugdaten möglich.
* Das Modul ist noch Beta, es wird immer nur das erste Fahrzeug im Account ausgelesen. Weitere Funktionen folgen.

### 2. Voraussetzungen

- IP-Symcon ab Version 5.0

### 3. Software-Installation

* Über den Module Store das Modul VolvoOnCall installieren.
* Alternativ über das Module Control folgende URL hinzufügen:
`https://github.com/gosanman/volvooncall/`

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'VolvoOnCall'-Modul unter dem Hersteller '(Gerät)' aufgeführt.  

__Konfigurationsseite__:

Name      | Beschreibung
--------- | ---------------------------------
Google API Schlüssel  | Google API Schlüssel für Maps Integration
Benutzername          | Benutzername für den Volvo On Call Account
Passwort              | Passwort für den Volvo On Call Account
Intervall (Minuten)   | Intervall in Minuten zum aktuallisieren der Daten 

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name                | Typ       | Beschreibung
------------------- | --------- | ----------------
distanceToEmpty     | Integer   | Anzeige wieviele km noch mit dem aktuellen Tankinhalt zu fahren sind
fuelAmount          | Integer   | Anzeige des aktuellen Tankinhaltes in Liter
fuelAmountLevel     | Integer   | Anzeige des aktuellen Tankinhaltes in Prozent
odoMeter            | Integer   | Gesamtkilomenterstand
carLocked           | Boolean   | Anzeige ob das Auto verriegelt ist
positionLatitude    | String    | Aktuelle Position des Autos (Breitengrad)
positionLongitude   | String    | Aktuelle Position des Autos (Längengrad)
positionPic         | String    | Google Maps Integration für das Webfront (API Key wird zwingend benötigt)
positionTime        | String    | Zeitpunkt der Position des Autos

##### Profile:

Name             | Typ
---------------- | ------- 
Volvo.Distance   | Integer
Volvo.FuelAmount | Integer
Volvo.FuelLevel  | Integer

### 6. WebFront

Über das WebFront können die momentanen Werte der gelisteten Zielvariablen angezeigt werden.

### 7. PHP-Befehlsreferenz

`boolean VOC_Update(integer $InstanzID);`  
Aktuallisiert alle Daten. Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`VOC_Update(12345);`

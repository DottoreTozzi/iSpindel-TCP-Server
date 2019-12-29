<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
$mainpage=strtoupper("Hauptseite");

// Loads personal config file for db connection details. If not found, default file will be used
if ((include_once './config/common_db_config.php') == FALSE){
    include_once("./config/common_db_default.php");
    }
//  Loads db query functions
include_once("./include/common_db_query.php");
?>


<!DOCTYPE html>
<html>
<head>
    <title>RasPySpindel Homepage</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">

<style id="php_Parameter_10648_Styles"><!--table
	{mso-displayed-decimal-separator:"\,";
	mso-displayed-thousand-separator:"\.";}
.xl1510648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6310648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	border:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6410648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	border:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6510648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	border-top:1.0pt solid windowtext;
	border-right:none;
	border-bottom:1.0pt solid windowtext;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6610648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	border-top:1.0pt solid windowtext;
	border-right:1.0pt solid windowtext;
	border-bottom:none;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6710648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	border-top:1.0pt solid windowtext;
	border-right:none;
	border-bottom:none;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6810648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	border:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6910648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:left;
	vertical-align:middle;
	border:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7010648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:left;
	vertical-align:middle;
	border-top:1.0pt solid windowtext;
	border-right:1.0pt solid windowtext;
	border-bottom:none;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7110648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:left;
	vertical-align:middle;
	border-top:none;
	border-right:1.0pt solid windowtext;
	border-bottom:none;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7210648
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:left;
	vertical-align:middle;
	border-top:none;
	border-right:1.0pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
--></style>

</head>
<body>
<form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">

<ul class="nav navbar-nav">
<h2>RasPySpindel</h2>
  <li><a class="active" href="#home">Hilfe</a></li>
  <li><a href="#main">Hauptseite</a></li>
  <li><a href="#config">Konfiguration</a></li>
  <li><a href="#calibration">Kalibrierung</a></li>
  <li><a href="#diagram">Diagramme</a></li>
  <li><a href="#sendmail">Email Alarme Konfigurieren</a></li>
  <li><a href="#parameter">php Script Parameter</a></li>
  <li><a href="#about">About</a></li>
  <li><a href="index.php">Zurück</a></li>
</ul>

<div id="home" style="margin-left:15%;padding:1px 16px;height:1000px;">
  <h2>Hilfe</h2>
  <p>Dieses in Python geschriebene Server Skript dient dazu, von der iSpindel kommende Rohdaten über eine generische TCP Verbindung zu empfangen. Auf zusätzlichen, unnötigen Overhead durch Protokolle wie http wird hierbei bewusst verzichtet, um den Stromverbrauch der iSpindel so gut es geht zu minimieren. Die empfangenen Daten können als CSV (“Comma Separated Values”, also durch Kommas getrennte Werte) in Textdateien gespeichert (und so zum Beispiel in Excel leicht importiert) werden. Ebenso ist es möglich, die Daten in einer MySQL Datenbank abzulegen.
Somit hat man einen lokalen Server, der auch ohne Internet Anbindung funktioniert und den Einsatz der iSpindel im Heimnetzwerk ermöglicht. Die Zugriffszeiten sind kürzer und dadurch sinkt natürlich auch der (ohnehin geringe) Stromverbrauch der iSpindel noch weiter.</p>

<p>Um nicht auf die Anbindung an Ubidots verzichten zu müssen, besteht aber auch die Option, die Daten zusätzlich dorthin weiterzuleiten.
Das geschieht transparent, ohne dass die iSpindel auf die Verbindung warten muss. Der lokale Server fungiert dann sozusagen als Proxy.</p>

<p>Das Skript ist plattformunabhängig und kann z.B. auf einem Raspberry Pi, eingesetzt werden. Aber auch der Einsatz auf einem evtl. gemieteten dedizierten (oder virtuellen) Server oder einem beliebigen Rechner im Heimnetz ist möglich. Der Betrieb mehrerer iSpindeln gleichzeitig funktioniert problemlos und ohne Verzögerungen, da Multithreading implementiert ist.
Getestet wurde es unter Mac OS X (Sierra) und Linux (Debian), es sollte aber auch unter Windows problemlos laufen.
Die einzige Voraussetzung ist, dass Python installiert ist.</p>

<p>Für die Anbindung an MySQL muss auch der python-mysql.connector installiert sein. In der Konfiguration der iSpindel wählt man TCP aus, und trägt die IP Adresse des Servers ein, auf dem das Skript läuft. Als Port wählt man am besten die Voreinstellung 9501.</p>

<p>Nun muss das Skript selbst unbedingt noch konfiguriert werden. Dazu öffnet man es mit einem Text Editor und bearbeitet die gleich beschriebenen Einstellungen.</p>

<p>Dann wird es in einen beliebigen Pfad auf dem Zielsystem kopiert, /usr/local/bin bietet sich an, oder einfach /home/pi. Mit chmod 755 iSpindle.py macht man es ausführbar und startet es mit ./iSpindle.py. Alternativ (z.B. unter Windows) startet man es mit python iSpindle.py. Wenn alles funktioniert, beendet man das Skript, setzt DEBUG = 0 und startet es im Hintergrund neu mit ./iSpindle.py &</p>
</div>

<div id="main" style="margin-left:15%;padding:1px 16px;height:2500px;">
  <h2>Hauptseite</h2>
  </br>
  </br>
  <p>Wenn der Server korrekt installiert ist und läuft, kann die Hauptseite über die IP des Servers aufgerufen werden (z.B.: http://192.168.10.11/index.php)</p>
  <p>Wenn Daten von Spindeln an die Datenbank innerhalb der letzten 7 Tage (default) gesendet wurden, so sieht man auf der Seite den letzten Datensatz der Spindeln, sofern das über den Parameter 'SHOWSUMMARY' in der Section 'GENERAL' in den Settings aktiviert wurde.</p>
  <p>Es wird auch angezeigt, ob und mit welcher PID der Server im Hintergrund läuft. </p>
  <p>Auf der Hauptseite gibt es auch die Möglichkeit, die Seite für die Kalibrierung der Spindeln zu öffnen. Hierfür muss der Button 'Spindel im TCP Server Kalibrieren' angeklickt werden. Durch anklicken des Buttons 'TCP Server Settings Editieren' wird die Konfigurationsseite geladen.</p>
  <img class="img_norm" src="help/images/index1.png" alt="Hauptseite mit Übersicht">
  <p>Sollten keine Daten in den letzten 7 Tagen in die Datenbank geschrieben worden sein, so wird die Option angeboten, den Zeitraum zu verändern, um ggf auch ältere Daten für Spindeln anzeigen zu lassen. Wenn man den Button 'Historie anpassen' anklickt, wird die Hauptseite neu geladen.</p>
  <img class="img_norm" src="help/images/index2.png" alt="Hauptseite ohne Daten">
  <p>Auf der Seite kann man ein entsprechendes Device auswählen: </p>
  <img class="img_norm" src="help/images/index3.png" alt="Hauptseite Device auswählen">
  <p>Für das ausgewählte Device kann man dann ein Diagramm oder eine Funktion wie z.B. 'Gärbeginn setzen' auswählen. Nach der Auswahl des Diagramms muss dann noch der Button 'Diagramm anzeigen' angeklickt werden und es öffnet sich die entsprechende Seite.</p>
  <img class="img_norm" src="help/images/index4.png" alt="Hauptseite Funktion/Diagramm auswählen">
</div>

<div id="config" style="margin-left:15%;padding:1px 16px;height:4500px;">
  <h2>Konfiguration</h2>
  </br>
  </br>
  <p>Von der Hauptseite kommt man über den Link 'TCP Server Settings Editieren' auf die Settings Seite</br>
  <p>Um zurück zur Hauptseite zu kommen, kann man oben links den 'Home' Button oder den 'Zurück' Button verwenden</br>
  <p>Dort hat man die Möglichkeit, verschiedene Einstellungen festzulegen. Die Einstellungen sind zunächst in 'Devices' unterteilt:</p>
  <b>Devices:</b>
  <p style="white-space: pre;">GLOBAL&#9;Settings sind Server spezifisch und somit für alle Devices gleich.</br>_DEFAULT&#9;Settings gelten für alle Devices, die keine individuellen Einstellungen haben. Diese Settings können für jedes device kopiert werden und dann individuell angepasst werden.</br>iSpindel001&#9;Beispielname für ein Device mit dem Namen iSpindel001, dass für individuelle angelegt wurde Settings angelegt wurde.</p>
  <p>Außerdem kann man die Datentabelle der Datenbank als sql File exportieren, wenn man den entsprechenden Button anklickt.</p>

  <img class="img_norm" src="help/images/Settings1.PNG" alt="Settings Deviceauswahl">
  <p><b>Sections:</b></br>
  Dann sind die Settings noch in sogenannte Sections unterteilt um eine bessere Übersicht zu haben. Es kann sein, dass z.B. bei der Auswahl von 'GOBAL' und z.B. 'BREWSPY' keine Settings angezeigt werden, da diese nur unter '_DEFAULT' bzw. einem angelegten individuellen Device zu sehen sind. </p>
  <img class="img_norm" src="help/images/Settings2.PNG" alt="Settings Sectionauswahl">
  <p><b>Settings speichern:</b></br>
  Wenn Settings geändert werden, müssen sie mit dem Button 'Settings in DB schreiben' in die Datenbank übertragen werden, sonst haben Änderungen keine Wirkung.</p>
  </br>
  <p><b>Individuelle Settings für Device anlegen:</b></br>
  Um individuelle Settings für ein Device anzulegen, muss das entsprechende Device aus der Liste unterhalb der Parameter ausgewählt werden und der Button 'Individuelle Settings für Device anlegen' geklickt werden. Danach erscheint in der Auswahl 'Device' im oberen Bereich auch das entsprechende Device zur Auswahl. Ist nun das Device angelegt, dann kann man im Feld 'Section' die einzelnen Bereiche auswählen, für die man individuelle Settings einstellen und speichern kann. Änderungen müssen durch anklicken des Button 'Settings in DB schreiben' gesichert werden. Sonst sind die Änderungen unwirksam.</p>

  <img class="img_norm" src="help/images/Settings3.PNG" alt="Settings für Device individuell anlegen">
  <p><b>Device aus individuellen Settings entfernen:</b></br>
  Ein Device kann auch aus den individuellen Settings wieder entfernt werden. Dazu muss das entsprechende Device ausgewählt werdne und der Button 'Device aus individuellen Settings entfernen' angeklickt werden. Derzeit findet keine Sicherheitsabfrage statt. Die Individuellen Settings werden unwiderruflich gelöscht und das Device verwendet dann die Settings, die unter '_DEFAULT' hinterlegt sind. Um individuelle Settings für das Device wieder zu verwenden, muss es erst wieder neu angelegt werden.</p>
  <img class="img_norm" src="help/images/Settings4.PNG" alt="Device aus individuellen Settings entfernen">
</div>

<div id="calibration" style="margin-left:15%;padding:1px 16px;height:1500px;">
  <h2>Kalibrierung</h2>
  <p>Von der Hauptseite kommt man über den Link 'Spindel im TCP Server kalibrieren' auf die Kalibrierseite.</br>
     Um zurück zur Hauptseite zu kommen, kann man oben links den 'Home' Button oder den 'Zurück' Button verwenden.</br>
     Im Auswahlfeld muss die Spindel selektiert werden, für die im Server eine Kalibrierung hinterlegt werden soll. </p>
  <img class="img_norm" src="help/images/Calibrate1.png" alt="Spindel fuer Kalibrierung auswaehlen">
  <p>Danach können die 3 Konstanten eingegeben werden, die z.B. mit dem Excel Sheet für die Kalibrierung berechnet wurden. Um die Kalibrierung auch zu speichern, muss der Button 'Kalibrierung an DB senden' noch angeklickt werden. Bei alle Diagrammen, außer plato.php wird nun die Stammwürze, bzw. der Restextrakt oder der Vergärungsgrad mit den für diese Spindel mit der hinterlegten Kalibrierung berechnet. Das gleiche gilt für die Berechnungen in den Email Alarmen. Eine Berechnung auf Basis der von der Spindel übermittelten Gravity Werte ist derzeit nicht integriert.</p>
  <img class="img_norm" src="help/images/Calibrate2.png" alt="Kalibrierung an DB senden">
</div>

<div id="diagram" style="margin-left:15%;padding:1px 16px;height:4500px;">
  <h2>Diagramme</h2>
  <p>Die Diagramme werden von der Hauptseite aus für die ausgewählte Spindel aufgerufen.</br>
     Wird ein Diagramm mit dem Resetflag aufgerufen, so werden Daten für die ausgewählte Spindel seit dem letzten Rest dargestellt.</br>
     In der Kopfzeile des Diagramms ist der Name der Spindel und der aktuelle Sudname zu sehen. Oben rechts ist ein 'Home' Button integriert. Beim Anklicken des Buttons kommt man wieder zur Hauptseite zurück.</br>
</p>
  <img class="img_norm" src="help/images/Diagramm1.png" alt="Diagramm mit Reset">
  <p>Wird das Resetflag auf der Hauptseite beim Laden des Diagramms nicht gewählt, sondern nur wie im Beispiel 12 Tage, dann werden die letzten 12 Tage für die Spindel ab dem jetzigen Datum dargestellt.</p>
  <img class="img_norm" src="help/images/Diagramm2.png" alt="Diagramm2">
  <p>Wenn man die Maus über das Diagramm bewegt, so werden die Daten der Kurven auch noch einmal für den entsprechenden Zeitpunkt als Tooltip angezeigt.</p>
  <img class="img_norm" src="help/images/Diagramm3.png" alt="Diagramm3">
  <p>Am rechten oberen Rand der x/y-Diagramme ist ein Menü, mit dem man das Diagramm als PDF oder Bild abspeichern kann.</p>
  <img class="img_norm" src="help/images/Diagramm4.png" alt="Diagramm4">
  <p>Da die Spindelwerte insbesondere bei obergärigen Hefen stärkeren Schwankungen unterliegen können, gibt es die Möglichkeit einige Diagramme (Extrakt vom Server und Winkel) normal oder geglättet darzustellen. Bei der Glättung wird ein gleitender Mittelwert berechnet. Als default wird ein Zeitraum von 120 Minuten verwendet. Details können im Abschnitt php Script Parameter angesehen werden.</p>
  <img class="img_small" src="help/images/Diagramm5.png" alt="Diagramm normal">
  <img class="img_small" src="help/images/Diagramm6.png" alt="Diagramm geglaettet">
</div>



<div id="sendmail" style="margin-left:15%;padding:1px 16px;height:3500px;">
  <h2>Email Alarme Konfigurieren</h2>
  <p>Der Server kann so konfiguriert werden, dass er Email Alarme, bzw. eine tägliche Status Email versendet. Hierzu müssen Einstellungen in den Settings vorgenommen werden.</br></br>
     Zunächst müssen erst einmal die Daten des Email Kontos eingegeben werden. Die Änderungen müssen erst gespeichert werden ('Settings in DB schreiben'), bevor sie wirksam wird. Die Globalen Einstellungen gelten für alle Devices.</br></br>
     Mit den Button 'Sende Test Email' kann getestet werden, ob das versenden funktioniert. Sollte es Probleme geben, so können über den Parameter ENABLEDEBUG noch mehr Details zum Problem während des Versands der Test Email angezeigt werden.</br></br>
Über den Parameter ENABLESTATUS kann man festlegen, ob eine tägliche Statusemail der Daten versendet werdne soll. Der Parameter TIMESTATUS legt die Uhrzeit fest, zu der eine Statusemail versendet wird. 6 steht z.B. für 6:00. Da der Server nur eine Email sendet, wenn auch Daten von einer Spindel kommen, ist ein Zeitraum von +/- 15 Minuten festgelegt. Somit kommt dann bei der Auswahl von 6 eine Email zwischen 5:45 und 6:15, falls eine Spindel Daten sendet.</br></br>
Wenn man z.B. eine ganze Reihe von Devices hat, für die keine Email versendet werden soll, so kann man diesen im Namen ein Kürzel geben. Entspricht das Kürzel dem Feld EXCLUDESTRING, so werden diese Devices beim Email Versand nicht berücksichtigt.</p>
  <img class="img_norm" src="help/images/email1.png" alt="Globale Email Settings">
  <p>Wechselt man nun bei den Settings auf das Device _DEFAULT können nun die Default Settings eingstellt werden. Diese werden dann auch erst einmal für jede Spindel verwendet. Die Default Settings können auch für ein Device kopiert und dann geändert werden. Somit hat man die Möglichkeit, diese Einstellungen für jedes Devices anders anzupassen</p>
  <img class="img_norm" src="help/images/email2.png" alt="Default Email Settings">
  <p>Hier kann man sehen, dass z.B. Settings für das Device 'iSpindel001' angelegt worden sind. Für diese SPindel werden dann diese Settings herangezogen, während für die anderen Spindeln die Settings unter '_DEFAULT' verwendet werden.</p>
  <img class="img_norm" src="help/images/email3.png" alt="Individuelle Email Settings">
  <p>Wenn alle Einstellung vorgenommen und gespeichert wurden, kommt man mit dem Home Button wieder zur Hauptseite zurück.</p>
</div>


<div id="parameter" style="margin-left:15%;padding:1px 16px;height:3000px;">
  <h2>php Script Parameter</h2>
  <p>Die meisten Seiten des Servers können mit Parametern geladen werden. Die möglichen Parameter für jedes Script sind unten beschrieben. Prinzipiell werden die ensprechenden PArameter bereits über dne Aufruf der Scripte von der Hauptseite gewählt.</p>
  <p>Der Aufruf eines Scripts sieht dann z.B. so aus:</p>
  <p>http://IP.DES.SERVERS/iSpindle/plato4.php?name=iSpindel001&days=7</p>
  <p>Der erste Parameter wird mit einem ? hinter dem .php geschrieben und der Wert wird dann hinter einem '=' gesetzt. Möchte man mehrere Parameter an das Script übergeben, sie geschieht die Verknüpfung über ein '&'.</p>
  <p></p>

<div id="php_Parameter_10648" align=left x:publishsource="Excel">

<table border=0 cellpadding=0 cellspacing=0 width=933 style='border-collapse:
 collapse;table-layout:fixed;width:700pt'>
 <col width=273 span=2 style='width:205pt'>
 <col width=387 style='mso-width-source:userset;mso-width-alt:14153;width:290pt'>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6310648 width=273 style='height:15.75pt;width:205pt'>Seite</td>
  <td class=xl6310648 width=273 style='border-left:none;width:205pt'>Paramter</td>
  <td class=xl6810648 width=387 style='border-left:none;width:290pt'>Ergebnis</td>
 </tr>
 <tr height=86 style='height:64.5pt'>
  <td height=86 class=xl6910648 width=273 style='height:64.5pt;border-top:none;
  width:205pt'>index.php</td>
  <td class=xl6410648 width=273 style='border-top:none;border-left:none;
  width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;border-left:none;
  width:290pt'>Wenn die Seite mit dem Parameter geladen wird, so wird nicht der
  default Wert für daysago aus der config Datei verwendet, sondern der Wert von
  days. So kann man z.B. die suche nach Daten über einen längeren Zeitraum
  ermöglichen, ohne den Defaultwert ändern zu müssen</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td rowspan=5 height=195 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:146.25pt;border-top:none;width:205pt'>angle.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>weeks=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Wochen, für die Daten im Diagramm dargestellt werden (default=0)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Tage, für die Daten im Diagramm dargestellt werden (default=7 bzw. der Wert
  aus der php config)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6710648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>hours=INTEGER</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Stunden, für die Daten im Diagramm dargestellt werden (default=0). Alle drei
  Werte können Kombiniert werden</td>
 </tr>
 <tr height=69 style='height:51.75pt'>
  <td height=69 class=xl6510648 width=273 style='height:51.75pt;border-left:
  none;width:205pt'>reset=TRUE (0 oder 1)</td>
  <td class=xl6410648 width=387 style='width:290pt'>Soll das Diagramm seit dem
  letzten Setzen des Resetflags dargetellt werden, so ist hier eine 1 zu
  wählen. Dieses Flag überschreibt auch alle eventuell gewählten Parameter
  bzgl. Weeks, Days und Hours.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td rowspan=6 height=247 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:185.25pt;border-top:none;width:205pt'>angle_ma.php</td>
  <td class=xl6710648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-left:
  none;width:205pt'>weeks=INTEGER</td>
  <td class=xl6410648 width=387 style='width:290pt'>Anzahl der Wochen, für die
  Daten im Diagramm dargestellt werden (default=0)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Tage, für die Daten im Diagramm dargestellt werden (default=7 bzw. der Wert
  aus der php config)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6710648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>hours=INTEGER</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Stunden, für die Daten im Diagramm dargestellt werden (default=0). Alle drei
  Werte können Kombiniert werden</td>
 </tr>
 <tr height=69 style='height:51.75pt'>
  <td height=69 class=xl6510648 width=273 style='height:51.75pt;border-left:
  none;width:205pt'>reset=TRUE (0 oder 1)</td>
  <td class=xl6410648 width=387 style='width:290pt'>Soll das Diagramm seit dem
  letzten Setzen des Resetflags dargetellt werden, so ist hier eine 1 zu
  wählen. Dieses Flag überschreibt auch alle eventuell gewählten Parameter
  bzgl. Weeks, Days und Hours.</td>
 </tr>
 <tr height=52 style='height:39.0pt'>
  <td height=52 class=xl6510648 width=273 style='height:39.0pt;border-top:none;
  border-left:none;width:205pt'>moving=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Zeitraum in
  Minuten, für den der gleitende Mittelwert berechnet wird. Wird der Parameter
  nicht verwendet, so wird ein default Wert von 120 Minuten zur Berechnung
  herangezogen.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6910648 width=273 style='height:15.75pt;border-top:
  none;width:205pt'>battery.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td rowspan=5 height=195 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:146.25pt;border-top:none;width:205pt'>batterytrend.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>weeks=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Wochen, für die Daten im Diagramm dargestellt werden (default=0)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Tage, für die Daten im Diagramm dargestellt werden (default=7 bzw. der Wert
  aus der php config)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6710648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>hours=INTEGER</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Stunden, für die Daten im Diagramm dargestellt werden (default=0). Alle drei
  Werte können Kombiniert werden</td>
 </tr>
 <tr height=69 style='height:51.75pt'>
  <td height=69 class=xl6510648 width=273 style='height:51.75pt;border-left:
  none;width:205pt'>reset=TRUE (0 oder 1)</td>
  <td class=xl6410648 width=387 style='width:290pt'>Soll das Diagramm seit dem
  letzten Setzen des Resetflags dargetellt werden, so ist hier eine 1 zu
  wählen. Dieses Flag überschreibt auch alle eventuell gewählten Parameter
  bzgl. Weeks, Days und Hours.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6910648 width=273 style='height:26.25pt;border-top:
  none;width:205pt'>calibration.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die die Kalibrierung durchgeführt werden soll.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl7010648 width=273 style='height:15.75pt;border-top:
  none;width:205pt'>plato4_delta.php</td>
  <td class=xl6710648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl7110648 width=273 style='height:26.25pt;width:205pt'>&nbsp;</td>
  <td class=xl6510648 width=273 style='border-left:none;width:205pt'>weeks=INTEGER</td>
  <td class=xl6410648 width=387 style='width:290pt'>Anzahl der Wochen, für die
  Daten im Diagramm dargestellt werden (default=0)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl7110648 width=273 style='height:26.25pt;width:205pt'>&nbsp;</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Tage, für die Daten im Diagramm dargestellt werden (default=7 bzw. der Wert
  aus der php config)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl7110648 width=273 style='height:26.25pt;width:205pt'>&nbsp;</td>
  <td class=xl6710648 width=273 style='border-top:none;border-left:none;
  width:205pt'>hours=INTEGER</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Stunden, für die Daten im Diagramm dargestellt werden (default=0). Alle drei
  Werte können Kombiniert werden.</td>
 </tr>
 <tr height=69 style='height:51.75pt'>
  <td height=69 class=xl7110648 width=273 style='height:51.75pt;width:205pt'>&nbsp;</td>
  <td class=xl6510648 width=273 style='border-left:none;width:205pt'>reset=TRUE
  (0 oder 1)</td>
  <td class=xl6410648 width=387 style='width:290pt'>Soll das Diagramm seit dem
  letzten Setzen des Resetflags dargetellt werden, so ist hier eine 1 zu
  wählen. Dieses Flag überschreibt auch alle eventuell gewählten Parameter
  bzgl. Weeks, Days und Hours.</td>
 </tr>
 <tr height=69 style='height:51.75pt'>
  <td height=69 class=xl7210648 width=273 style='height:51.75pt;width:205pt'>&nbsp;</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>moving=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Zeitraum in
  Minuten, für den die Differenz der Gravity berechnet wird. Wird der Parameter
  nicht verwendet, so wird ein default Wert von 720 Minuten (12 Stunden) zur
  Berechnung herangezogen.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td rowspan=6 height=247 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:185.25pt;border-top:none;width:205pt'>plato4_ma.php</td>
  <td class=xl6710648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-left:
  none;width:205pt'>weeks=INTEGER</td>
  <td class=xl6410648 width=387 style='width:290pt'>Anzahl der Wochen, für die
  Daten im Diagramm dargestellt werden (default=0)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Tage, für die Daten im Diagramm dargestellt werden (default=7 bzw. der Wert
  aus der php config)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6710648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>hours=INTEGER</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Stunden, für die Daten im Diagramm dargestellt werden (default=0). Alle drei
  Werte können Kombiniert werden</td>
 </tr>
 <tr height=69 style='height:51.75pt'>
  <td height=69 class=xl6510648 width=273 style='height:51.75pt;border-left:
  none;width:205pt'>reset=TRUE (0 oder 1)</td>
  <td class=xl6410648 width=387 style='width:290pt'>Soll das Diagramm seit dem
  letzten Setzen des Resetflags dargetellt werden, so ist hier eine 1 zu
  wählen. Dieses Flag überschreibt auch alle eventuell gewählten Parameter
  bzgl. Weeks, Days und Hours.</td>
 </tr>
 <tr height=52 style='height:39.0pt'>
  <td height=52 class=xl6510648 width=273 style='height:39.0pt;border-top:none;
  border-left:none;width:205pt'>moving=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Zeitraum in
  Minuten, für den der gleitende Mittelwert berechnet wird. Wird der Parameter
  nicht verwendet, so wird ein default Wert von 120 Minuten zur Berechnung
  herangezogen.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td rowspan=5 height=195 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:146.25pt;border-top:none;width:205pt'>plato4.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>weeks=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Wochen, für die Daten im Diagramm dargestellt werden (default=0)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Tage, für die Daten im Diagramm dargestellt werden (default=7 bzw. der Wert
  aus der php config)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6710648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>hours=INTEGER</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Stunden, für die Daten im Diagramm dargestellt werden (default=0). Alle drei
  Werte können Kombiniert werden</td>
 </tr>
 <tr height=69 style='height:51.75pt'>
  <td height=69 class=xl6510648 width=273 style='height:51.75pt;border-left:
  none;width:205pt'>reset=TRUE (0 oder 1)</td>
  <td class=xl6410648 width=387 style='width:290pt'>Soll das Diagramm seit dem
  letzten Setzen des Resetflags dargetellt werden, so ist hier eine 1 zu
  wählen. Dieses Flag überschreibt auch alle eventuell gewählten Parameter
  bzgl. Weeks, Days und Hours.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td rowspan=5 height=195 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:146.25pt;border-top:none;width:205pt'>plato.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>weeks=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Wochen, für die Daten im Diagramm dargestellt werden (default=0)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Tage, für die Daten im Diagramm dargestellt werden (default=7 bzw. der Wert
  aus der php config)</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6710648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>hours=INTEGER</td>
  <td class=xl6610648 width=387 style='border-top:none;width:290pt'>Anzahl der
  Stunden, für die Daten im Diagramm dargestellt werden (default=0). Alle drei
  Werte können Kombiniert werden</td>
 </tr>
 <tr height=69 style='height:51.75pt'>
  <td height=69 class=xl6510648 width=273 style='height:51.75pt;border-left:
  none;width:205pt'>reset=TRUE (0 oder 1)</td>
  <td class=xl6410648 width=387 style='width:290pt'>Soll das Diagramm seit dem
  letzten Setzen des Resetflags dargetellt werden, so ist hier eine 1 zu
  wählen. Dieses Flag überschreibt auch alle eventuell gewählten Parameter
  bzgl. Weeks, Days und Hours.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td rowspan=2 height=70 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:52.5pt;border-top:none;width:205pt'>reset_now.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Reset Flag in der Datenbank gesetzt werden soll.</td>
 </tr>
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6510648 width=273 style='height:26.25pt;border-top:
  none;border-left:none;width:205pt'>recipe=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Neuer
  Rezeptname für die Spindel, der ab dem Reset verwendet wird.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td rowspan=2 height=42 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:31.5pt;border-top:none;width:205pt'>settings.php</td>
  <td class=xl6410648 width=273 style='border-top:none;border-left:none;
  width:205pt'>device=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;border-left:none;
  width:290pt'>&nbsp;</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6410648 width=273 style='height:15.75pt;border-top:
  none;border-left:none;width:205pt'>recipe=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;border-left:none;
  width:290pt'>&nbsp;</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6910648 width=273 style='height:15.75pt;border-top:
  none;width:205pt'>status.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td rowspan=6 height=157 class=xl7010648 width=273 style='border-bottom:1.0pt solid black;
  height:117.75pt;border-top:none;width:205pt'>svg_ma.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6510648 width=273 style='height:15.75pt;border-top:
  none;border-left:none;width:205pt'>weeks=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>keine
  Auswirkung</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6510648 width=273 style='height:15.75pt;border-top:
  none;border-left:none;width:205pt'>days=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>keine
  Auswirkung</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6510648 width=273 style='height:15.75pt;border-top:
  none;border-left:none;width:205pt'>hours=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>keine
  Auswirkung</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6510648 width=273 style='height:15.75pt;border-top:
  none;border-left:none;width:205pt'>reset=TRUE (0 oder 1)</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>keine
  Auswirkung</td>
 </tr>
 <tr height=52 style='height:39.0pt'>
  <td height=52 class=xl6510648 width=273 style='height:39.0pt;border-top:none;
  border-left:none;width:205pt'>moving=INTEGER</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Zeitraum in
  Minuten, für den der gleitende Mittelwert berechnet wird. Wird der Parameter
  nicht verwendet, so wird ein default Wert von 120 Minuten zur Berechnung
  herangezogen.</td>
 </tr>
 <tr height=21 style='height:15.75pt'>
  <td height=21 class=xl6910648 width=273 style='height:15.75pt;border-top:
  none;width:205pt'>wifi.php</td>
  <td class=xl6510648 width=273 style='border-top:none;border-left:none;
  width:205pt'>name=STRING</td>
  <td class=xl6410648 width=387 style='border-top:none;width:290pt'>Name der
  Spindel, für die das Diagramm erstellt werden soll.</td>
 </tr>
 <![if supportMisalignedColumns]>
 <tr height=0 style='display:none'>
  <td width=273 style='width:205pt'></td>
  <td width=273 style='width:205pt'></td>
  <td width=387 style='width:290pt'></td>
 </tr>
 <![endif]>
</table>

</div>

  <p></p>

</div>
<div id="about" style="margin-left:15%;padding:1px 16px;height:1000px;">
  <h2>About</h2>
  <p></p>
  <p></p>
</div>

</body>
</form>
</html>

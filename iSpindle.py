#!/usr/bin/env python2.7

# Version: 1.4.0
# New: Added new data fields for Interval and WiFi reception (RSSI) for Firmware 5.8 and later
# Chg: TimeStamp in CSV now in first column

# Version: 1.3.3
# New:  Added config parameter to use token field in iSpindle config as Ubidots token

# Previous changes and fixes:
# Fix: Debug Output of Ubidots response
# New: Forward data to another instance of this script or any other JSON recipient
# New: Support changes in firmware >= 5.4.0 (ID now transmitted as Integer)
#
# Generic TCP Server for iSpindel (https://github.com/universam1/iSpindel)
# Receives iSpindel data as JSON via TCP socket and writes it to a CSV file, Database and/or forwards it
#
# Stephan Schreiber <stephan@sschreiber.de>, 2017-02-02 - 2018-02-13
#

from socket import socket, AF_INET, SOCK_STREAM, SOL_SOCKET, SO_REUSEADDR
from datetime import datetime
import thread
import json

# CONFIG Start

# General
DEBUG = 0                               # Set to 1 to enable debug output on console (usually devs only)
PORT = 9501                             # TCP Port to listen to (to be used in iSpindle config as well)
HOST = '0.0.0.0'                        # Allowed IP range. Leave at 0.0.0.0 to allow connections from anywhere

# CSV
CSV = 0                                 # Set to 1 if you want CSV (text file) output
OUTPATH = '/home/pi/iSpindel/csv/'      # CSV output file path; filename will be name_id.csv
DELIMITER = ';'                         # CSV delimiter (normally use ; for Excel)
NEWLINE='\r\n'                          # newline (\r\n for windows clients)
DATETIME = 1                            # Leave this at 1 to include Excel compatible timestamp in CSV

# MySQL
SQL = 1                                 # 1 to enable output to MySQL database
SQL_HOST = '127.0.0.1'                  # Database host name (default: localhost - 127.0.0.1 loopback interface)
SQL_DB = 'iSpindle'                     # Database name
SQL_TABLE = 'Data'                      # Table name
SQL_USER = 'iSpindle'                   # DB user
SQL_PASSWORD = 'ohyeah'                 # DB user's password (change this)

# Ubidots (using existing account)
UBIDOTS = 1                                     # 1 to enable output to ubidots
UBI_USE_ISPINDLE_TOKEN = 1                      # 1 to use "token" field in iSpindle config (overrides UBI_TOKEN)
UBI_TOKEN = '******************************'    # global ubidots token, see manual or ubidots.com

# Forward to public server or other relay (i.e. another instance of this script)
FORWARD = 0
# FORWARDADDR = 'ispindle.de'
# FORWARDPORT = 9501
FORWARDADDR = '192.168.2.21'
FORWARDPORT = 9501

# ADVANCED
ENABLE_ADDCOLS = 0                              # Enable dynamic columns (do not use this unless you're a developer)

# CONFIG End

ACK = chr(6)            # ASCII ACK (Acknowledge)
NAK = chr(21)           # ASCII NAK (Not Acknowledged)
BUFF = 1024             # Buffer Size (greatly exaggerated for now)

def dbgprint(s):
    if DEBUG: print(str(s))

def handler(clientsock,addr):
    inpstr = ''
    success = 0
    spindle_name = ''
    spindle_id = 0
    angle = 0.0
    temperature = 0.0
    battery = 0.0
    gravity = 0.0
    user_token = ''
    interval = 0
    rssi = 0

    while 1:
        data = clientsock.recv(BUFF)
        if not data: break  # client closed connection
        dbgprint(repr(addr) + ' received:' + repr(data))
        if "close" == data.rstrip():
            clientsock.send(ACK)
            dbgprint(repr(addr) + ' ACK sent. Closing.')
            break   # close connection
        try:
            inpstr += str(data.rstrip())
            if inpstr[0] != "{" :
                clientsock.send(NAK)
                dbgprint(repr(addr) + ' Not JSON.')
                break # close connection
            dbgprint(repr(addr) + ' Input Str is now:' + inpstr)
            if inpstr.find("}") != -1 :
                jinput = json.loads(inpstr)
                spindle_name = jinput['name']
                spindle_id = jinput['ID']
                angle = jinput['angle']
                temperature = jinput['temperature']
                battery = jinput['battery']
                try:
                   gravity = jinput['gravity']
                   interval = jinput['interval']
                   rssi = jinput['RSSI']
                except:
                   # older firmwares might not be transmitting all of these
                   dbgprint("Consider updating your iSpindel's Firmware.")
                try:
                    # get user token for connection to ispindle.de public server
                    user_token = jinput['token']
                except:
                    # older firmwares < 5.4 or field not filled in
                    user_token = '*'
                # looks like everything went well :)
                clientsock.send(ACK)
                dbgprint(repr(addr) + ' ' + spindle_name + ' (ID:' + str(spindle_id) + ') : Data received OK.')
                success = 1
                break # close connection
        except Exception as e:
            # something went wrong
            # traceback.print_exc() # this would be too verbose, so let's do this instead:
            dbgprint(repr(addr) + ' Error: ' + str(e))
            clientsock.send(NAK)
            dbgprint(repr(addr) + ' NAK sent.')
            break # close connection server side after non-success
    clientsock.close()
    dbgprint(repr(addr) + " - closed connection") #log on console

    if success:
        # We have the complete spindle data now, so let's make it available
        if CSV:
            dbgprint(repr(addr) + ' - writing CSV')
            try:
                filename = OUTPATH + spindle_name + '_' + str(spindle_id) + '.csv'
                with open(filename, 'a') as csv_file:
                    # this would sort output. But we do not want that...
                    # import csv
                    # csvw = csv.writer(csv_file, delimiter=DELIMITER)
                    # csvw.writerow(jinput.values())
                    outstr = ''
                    if DATETIME == 1:
                        cdt = datetime.now()
                        outstr += cdt.strftime('%x %X') + DELIMITER
                    outstr += str(spindle_name) + DELIMITER
                    outstr += str(spindle_id) + DELIMITER
                    outstr += str(angle) + DELIMITER
                    outstr += str(temperature) + DELIMITER
                    outstr += str(battery) + DELIMITER
                    outstr += str(gravity) + DELIMITER
                    outstr += user_token + DELIMITER
                    outstr += str(interval) + DELIMITER
                    outstr += str(rssi)
                    outstr += NEWLINE
                    csv_file.writelines(outstr)
                    dbgprint(repr(addr) + ' - CSV data written.')
            except Exception as e:
                dbgprint(repr(addr) + ' CSV Error: ' + str(e))

        if SQL:
            try:
                import mysql.connector
                dbgprint(repr(addr) + ' - writing to database')
                # standard field definitions:
                fieldlist = ['Timestamp', 'Name', 'ID', 'Angle', 'Temperature', 'Battery', 'Gravity']
                valuelist = [datetime.now(), spindle_name, spindle_id, angle, temperature, battery, gravity]

                # do we have a user token defined? (Fw > 5.4.x)
                # this is for later use (public server) but if it exists, let's store it for testing purposes
                # this also should ensure compatibility with older fw versions and not-yet updated databases
                if user_token != '':
                    fieldlist.append('UserToken')
                    valuelist.append(user_token)

                # If we have firmware 5.8 or higher:
                if rssi != 0:
                    fieldlist.append('`Interval`') # this is a reserved SQL keyword so it requires additional quotes
                    valuelist.append(interval)
                    fieldlist.append('RSSI')
                    valuelist.append(rssi)

                # establish database connection
                cnx = mysql.connector.connect(user=SQL_USER, password=SQL_PASSWORD, host=SQL_HOST, database=SQL_DB)
                cur = cnx.cursor()

                # add extra columns dynamically?
                # this is kinda ugly; if new columns should persist, make sure you add them to the lists above...
                # for testing purposes it allows to introduce new values of raw data without having to fiddle around.
                # Basically, do not use this unless your name is Sam and you are the firmware developer... ;)
                if ENABLE_ADDCOLS:
                    jinput = json.loads(inpstr)
                    for key in jinput:
                        if not key in fieldlist:
                            dbgprint(repr(addr) + ' - key \'' + key + ' is not yet listed, adding it now...')
                            fieldlist.append(key)
                            value = jinput[key]
                            valuelist.append(value)
                            # crude way to check if it's numeric or a string (we'll handle strings and doubles only)
                            vartype = 'double'
                            try:
                                dummy = float(value)
                            except:
                                vartype = 'varchar(64)'
                            # check if the field exists, if not, add it
                            try:
                                dbgprint(repr(addr) + ' - key \'' + key + '\': adding to database.')
                                sql = 'ALTER TABLE ' + SQL_TABLE + ' ADD ' + key + ' ' + vartype
                                cur.execute(sql)
                            except Exception as e:
                                if e[0] == 1060:
                                    dbgprint(repr(addr) + ' - key \'' + key + '\': exists. Consider adding it to defaults list if you want to keep it.')
                                else:
                                    dbgprint(repr(addr) + ' - key \'' + key + '\': Error: ' + str(e))

                # gather the data now and send it to the database
                fieldstr = ', '.join(fieldlist)
                valuestr = ', '.join(['%s' for x in valuelist])
                add_sql = 'INSERT INTO Data (' + fieldstr + ')'
                add_sql += ' VALUES (' + valuestr + ')'
		dbgprint(add_sql)
		dbgprint(valuelist)
                cur.execute(add_sql, valuelist)
                cnx.commit()
                cur.close()
                cnx.close()
                dbgprint(repr(addr) + ' - DB data written.')
            except Exception as e:
                dbgprint(repr(addr) + ' Database Error: ' + str(e) + NEWLINE + 'Did you update your database?')

        if UBIDOTS:
            try:
                if UBI_USE_ISPINDLE_TOKEN:
                    token = user_token
                else:
                    token = UBI_TOKEN
                if token != '':
                    if token[:1] != '*':
                        dbgprint(repr(addr) + ' - sending to ubidots')
                        import urllib2
                        outdata = {
                            'tilt' : angle,
                            'temperature' : temperature,
                            'battery' : battery,
                            'gravity' : gravity,
                            'interval' : interval,
                            'rssi' : rssi
                        }
                        out = json.dumps(outdata)
                        dbgprint(repr(addr) + ' - sending: ' + out)
                        url = 'http://things.ubidots.com/api/v1.6/devices/' + spindle_name + '?token=' + token
                        req = urllib2.Request(url)
                        req.add_header('Content-Type', 'application/json')
                        req.add_header('User-Agent', spindle_name)
                        response = urllib2.urlopen(req, out)
                        dbgprint(repr(addr) + ' - received: ' + response.read())
            except Exception as e:
                dbgprint(repr(addr) + ' Ubidots Error: ' + str(e))

        if FORWARD:
            try:
                dbgprint(repr(addr) + ' - forwarding to ' + FORWARDADDR)
                outdata = {
                    'name' : spindle_name,
                    'ID' : spindle_id,
                    'angle' : angle,
                    'temperature' : temperature,
                    'battery' : battery,
                    'gravity' : gravity,
                    'token' : user_token,
                    'interval' : interval,
                    'RSSI' : rssi
                }
                out = json.dumps(outdata)
                dbgprint(repr(addr) + ' - sending: ' + out)
                s = socket(AF_INET, SOCK_STREAM)
                s.connect((FORWARDADDR, FORWARDPORT))
                s.send(out)
                rcv = s.recv(BUFF)
                s.close()
                if rcv[0] == ACK :
                    dbgprint(repr(addr) + ' - received ACK - OK!')
                elif rcv[0] == NAK :
                    dbgprint(repr(addr) + ' - received NAK - Not OK...')
                else:
                    dbgprint(repr(addr) + ' - received: ' + rcv)

            except Exception as e:
                dbgprint(repr(addr) + ' Error while forwarding to ' + FORWARDADDR + ': ' + str(e))


def main():
    ADDR = (HOST, PORT)
    serversock = socket(AF_INET, SOCK_STREAM)
    serversock.setsockopt(SOL_SOCKET, SO_REUSEADDR, 1)
    serversock.bind(ADDR)
    serversock.listen(5)
    while 1:
        dbgprint('waiting for connection... listening on port: ' + str(PORT))
        clientsock, addr = serversock.accept()
        dbgprint('...connected from: ' + str(addr))
        thread.start_new_thread(handler, (clientsock, addr))

if __name__ == "__main__":
    main()


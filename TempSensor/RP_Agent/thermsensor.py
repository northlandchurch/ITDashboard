import os
import time
import datetime
import MySQLdb
from decimal import Decimal


os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')
base_dir = '/sys/bus/w1/devices/'
device_id = '28-01192e073924'
device_file = base_dir + device_id + '/w1_slave'
device_name = 'RPi 68:30:7e'
now = datetime.datetime.now()



def read_temp_raw():
    f = open(device_file, 'r')
    lines = f.readlines()
    f.close()
    return lines

def read_temp():
    lines = read_temp_raw()
    while lines[0].strip()[-3:] != 'YES':
        time.sleep(0.2)
        lines = read_temp_raw()
    equals_pos = lines[1].find('t=')
    if equals_pos != -1:
        temp_string = lines[1][equals_pos+2:]
        temp_c = float(temp_string) / 1000.0

#Convert to farenheit. CG 5/12/2020
        temp_f = (temp_c * 1.8) + 32

#Calibrate if necessary. CG 5/14/2020
        temp_calibrated = temp_f + 0

        return temp_calibrated

#Write sensor data to sql. CG 6/1/2020
try:
    db = MySQLdb.connect(host="10.7.0.150", user="admin", passwd="password",db="nacdtempsensor")
    c=db.cursor()
    temp = read_temp()
    dtemp = Decimal(temp)
    ddtemp=round(dtemp, 2)
    print(ddtemp)
    c.execute("insert into activities (deviceid, devicename, createdat, temperature) values ('RPi fe:ca:5d', 'tmp_sensor_4', now($
    db.commit()
    c.close()
except:
    print("An exception has occured.")

#!/usr/bin/env python
import os
import sys

basepath = os.path.abspath(os.path.dirname(__file__))
sys.path.append(os.path.join(basepath, "poster"))

from poster.encode import multipart_encode

def strBin(s_str):
	binary = []
	for s in s_str:
	    if s == ' ':
	        binary.append('00100000')
	    else:
	        binary.append(bin(ord(s)))
	return binary

media = os.path.join(basepath, "Archive", "test.3gp")
body = {"desc": "This is just a test. Please ignore it", "user": "tony2@fuel-it-up.com", "photo": open(media, "rb")}


datagen, headers = multipart_encode(body)

# content = strBin(datagen.next())
# content = "".join(content)
content = datagen.next()

content += datagen.next()

content += datagen.next()

with open(os.path.join(basepath, "newvideobody.dat"), "wb") as body_data:
	body_data.write(content)

print headers

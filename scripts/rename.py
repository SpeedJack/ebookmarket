#!/usr/bin/env python3
"""Rename cover image files"""

import os
import os.path
import mysql.connector

db = mysql.connector.connect(
	host="127.0.0.1",
	user="dbuser",
	password="dbpass",
	database="ebookmarket"
)

cur = db.cursor()
cur.execute("SELECT id, filehandle FROM books")
res = cur.fetchall()

for row in res:
	print(row)
	fname = "../assets/covers/" + str(row[0])
	if os.path.isfile(fname + ".jpg"):
		os.rename(fname + ".jpg", "../assets/covers/" + row[1] + ".jpg")
	elif os.path.isfile(fname + ".png"):
		os.rename(fname + ".png", "../assets/covers/" + row[1] + ".png")
	else:
		print("!! NOT FOUND !!")

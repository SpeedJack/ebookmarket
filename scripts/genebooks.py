#!/usr/bin/env python3
"""Generate ebooks"""

import random
import os
import os.path
import mysql.connector

pdfprob = 1.0
epubprob = 0.5
mobiprob = 0.4

db = mysql.connector.connect(
	host="127.0.0.1",
	user="dbuser",
	password="dbpass",
	database="ebookmarket"
)

cur = db.cursor()
cur.execute("SELECT id, title, author, filehandle FROM books")
res = cur.fetchall()

for row in res:
	print(row)
	title = row[1].replace("'", r"\'")
	author = row[2]
	os.system(r'pdflatex -output-directory ../assets/ebooks/ "\def\coverpath{../assets/covers/} \def\booktitle{' + title + r'} \def\bookauthor{' + author + r'} \def\covername{' + row[3] + r'} \input{ebook}" &> /dev/null')
	if os.path.isfile("../assets/ebooks/ebook.pdf"):
		os.system("rm -f ../assets/ebooks/*{.aux,.log}")
		n = random.random()
		os.rename("../assets/ebooks/ebook.pdf", "../assets/ebooks/" + row[3] + ".pdf")
		if n <= epubprob:
			os.system("ebook-convert ../assets/ebooks/" + row[3] + "{.pdf,.epub} &> /dev/null")
		if n <= mobiprob:
			os.system("ebook-convert ../assets/ebooks/" + row[3] + "{.pdf,.mobi} &> /dev/null")
		if n > pdfprob:
			os.remove("../assets/ebooks/" + row[3] + ".pdf")
	else:
		print("PDFLATEX ERROR !! STOP")
		break

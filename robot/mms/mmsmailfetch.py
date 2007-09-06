#!/usr/bin/python
import poplib
import os
import sys
import email
import errno
import mimetypes
import re
import time

from config import Configure

class FetchGmail:
	def init(self):
		pass
	def login(self,u,p):
		self.pop = poplib.POP3_SSL('pop.gmail.com', 995 )
		self.pop.user(u)
		self.pop.pass_(p)

	def list(self):
		return self.pop.uidl()[1];

	def quit(self):
		return self.pop.quit()

	def retr(self, n):
		try:
			c = self.pop.retr(n)
		except poplib.error_proto:
			return None
		return c[1]

def saveMail(uid, content):
	content = "\r\n".join( content );
	msg = email.message_from_string( content )
	mailfrom = msg.get("From")
	address = None
	#mailfrom = "hehee adsad <+8613955457582@mothamdsad.com>";
	mo = re.search( '(\+86){0,1}(?P<realmail>\d{11})', mailfrom, re.I );
	if mo:
		address = mo.group('realmail')
	else:
		print "Pass Non-MMS mail"
		return
	print "Get MMS From: %s" % (address)
	parseMail( msg, uid, address )


def parseMail(msg, uid, address):
	dirname = "%s/%s-%s" % (Configure.d, uid, address );
	if False == os.path.exists(dirname) : 
		os.makedirs( dirname, 0777 )

	counter = 1
	for part in msg.walk():
		if part.get_content_maintype() == 'multipart':
			continue
		filename = part.get_filename()

		if not filename:
			ext = mimetypes.guess_extension(part.get_content_type())
			if not ext:
				ext = '.bin'
			filename = 'part-%03d%s' % (counter, ext)
		counter += 1
		partfilename = "%s/%s" % ( dirname, filename )

		fp = open( partfilename , 'wb')
		fp.write(part.get_payload(decode=True))
		fp.close()

def loopmail():
	while True:

		fm = FetchGmail();
		fm.login(Configure.u, Configure.p)

		mails = fm.list()
		maillen = len(mails)
		print "Get %d mails from <%s>" % ( maillen , Configure.u )


		for index in range( maillen ):
			pair = mails[index].split()
			order = pair[0]
			uid = pair[1]
			c = fm.retr( order )
			saveMail( uid, c )

		fm.quit()
		time.sleep( Configure.i )

if __name__ == '__main__':
	loopmail()

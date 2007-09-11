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
		if Configure.port == 110 :
			self.pop = poplib.POP3( Configure.host, Configure.port )
		else:
			self.pop = poplib.POP3_SSL( Configure.host, Configure.port )
		self.pop.user(u)
		self.pop.pass_(p)

	def list(self):
		return self.pop.uidl()[1];

	def quit(self):
		return self.pop.quit()
	
	def dele(self, n):
		try:
			self.pop.dele(n)
		except poplib.error_proto:
			pass

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
	dirname = "%s/%s-%s" % (Configure.dir, uid, address );
	if False == os.path.exists(dirname) : 
		os.makedirs( dirname, 0777 )
	
	#store subject
	subject = msg['Subject']
	subjectfilename = '%s/subject.sub' % ( dirname );
	fp = open( subjectfilename , 'wb')
	fp.write( subject )
	fp.close()


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
		fm.login(Configure.user, Configure.pw)

		mails = fm.list()
		maillen = len(mails)
		print "Get %d mails from <%s>" % ( maillen , Configure.user )


		for index in range( maillen ):
			pair = mails[index].split()
			order = pair[0]
			uid = pair[1]
			c = fm.retr( order )
			saveMail( uid, c )
			fm.dele( order )

		fm.quit()
		time.sleep( Configure.interv )

if __name__ == '__main__':
	loopmail()

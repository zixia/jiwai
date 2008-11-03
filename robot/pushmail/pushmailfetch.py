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
	mailto = msg.get("To")
	moAddr = mtAddr = None
	#mailfrom = "hehee adsad <+8613955457582@mothamdsad.com>";
	#http://distro.ibiblio.org/pub/linux/distributions/rpath/raa/raa-1.0.12.tar.bz2
	mo = re.search( '(?P<realmail>(?:(?:(?:[a-zA-Z0-9][\.\-\+_]?)*)[a-zA-Z0-9])+@((?:(?:(?:[a-zA-Z0-9][\.\-_]?){0,62})[a-zA-Z0-9])+)\.([a-zA-Z0-9]{2,6}))', mailfrom, re.I );
	mt = re.search( '(?P<realmail>(?:(?:(?:[a-zA-Z0-9][\.\-\+_]?)*)[a-zA-Z0-9])+@((?:(?:(?:[a-zA-Z0-9][\.\-_]?){0,62})[a-zA-Z0-9])+)\.([a-zA-Z0-9]{2,6}))', mailto, re.I );
	if mo and mt:
		moAddr = mo.group('realmail')
		mtAddr = mt.group('realmail')
	else:
		print "Malformed Email Address"
		return
	print "Get Email From: %s" % (moAddr)
	parseMail( msg, uid, moAddr, mtAddr )


def parseMail(msg, uid, mo, mt):
	dirname = "%s/%s-%s-%s" % (Configure.dir, uid, mo, mt);
	if False == os.path.exists(dirname) : 
		os.makedirs( dirname, 0777 )
	
	#store subject
	subject = msg['Subject']
	if subject != None:
		subjectfilename = '%s/subject.sub' % ( dirname );
		fp = open( subjectfilename , 'wb')
		fp.write( subject )
		fp.close()

	#store Date
	mailtime = msg['Date']
	if mailtime != None:
		mailtimefilename = '%s/mail.tim' % ( dirname );
		fp = open( mailtimefilename , 'wb')
		fp.write( mailtime )
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

		try:
			fp = open( partfilename , 'wb')
			fp.write(part.get_payload(decode=True))
			fp.close()
		except IOError:
			print "Malformed partfilename: %s" %(partfilename)

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

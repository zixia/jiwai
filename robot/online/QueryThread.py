import sys
import os
import time
import re
import urllib
import socket
from threading import Thread, Timer
from Configure import Configure

def qqOnlineTest( address ):
	f = urllib.urlopen('http://wpa.qq.com/pa?p=1:%s:3' % (address,) )
	if f.geturl().find( 'online' ) > 0 :
		return True, time.time();
	return False, time.time()

def gtalkOnlineTest( address ):
	try:
		print address;
		s = socket.socket( socket.AF_INET, socket.SOCK_STREAM )
		host = "localhost"
		port = 55010
		s.connect( (host, port) )
		s.send( "%s\r\n" % (address) )
		data = s.recv(10)
		if  re.match( "^y" , data , re.I ) :
			return True, time.time()
		else:
			return False, time.time()
	except socket.error, msg:
		return False, time.time()



class QueryThread:
	def __init__(self, sock, device, address):
		self.sock = sock
		self.device = device
		self.address = address

	def run(self):
		if self.device == "qq" :
			if self.address.isdigit() :
				if False == Configure.qqOnline.has_key( self.address ):
					Configure.qqOnline[ self.address ] = qqOnlineTest( self.address )
				else:
					if Configure.qqOnline[ self.address ][1] - time.time() > Configure.delay:
						Configure.qqOnline[ self.address ] = qqOnlineTest( self.address )
				status = Configure.qqOnline[ self.address ];
			else:
				status = ( False, 0 )
		if self.device == "gtalk" :
			if False == Configure.gtalkOnline.has_key( self.address ):
				Configure.gtalkOnline[ self.address ] = gtalkOnlineTest( self.address )
			else:
				if Configure.gtalkOnline[ self.address ][1] - time.time() > Configure.delay:
					Configure.gtalkOnline[ self.address ] = gtalkOnlineTest( self.address )
			status = Configure.gtalkOnline[ self.address ];

		if True == status[0] :
			self.sock.send( 'Y' )
		else :
			self.sock.send( 'N' )

		self.sock.close()

import sys
import os
import time
import re
import urllib
import socket
from threading import Thread, Timer
from Configure import Configure

def qqOnlineTest_20070731( address ):
	f = urllib.urlopen('http://wpa.qq.com/pa?p=1:%s:3' % (address,) )
	if f.geturl().find( 'online' ) > 0 :
		return True, time.time();
	return False, time.time()

def qqOnlineTest( address ):
	try:
		s = socket.socket( socket.AF_INET, socket.SOCK_STREAM )
		host = "localhost"
		port = 55020
		s.connect( (host, port) )
		s.send( "%s\r\n" % (address) )
		data = s.recv(10)
		if  re.match( "^y" , data , re.I ) :
			return True, time.time()
		else:
			return False, time.time()
	except socket.error, msg:
		return False, time.time()

def msnOnlineTest( address ):
	f = urllib.urlopen('http://osi.hshh.org:8888/msn/%s' % (address,) )
	if f.geturl().find( 'online' ) > 0 :
		return True, time.time();
	return False, time.time()

def gtalkOnlineTest( address ):
	try:
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
	
	def getStatus(self, set, func):
		if False == set.has_key( self.address ):
			return func( self.address )
		else:
			if time.time() - set[ self.address ][1] > Configure.delay:
				return func( self.address )
		return set[ self.address ];

	def run(self):
		if self.device == "qq" :
			if self.address.isdigit() :
				status = self.getStatus( Configure.qqOnline, qqOnlineTest )
				Configure.qqOnline[ self.address ] = status
			else:
				status = ( False, 0 )
	 	elif self.device == "gtalk" :
			status = self.getStatus( Configure.gtalkOnline, gtalkOnlineTest )
			Configure.gtalkOnline[ self.address ] = status
		elif self.device == "msn" :
			status = self.getStatus( Configure.msnOnline, msnOnlineTest )
			Configure.msnOnline[ self.address ] = status
		else :
		     	status = False, 0 

		if True == status[0] :
			self.sock.send( 'Y' )
		else :
			self.sock.send( 'N' )

		self.sock.close()

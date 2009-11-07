# coding:utf-8

import time
import socket
import Queue
import threading
import spread
import re
import os
try: import cPickle as pickle
except: import pickle
try: import simplejson as json
except: import json

class spworker(threading.Thread):
	def __init__(self):
		threading.Thread.__init__(self)
	def run(self):
		while True:
			try:
				data = json.loads( spqueue.get(True) )
				follower_lock.acquire()

				id = str(data['id'])
				idUser = str(data['idUser'])
				idFollower = str(data['idFollower'])
				action = str(data['action'])
				now = int(time.time())

				if action == 'create':
					follower.setdefault(idUser,set()).add(idFollower)
					following.setdefault(idFollower,set()).add(idUser)
				elif action == 'destroy':
					follower.setdefault(idUser,set()).discard(idFollower)
					following.setdefault(idFollower,set()).discard(idUser)
				pass

				follower_lock.release()
			except Exception,e: pass
		pass
			
def process(line):
	try:
		cmd, idUser = line.lower().split(' ',1)[0:2]
		if cmd == 'followme':
			return list(follower.get(idUser, set()))
		elif cmd == 'mefollow':
			return list(following.get(idUser, set()))
		pass
	except: pass
	return None
	

class scworker(threading.Thread):
	def __init__(self, sock):
		threading.Thread.__init__(self)
		self.sock = sock
	def run(self):
		line = self.sock.recv(8192).strip()
		r = process(line)
		if r is not None:
			self.sock.send(json.dumps(r)+'\r\n')
		try: self.sock.close()
		except: pass


class service_thread(threading.Thread):
	def __init__(self):
		threading.Thread.__init__(self)
	def run(self):
		import tornado
		def ioprocess(sock):
			def on_data(data):
				r = process(data)
				if r is not None: stream.write(json.dumps(r)+'\r\n')
			sock.setblocking(0)
			stream = tornado.iostream.IOStream(sock)
			stream.read_until("\r\n", on_data)
			
		def connection_ready(sock, fd, events):
			#ioprocess(sock.accept()[0])
			scworker(sock.accept()[0]).start()

		print "follower service"
		print "listen (0.0.0.0:4002) for followers db"
		print "example: telnet localhost 4002, followme/mefollow 89"
		sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM, 0)
		sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
		sock.setblocking(0)
		sock.bind(('', 4002))
		sock.listen(128)
		io_loop = tornado.ioloop.IOLoop.instance()
		callback = partial(connection_ready, sock)
		io_loop.add_handler(sock.fileno(), callback, io_loop.READ)
		io_loop.start()		

def cache_get(key):
	if type(key) is str: lkey = [key]
	else: lkey = [str(x) for x in key]
	hkey = hash(repr(lkey))
	return cache.get(hkey, (None,None,None))[1]

def cache_mod(idUser=None):
	mintime = int(time.time()) - 1800 #cache 30min
	for h, (k,v,t) in cache.items():
		if idUser in k or t < mintime or 'public' in k: del cache[h]

def cache_set(key, value):
	if type(key) is str: lkey = [key]
	else: lkey = [str(x) for x in key]
	hkey = hash(repr(lkey))
	cache[hkey] = (lkey, value, int(time.time()))

def load():
	global follower
	global following
	r = file('db.txt')
	follower = {}
	following = {}
	for q in r:
		idUser, idFollower  = str(q.strip()).split('\t');
		follower.setdefault(idUser,set()).add(idFollower)
		following.setdefault(idFollower,set()).add(idUser)
		
spqueue = Queue.Queue()
scqueue = Queue.Queue()

follower = {}
following = {}
follower_lock = threading.Lock()
cache = {} #for 30min

regcomma = re.compile('[\s,]+')

def partial(func, *args, **keywords):
	def newfunc(*fargs, **fkeywords):
		newkeywords = keywords.copy()
		newkeywords.update(fkeywords)
		return func(*(args + fargs), **newkeywords)
	newfunc.func = func
	newfunc.args = args
	newfunc.keywords = keywords
	return newfunc


def main():
	sp = spread.Spread(str(os.getpid()), '4803@localhost')
	sp.connect()
	sp.join(['/create/follower', '/destroy/follower'])
	load();
	service_thread().start()
	for x in range(10): spworker().start()
	while True: spqueue.put(sp.receive())
	sp.leave()
	sp.disconnect()

if __name__ == '__main__':
	main()

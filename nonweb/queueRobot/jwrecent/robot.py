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
	'''data(time, id, idUser, idUserReplyTo, idThread)'''
	def __init__(self):
		threading.Thread.__init__(self)
	def run(self):
		while True:
			try:
				data = json.loads( spqueue.get(True) )
				recent_lock.acquire()
				recent.insert(0, (int(time.time()), data['metaInfo']['idStatus'], int(data['idUser']), data['idUserReplyTo'], data['metaInfo']['options']['idThread']))
				cache_mod(data['idUser'])
				recent_lock.release()
			except Exception,e: pass
		pass
			

class scworker(threading.Thread):
	def __init__(self, sock):
		threading.Thread.__init__(self)
		self.sock = sock
	def run(self):
		line = self.sock.recv(8192).strip()
		try:
			cmd, param = line.split(' ',1)[0:2]
			if cmd.upper() == 'GET':
				idlist = regcomma.split(param)
				r = cache_get(idlist)
				if r is None:
					r = [(x[1],x[2]) for x in recent if str(x[2]) in idlist]
					r.sort(reverse=True)
					cache_set(idlist, r)
				self.sock.send(json.dumps(r)+'\r\n')
			elif cmd.upper() == 'PUBLIC':
				r = cache_get('public')
				if r is None:
					num = int(param)
					r = recent[0:num]
					r = [(x[1],x[2]) for x in r]
					r.sort(reverse=True)
					cache_set('public', r)
				self.sock.send(json.dumps(r)+'\r\n')
			elif cmd.upper() == 'TEST':
				self.sock.send('TEST BACK\r\n')
			pass
		except: pass
		try: self.sock.close()
		except: pass


class service_thread(threading.Thread):
	def __init__(self):
		threading.Thread.__init__(self)
	def run(self):
		import tornado
		def process(sock):
			sock.setblocking(0)
			stream = tornado.iostream.IOStream(sock)
			def on_data(data):
				cmd, param = data.strip().split(' ',1)
				if cmd.upper() == 'GET':
					idlist = regcomma.split(param)
					r = [x[1] for x in recent if str(x[2]) in idlist]
					r.sort(reverse=True)
					r = ','.join([str(x) for x in r])
					stream.write(r+'\r\n')
				elif cmd.upper() == 'TEST':
					stream.send('TEST BACK\r\n')
				pass
			stream.read_until("\r\n", on_data)
			
		def connection_ready(sock, fd, events):
			#process(sock.accept()[0])
			scworker(sock.accept()[0]).start()

		print "recent jiwai service, when reboot, data will be lost"
		print "listen (0.0.0.0:4001) for updates in 24hour"
		print "query example: telnet localhost 4001, GET 89,2802,32529"
		sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM, 0)
		sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
		sock.setblocking(0)
		sock.bind(('', 4001))
		sock.listen(128)
		io_loop = tornado.ioloop.IOLoop.instance()
		callback = partial(connection_ready, sock)
		io_loop.add_handler(sock.fileno(), callback, io_loop.READ)
		io_loop.start()		

def cache_get(key):
	if type(key) is str: key = [key]
	lkey = [str(x) for x in key]
	hkey = hash(repr(lkey))
	return cache.get( hkey, (None,None,None))[1]

def cache_mod(idUser=None):
	mintime = int(time.time()) - 1800 #cache 30min
	idUser = str(idUser)
	for h, (k,v,t) in cache.items():
		if idUser in k or t < mintime: 
			del cache[h]

def cache_set(key, value):
	if type(key) is str: key = [key]
	lkey = [str(x) for x in key]
	hkey = hash(repr(lkey))
	cache[hkey] = (key, value, int(time.time()))

def load():
	global recent
	if os.path.exists('jwrecent.db'): recent = pickle.load(file('jwrecent.db'))
		
def clear(delay=1800):
	global recent
	mintime = int(time.time() - 86400)
	recent_lock.acquire()
	recent = [x for x in recent if x[0] > mintime]
	pickle.dump(recent, file('jwrecent.db', 'w+'), 1)
	recent_lock.release()
	print "clear expired list at:", mintime
	threading.Timer(delay, clear, [delay]).start()

spqueue = Queue.Queue()
scqueue = Queue.Queue()
recent = []
recent_lock = threading.Lock()
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
	sp = spread.Spread('jwrecent', '4803@localhost')
	sp.connect()
	sp.join(['/statuses/update'])
	load();
	clear(600)
	service_thread().start()
	for x in range(10): spworker().start()
	#for x in range(10): scworker().start()
	while True: spqueue.put(sp.receive())
	sp.leave()
	sp.disconnect()

if __name__ == '__main__':
	main()

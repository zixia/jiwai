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
		global public
		while True:
			try:
				data = json.loads( spqueue.get(True) )
				recent_lock.acquire()

				id = str(data['id'])
				idUser = str(data['idUser'])
				idThread = str(data['idThread'])
				action = str(data['action'])
				now = int(time.time())

				if action == 'create':
					item = (now, id, idUser, idThread)
					recent.insert(0, item)
					recentdict.setdefault(idUser,[]).append(item)
					if data['idPicture']: 
						public.insert(0, (item))
						public = public[0:1000]
				elif action == 'destroy':
					remove.append((id, idUser))
				pass

				cache_mod(idUser)
				recent_lock.release()
			except Exception,e: pass
		pass
			
def process(line):
	try:
		cmd, param = line.lower().split(' ',1)[0:2]
		if cmd == 'get':
			idlist = regcomma.split(param)
			r = cache_get(idlist)
			if r is None:
				r = []
				for k in recentdict.keys():
					if str(k) in idlist: r += recentdict[k]
				r = [(x[1],x[2]) for x in r]
				r = [x for x in r if x not in remove]
				r.sort(reverse=True)
				cache_set(idlist, r)
			return r
		elif cmd == 'public':
			num = int(param)
			r = public[0:num]
			r = [(x[1],x[2]) for x in r]
			r = [x for x in r if x not in remove]
			r.sort(reverse=True)
			return r
		elif cmd == 'rpublic':
			num = int(param)
			r = cache_get([cmd, param])
			if r is None:
				r = recent[0:num]
				r = [(x[1],x[2]) for x in r]
				r = [x for x in r if x not in remove]
				r.sort(reverse=True)
				cache_set([cmd, param], r)
			return r
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
	global recent
	global public
	global recentdict
	if os.path.exists('jwrecent.db'): 
		recent = pickle.load(file('jwrecent.db'))
		public = recent[0:1000]
		for x in recent: recentdict.setdefault(str(x[2]),[]).append(x)
		
def clear(delay=1800):
	global recent
	global recentdict
	global remove
	mintime = int(time.time() - 86400)
	recent_lock.acquire()

	recent = [x for x in recent if x[0]>mintime and (x[1],x[2]) not in remove]
	pickle.dump(recent, file('jwrecent.db', 'w+'), 1)
	recentdict = {}
	remove = []
	for x in recent: recentdict.setdefault(str(x[2]),[]).append(x)

	recent_lock.release()
	print "clear expired list at:", mintime
	threading.Timer(delay, clear, [delay]).start()

spqueue = Queue.Queue()
scqueue = Queue.Queue()

recent = []
recent_lock = threading.Lock()
recentdict = {}
public = []
remove = []
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
	sp.join(['/create/status', '/destroy/status'])
	load();
	clear(600)
	service_thread().start()
	for x in range(10): spworker().start()
	while True: spqueue.put(sp.receive())
	sp.leave()
	sp.disconnect()

if __name__ == '__main__':
	main()

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
				activate_lock.acquire()

				id = str(data['id'])
				now = int(time.time())
				activate[id] = now

				activate_lock.release()
			except Exception,e: pass
		pass
			
def process(line):
	try:
		x = str(line.lower()).split(' ',2)[0:3]
		if x[0] == 'get':
			num = int(x[1])
			idlist = regcomma.split(x[2])
			u = [(activate.get(x,0), x) for x in idlist]
			u.sort(reverse=True)
			return [x for t,x in u[0:num]]
		elif x[0] == 'stat':
			return len(activate)
		pass
	except Exception,e: print e
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

		print "activate jiwai service, when reboot, data will be lost"
		print "listen (0.0.0.0:4003) for activate user"
		print "query example: telnet localhost 4003, GET 2 89,2802,32529"
		sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM, 0)
		sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
		sock.setblocking(0)
		sock.bind(('', 4003))
		sock.listen(128)
		io_loop = tornado.ioloop.IOLoop.instance()
		callback = partial(connection_ready, sock)
		io_loop.add_handler(sock.fileno(), callback, io_loop.READ)
		io_loop.start()		

def load():
	global activate
	if os.path.exists('jwactivate.db'): 
		activate = pickle.load(file('jwactivate.db'))
		
def clear(delay=1800):
	mintime = time.time() - 30*86400
	global activate
	activate_lock.acquire()
	for k in activate.keys():
		if activate[k] < mintime: del activate[k]
	pickle.dump(activate, file('jwactivate.db', 'w+'), 1)
	activate_lock.release()
	print "clear expired list at:", int(time.time())
	threading.Timer(delay, clear, [delay]).start()

spqueue = Queue.Queue()
scqueue = Queue.Queue()

activate = {}
activate_lock = threading.Lock()
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
	sp.join(['/activate/user'])
	load();
	clear(7200)
	service_thread().start()
	for x in range(10): spworker().start()
	while True: spqueue.put(sp.receive())
	sp.leave()
	sp.disconnect()

if __name__ == '__main__':
	main()

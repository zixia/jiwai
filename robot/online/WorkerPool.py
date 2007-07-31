import thread

def worker(pool):
	while True:
		task = pool.getTask()
		if task:
			task.run()

class WorkerPool:
	def __init__(self, n=10):
		self.queue = []
		self.lock = thread.allocate_lock() 
		for i in range(n):
			thread.start_new_thread( worker, (self,) ) 
	def addTask(self,task):
		self.lock.acquire()
		self.queue.append ( task )
		self.lock.release()

	def getTask(self):
		self.lock.acquire()
		if len(self.queue) == 0 :
			r = None
		else:
		     	r = self.queue.pop(0)
		self.lock.release()
		return r

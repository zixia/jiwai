#!/usr/bin/env python
# coding=UTF-8
#
#	2007-05-19
#	zixia@zixia.net
#	AKA Inc. http://aka.cn
#
#############################################################################################


#i18n process
import sys
sys.path.insert(0, './Jabber') 

import socket
import jabber
import xmlstream
import time
import random
import traceback
import urllib
import os.path

import dircache
import re
import time
import os



from dict4ini import DictIni

IM_QUEUE='/var/cache/tmpfs/jiwai/queue/'
GTALK_QUEUE=IM_QUEUE + 'gtalk/'

# 程序没事干的时候发呆的毫秒数
IDLE_CIRCLE			= 1
IDLE_CIRCLE_MAX		= 256


con = None

def jiwai_queue_mt():
	global IDLE_CIRCLE, IDLE_CIRCLE_MAX, con

	if not con:
		return []

	sys.stderr.write(".")

	MAX_RETURN = 100

	mt_queue_dir = GTALK_QUEUE + 'mt/';

	file_list = os.listdir(mt_queue_dir)

	MTs = [];

	#print "MTs in mt: ", MTs
	counter = 0;

	for file in file_list:
		if re.match('^gtalk_', file, re.I)==None :
			continue
		
		file_path = mt_queue_dir + file

		print "processing: %s" %file_path

		email 	= ""
		msg		= ""

		try :
			f = open(file_path,'rb')

			file_content = f.read()
			f.close()
			
			matches = re.match('(.+?)\n\n(.+)',file_content,re.S)
			if matches != None :
				[head,body] = matches.group(1,2);

				matches = re.match('^ADDRESS: gtalk://(\S+)',head,re.I)
 				if matches != None :
					email = matches.group(1)

				mt = (email,body, file_path)
				MTs.append(mt);

				counter = counter+1
				if counter > MAX_RETURN :
					break
		except IOError, (errno, strerror):
			print "I/O error(%s): %s" % (errno, strerror)
		except ValueError:
			print "Could not convert data to an integer."
		except:
			print "open file: %s exception" %file_path
			print "Unexpected error:", sys.exc_info()[0]
			raise


	if 0==counter :
		if IDLE_CIRCLE > IDLE_CIRCLE_MAX :
			IDLE_CIRCLE = IDLE_CIRCLE_MAX
		else :
			IDLE_CIRCLE += 1;
			IDLE_CIRCLE *= 2;
	else :
		IDLE_CIRCLE = 0

	return MTs


def jiwai_queue_mo(email,msg) :
	if None==con :
		return

	if None==email or None==msg :
		return

	current_time = '%f' %(time.time())
#	print "curr: ", current_time
	matches = re.match("(\d+)\.(\d+)",current_time)
	(s, usec) = matches.group(1,2)
	queue_file = "%smo/gtalk__%s__%s_%s" %(GTALK_QUEUE, email, s,usec)

	while os.path.exists(queue_file) :
		current_time = '%f' %(time.time())
		matches = re.match("(\d+)\.(\d+)",current_time)
		(s, usec) = matches.group(1,2)
		queue_file = "%smo/gtalk__%s__%s_%s" %(GTALK_QUEUE, email, s,usec)

	print "writing to ", queue_file
	f=open(queue_file, 'w')
	if f :
		f.write( "ADDRESS: gtalk://%s\n\n%s" %(email,msg) )
		f.close()
	else :
		print >>sys.stderr, "open file ", queue_file
		return False

	return True;


def check_queue() :
	MTs = jiwai_queue_mt()

	#print "MTs: ", MTs
	#print "len: ", len(MTs)

	if 0<len(MTs) :
		for mt in MTs :
			#print "in for: ", mt
			(email,msg,file) = mt
			print "mt: %s of %s" %(email,file)
			sendtoone(email, msg)
			#print "try to remove file: %s" %file
			os.remove(file)


#####################################################################
#a	JiWai Directory Functions Above
#####################################################################

conf = None	#global config object
welcome = """Welcome to ConferenceBot %(version)s
By Isomer (Perry Lorier) and Limodou
This conference bot is set up to allow groups of people to chat.
")help" to list commands, ")quit" to quit
")lang en" for English, and ")lang zh_CN" for Chinese"""

xmllogf = open("xmpp.log","w")
#xmllogf = sys.stderr
lastlog = []

class ADMIN_COMMAND(Exception):pass
class MSG_COMMAND(Exception):pass
class NOMAN_COMMAND(Exception):pass
class RECONNECT_COMMAND(Exception):pass

def getdisplayname(x):
	"Changes a user@domain/resource to a displayable nick (user)"
	server = conf.general['server']
	x=unicode(x)
	if '/' in x:
		x = x[:x.find("/")]
	if '@' in x and x[x.find('@'):] == "@" + server:
		x = x[:x.find("@")]
	return x

def getjid(x):
	"returns a full jid from a display name"
	server = conf.general['server']
	x = getdisplayname(x)
	if '@' not in x:
		x = x + "@" + server
	return x

def sendtoone(who, msg):
	#print "%s: %s" %(who,msg)

	m = jabber.Message(getjid(who), msg)
	m.setFrom(JID)
	if conf.general.debug > 1:
		print '...Begin....................', who
	con.send(m)
#	time.sleep(.1)

def sendtoall(msg,butnot=[],including=[]):
	global lastlog
	r = con.getRoster()
	print >>logf,time.strftime("%Y-%m-%d %H:%M:%S"), msg.encode("utf-8")
	logf.flush()
	if conf.general.debug:
		try:
			print time.strftime("%Y-%m-%d %H:%M:%S"), msg.encode(locale.getdefaultlocale()[1])
		except:
			print time.strftime("%Y-%m-%d %H:%M:%S"), msg.encode('utf-8')
	for i in r.getJIDs():
		state=r.isOnline(i)
		# zixia
		print "state: %s, show: %s, GID: %s" %(state, r.getShow(i), i)
		# if r.isOnline(i) and r.getShow(i) in ['available','chat','online',None]:
		#	pass
		sendtoone(i, msg)
		#sendtoone("zixia@zixia.net", msg)
		lastlog.append(msg)
	if len(lastlog)>5:
		lastlog=lastlog[1:]
	
statuses={}
suppressing=1

def boot(jid):
	"Remove a user from the chatroom"
	con.send(jabber.Presence(to=jid, type='unsubscribe'))
	con.send(jabber.Presence(to=jid, type='unsubscribed'))
#	con.removeRosterItem(jid)

def acmd_invite(who, msg):
	'"/invite nick" Invite someone to join this room'
	msg = msg.strip()
	jid = getjid(msg)
	if isadmin(who.getStripped()):
		if msg:
			con.send(jabber.Presence(to=jid, type='subscribe'))
			systoone(who, 'Invited <%s>'.para(jid))
		else:
			raise MSG_COMMAND
	else:
		raise ADMIN_COMMAND

def sendpresence(msg):
	p = jabber.Presence()
	p.setStatus(msg)
	# 让机器人 "唠叨" !
	p.setShow('chat')
	con.send(p)
	
def messageCB(con,msg):
	global ontesting
	whoid = getjid(msg.getFrom())
	if conf.general.debug > 2:
		try:
			print '>>>', time.strftime('%Y-%m-%d %H:%M:%S'), '[MESSAGE]', unicode(msg).encode(locale.getdefaultlocale()[1])
		except:
			print '>>>', time.strftime('%Y-%m-%d %H:%M:%S'), '[MESSAGE]', unicode(msg).encode('utf-8')

	print ">>> zixia: messageCB %s %s %s" %(msg.getError(), msg.getFrom(), msg.getBody())

	if msg.getError()!=None:
		if conf.general.debug > 2:
			try:
				print '>>> [ERROR]', unicode(msg).encode(locale.getdefaultlocale()[1])
			except:
				print '>>> [ERROR]', unicode(msg).encode('utf-8')
	elif msg.getBody():
		#check quality
		if msg.getFrom().getStripped() == getjid(JID):
			body = msg.getBody()
			if body and body[0] == 'Q':
				ontesting = False
				t = int(body[1:].split(':', 1)[0])
				t1 = int(time.time())
				if t1 - t > reconnectime:
					if conf.general.debug > 1:
						print '>>>', time.strftime('%Y-%m-%d %H:%M:%S'), 'RECONNECT... network delay it too long: %d\'s' % (t1-t)
					raise RECONNECT_COMMAND
			xmllogf.flush()
			return

		userjid[whoid] = unicode(msg.getFrom())
		if len(msg.getBody())>1024:
			pass
		else:
#				systoone(msg.getFrom().getStripped(), _('Warning: Because you set "away" flag, so you can not receive and send any message from this bot, until you reset using "/away" command'))
#				xmllogf.flush()
#				return
			global suppressing
			suppressing=0
# zixia
			jiwai_queue_mo(getdisplayname(msg.getFrom()),msg.getBody())

#			sendtoall('<%s> %s' % (getdisplayname(msg.getFrom()),msg.getBody()),
#				butnot=[getdisplayname(msg.getFrom())],
#				)
#			if con.getRoster().getShow(msg.getFrom().getStripped()) not in ['available','chat','online',None]:
#				systoone(msg.getFrom(), 'Warning: You are marked as "busy" in your client,\nyou will not see other people talk,\nset yourself "available" in your client to see their replies.')
#	xmllogf.flush() # just so flushes happen regularly


def presenceCB(con,prs):
	if conf.general.debug > 3:
		print '>>>', time.strftime('%Y-%m-%d %H:%M:%S'), '[PRESENCE]', prs
	who = unicode(prs.getFrom())
	whoid = getjid(who)
	type = prs.getType()

	print ">>> presenceCB ",who,type

	# TODO: Try only acking their subscription when they ack ours.
	if type == 'subscribe':
		print ">>> Subscribe from",whoid,
		print "Accepted"
		con.send(jabber.Presence(to=who, type='subscribed'))

		# we try to not subscribe him. is there has a max num?
		#con.send(jabber.Presence(to=who, type='subscribe'))

	elif type == 'unsubscribe':
		boot(prs.getFrom().getStripped())
		print ">>> Unsubscribe from",who
	elif type == 'subscribed':
		print ">>> SubscribED from",prs.getFrom().getStripped()
	elif type == 'unsubscribed':
		boot(prs.getFrom().getStripped())
		print ">>> UnsubscribED from",prs.getFrom().getStripped()
	elif type == 'available' or type == None:
		show = prs.getShow()
		if show in [None,'chat','available','online']:
			pass
		elif show in ['xa']:
			pass
		elif show in ['away']:
			pass
		elif show in ['dnd']:
			pass
		else:
			pass
	elif type == 'unavailable':
		pass
	else:
		if conf.general.debug > 3:
			print ">>> Unknown presence:",who,type


def iqCB(con,iq):
	# reply to all IQ's with an error
	reply=None

	print ">>> iqCB",iq.getFrom()

	try:
		# Google are bad bad people
		# they don't put their query inside a <query> in <iq>
		reply=jabber.Iq(to=iq.getFrom(),type='error')
		stuff=iq._node.getChildren()
		for i in stuff:
			reply._node.insertNode(i)
		reply.setError('501', 'Feature not implemented')
		con.send(reply)
	except:
		traceback.print_exc()

def disconnectedCB(con):
	#sys.exit(1)
	raise RECONNECT_COMMAND

def readoptionorprompt(section, option, description):
	"Read an option from the general section of the config, or prompt for it"
	val = conf[section].get(option)
	if not val:
		print description,
		conf[section][option] = raw_input()
	
def readconfig():
	global conf

	conf = DictIni()
	
	#general config
	conf.general.server = 'jiwai.de'
	conf.general.resource = 'chat'
	conf.general.private = 0
	conf.general.hide_status = 0
	conf.general.debug = 1
	conf.general.configencoding = 'utf-8'
	conf.general.sysprompt = '***'
	conf.general.logpath = ''
	conf.general.language = ''
	conf.general.logfileformat = '%Y%m%d'
	conf.general.status = 'Ready'
	
	
	if len(sys.argv)>1:
		conf.setfilename(sys.argv[1])
		conf.read(sys.argv[1])
	else:
		conf.setfilename("gtalk.ini")
		conf.read("gtalk.ini")
		
	
	#encoding convert
	encoding = conf.general.configencoding
	conf.general.topic = conf.general.topic
	conf.general.status = conf.general.status
	for key, value in conf.emotes.items():
		conf.emotes[key] = value

def connect():
	global con
	debug = conf.general.debug
	
	print ">>> Connecting"
	general = conf.general
	if debug:
		print '>>> debug is [%d]' % general['debug']
		print '>>> host is [%s]' %general['server']
		print '>>> account is [%s]' % general['account']
		print '>>> resource is [%s]' % general['resource']
	con = jabber.Client(host=general['server'],debug=False ,log=xmllogf,
						port=5223, connection=xmlstream.TCP_SSL)
	print ">>> Logging in"
	con.connect()
	con.setMessageHandler(messageCB)
	con.setPresenceHandler(presenceCB)
	con.setIqHandler(iqCB)
	con.setDisconnectHandler(disconnectedCB)
	con.auth(general['account'], general['password'], general['resource'])
	con.requestRoster()
	con.sendInitPresence()
	r = con.getRoster()

	for i in r.getJIDs():
		print(getdisplayname(i))
			
	sendpresence(conf.general['status'])
	print ">>> Online!"
	print >>logf, 'The bot is started!', time.strftime('%Y-%m-%d %H:%M:%S')


readconfig()

#set system default encoding to support unicode
reload(sys)
sys.setdefaultencoding('utf-8')

#make command list
commands = {}
acommands = {}
import types
for i, func in globals().items():
	if isinstance(func, types.FunctionType):
		if i.startswith('cmd_'):
			commands[i.lower()[4:]] = func
		elif i.startswith('acmd_'):
			acommands[i.lower()[5:]] = func

general = conf.general

#logfile process
logf = file(os.path.join(general['logpath'], time.strftime(general['logfileformat']) + '.log'), "a+")

con = None
JID="%s@%s/%s" % (general['account'], general['server'], general['resource'])
last_update=(time.time()-4*60*60)+60 # Send the update in 60 seconds
last_ping=0
last_testing=0
userjid = {}	#saving real jid just like "xxx@gmail.com/gtalkxxxxx"
reconnectime = 30	#network delay exceed this time, so the bot need to reconnect

ontesting = False

while 1:
	try:
		#create new log file as next day
		general = conf.general
		logfile = os.path.join(general['logpath'], time.strftime(general['logfileformat']) + '.log')
		if not os.path.exists(logfile):
			logf = file(logfile, "a+")
			
		if not con:
			connect()
		# Send some kind of dummy message every few minutes to make
		# sure that the connection is still up, and to tell google talk
		# we're still here.
		if time.time()-last_ping>120: # every 2 minutes
			# Say we're online.
			p = jabber.Presence()
			p.setFrom(JID)
			p.setShow('chat')
			con.send(p)
			sendpresence(conf.general['status'])
			last_ping = time.time()

		if time.time()-last_testing>120: # every 40 seconds
			#test quality
			if ontesting:	#mean that callback message doesn't be processed, so reconnect again
				print '>>>', time.strftime('%Y-%m-%d %H:%M:%S'), 'RECONNECT... network delay it too long: %d\'s' % (time.time()-last_testing)
				raise RECONNECT_COMMAND
			else:
				ontesting = True
				m = jabber.Message(to=JID, body='Q' + str(int(time.time())) + ':' + time.strftime('%Y-%m-%d %H:%M:%S'))
				con.send(m)
				if conf.general.debug > 1:
					print '>>> Quality testing...', time.strftime('%Y-%m-%d %H:%M:%S')
				last_testing = time.time()

		wait_sec = float(IDLE_CIRCLE) / 1000
		con.process(wait_sec)

		check_queue()

	except KeyboardInterrupt:
		break
	except SystemExit:
		break
	except RECONNECT_COMMAND:
		con = None
		ontesting = False
		last_testing = 0
		last_ping = 0
	except:
		traceback.print_exc()
		time.sleep(1)
		con = None

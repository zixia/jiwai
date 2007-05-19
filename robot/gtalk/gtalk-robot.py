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
import locale


from dict4ini import DictIni

conf = None	#global config object
welcome = """Welcome to ConferenceBot %(version)s
By Isomer (Perry Lorier) and Limodou
This conference bot is set up to allow groups of people to chat.
")help" to list commands, ")quit" to quit
")lang en" for English, and ")lang zh_CN" for Chinese"""

xmllogf = open("xmpp.log","w")
last_activity=time.time()
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
	print "%s: %s" %(who,msg)

	m = jabber.Message(getjid(who), msg)
	m.setFrom(JID)
	m.setType('chat')
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
		if r.isOnline(i) and r.getShow(i) in ['available','chat','online',None]:
			sendtoone(i, msg)
	if not msg.startswith(conf.general['sysprompt']):
		lastlog.append(msg)
	if len(lastlog)>5:
		lastlog=lastlog[1:]
		
def sendtoadmin(msg,butnot=[],including=[]):
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
		if not isadmin(i): continue
		if getdisplayname(i) in butnot:
			continue
		state=r.getShow(unicode(i))
		if has_userflag(getdisplayname(i), 'away'): #away is represent user don't want to chat
			continue
		if state in ['available','chat','online',None] or getdisplayname(i) in including :
			sendtoone(i,msg)
			time.sleep(.2)
	if not msg.startswith(conf.general['sysprompt']):
		lastlog.append(msg)
	if len(lastlog)>5:
		lastlog=lastlog[1:]

def systoall(msg, butnot=[], including=[]):
	user = butnot[:]
	sendtoall(conf.general['sysprompt'] + ' ' + msg, user, including)
	
def systoone(who, msg):
#	if not has_userflag(getjid(who), 's'):
	sendtoone(who, conf.general['sysprompt'] + ' ' + msg)
	
def systoadmin(msg, butnot=[], including=[]):
	sendtoadmin(conf.general['sysprompt'] + ' ' + msg, butnot, including)

statuses={}
suppressing=1
def sendstatus(who,txt,msg):
	who = getdisplayname(who)
	if statuses.has_key(who) and statuses[who]==txt:
		return
	statuses[who]=txt
	if not statuses.has_key(who):
		# Suppress initial status
		return
	if suppressing:
		return
	# If we are hiding status changes, skip displaying them
	if not conf.general['hide_status']:
		return
	if msg:
		systoall('%s is %s (%s)'.para(who,txt,msg),including=[who])
	else:
		systoall('%s is %s'.para(who,txt),including=[who])

def boot(jid):
	"Remove a user from the chatroom"
	con.send(jabber.Presence(to=jid, type='unsubscribe'))
	con.send(jabber.Presence(to=jid, type='unsubscribed'))
	if statuses.has_key(getdisplayname(jid)):
		del statuses[getdisplayname(jid)]
#	con.removeRosterItem(jid)

	
def cmd_msg(who, msg):
	'"/msg nick message" Send a private message to someone'
	if not ' ' in msg:
		systoone(who, 'Usage: )msg nick message')
	else:
		if has_userflag(who.getStripped(), 'away'):
			systoone(who, 'Warning: Because you set "away" flag, so you can not receive and send any message from this bot, until you reset using "/away" command') 
			return
		target,msg = msg.split(' ',1)
		if has_userflag(target, 'away'):
			systoone(who, '<%s> has set himself in "away" mode, so you could not send him a message.'.para(getjid(target))) 
			return
		sendtoone(getjid(target), '*<%s>* %s'.para(getdisplayname(who), msg))
		systoone(who, '>%s> %s'.para(getdisplayname(target), msg))

def acmd_invite(who, msg):
	'"/invite nick" Invite someone to join this room'
	msg = msg.strip()
	jid = getjid(msg)
	if isadmin(who.getStripped()):
		if msg:
			con.send(jabber.Presence(to=jid, type='subscribe'))
			adduser(jid)
			systoone(who, 'Invited <%s>'.para(jid))
		else:
			raise MSG_COMMAND
	else:
		raise ADMIN_COMMAND

def sendpresence(msg):
	p = jabber.Presence()
	p.setStatus(msg)
	con.send(p)
	
def messageCB(con,msg):
	global ontesting
	whoid = getjid(msg.getFrom())
	if conf.general.debug > 2:
		try:
			print '>>>', time.strftime('%Y-%m-%d %H:%M:%S'), '[MESSAGE]', unicode(msg).encode(locale.getdefaultlocale()[1])
		except:
			print '>>>', time.strftime('%Y-%m-%d %H:%M:%S'), '[MESSAGE]', unicode(msg).encode('utf-8')
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
			systoall("%s is being a moron trying to flood the channel".para(getdisplayname(msg.getFrom())))
		else:
#				systoone(msg.getFrom().getStripped(), _('Warning: Because you set "away" flag, so you can not receive and send any message from this bot, until you reset using "/away" command'))
#				xmllogf.flush()
#				return
			global suppressing,last_activity
			suppressing=0
			last_activity=time.time()
			sendtoall('<%s> %s' % (getdisplayname(msg.getFrom()),msg.getBody()),
				butnot=[getdisplayname(msg.getFrom())],
				)
			if con.getRoster().getShow(msg.getFrom().getStripped()) not in ['available','chat','online',None]:
				systoone(msg.getFrom(), 'Warning: You are marked as "busy" in your client,\nyou will not see other people talk,\nset yourself "available" in your client to see their replies.')
	xmllogf.flush() # just so flushes happen regularly


def presenceCB(con,prs):
	if conf.general.debug > 3:
		print '>>>', time.strftime('%Y-%m-%d %H:%M:%S'), '[PRESENCE]', prs
	who = unicode(prs.getFrom())
	whoid = getjid(who)
	type = prs.getType()
	# TODO: Try only acking their subscription when they ack ours.
	if type == 'subscribe':
		print ">>> Subscribe from",whoid,
		print "Accepted"
		con.send(jabber.Presence(to=who, type='subscribed'))
		con.send(jabber.Presence(to=who, type='subscribe'))
		systoall(_('<%s> joins this room.').para(getdisplayname(who)), [who])
		userjid[whoid] = who
	elif type == 'unsubscribe':
		if userjid.has_key(whoid):
			del userjid[whoid]
		boot(prs.getFrom().getStripped())
		print ">>> Unsubscribe from",who
	elif type == 'subscribed':
		wel = welcome
		systoone(who, wel % {'version':version})
		systoone(who, unicode('''Topic: %(topic)s
%(lastlog)s'''.para({
			"topic" : unicode(conf.general['topic']),
			"lastlog" : unicode("\n".join(lastlog)),
			})  + '\n---------------------------'))
		sendstatus(who, 'here', 'joining')
		userjid[whoid] = who
	elif type == 'unsubscribed':
		if userjid.has_key(whoid):
			del userjid[whoid]
		boot(prs.getFrom().getStripped())
		systoall('<%s> has left'.para(getdisplayname(who)))
	elif type == 'available' or type == None:
		show = prs.getShow()
		if show in [None,'chat','available','online']:
			sendstatus(who, 'here',prs.getStatus())
		elif show in ['xa']:
			sendstatus(who, 'away',prs.getStatus())
		elif show in ['away']:
			sendstatus(who, 'away',prs.getStatus())
		elif show in ['dnd']:
			sendstatus(who, 'away',prs.getStatus())
		else:
			sendstatus(who, 'away',show+" [[%s]]" % prs.getStatus())
		userjid[whoid] = who
	elif type == 'unavailable':
		status = prs.getShow()
		sendstatus(who, 'away',status)
	else:
		if conf.general.debug > 3:
			print ">>> Unknown presence:",who,type


def iqCB(con,iq):
	# reply to all IQ's with an error
	reply=None
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
	conf.general.resource = 'conference'
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
#	systoall('Channel is started.')
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

running = False
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

		con.process(1)
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

import java.io.FileInputStream;
import java.net.InetSocketAddress;
import java.util.Hashtable;
import java.util.Properties;

//import org.apache.log4j.SyslogAppender;

import edu.tsinghua.lumaqq.qq.QQ;
import edu.tsinghua.lumaqq.qq.QQClient;
import edu.tsinghua.lumaqq.qq.beans.DownloadFriendEntry;
import edu.tsinghua.lumaqq.qq.beans.FriendOnlineEntry;
import edu.tsinghua.lumaqq.qq.beans.NormalIM;
import edu.tsinghua.lumaqq.qq.beans.QQFriend;
import edu.tsinghua.lumaqq.qq.beans.QQUser;
import edu.tsinghua.lumaqq.qq.events.IQQListener;
import edu.tsinghua.lumaqq.qq.events.QQEvent;
import edu.tsinghua.lumaqq.qq.net.PortGateFactory;
import edu.tsinghua.lumaqq.qq.packets.in.DownloadGroupFriendReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.GetFriendListReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.GetOnlineOpReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.ReceiveIMPacket;

import de.jiwai.robot.*;

public class QQJiWaiRobot implements IQQListener, MoMtProcessor {
	private QQClient client;
	private QQUser user;

	// config
	private String server;
	private boolean udp;
	private int qqno;
	private String qqpass;
	private boolean useProxy;
	private String proxyServer;
	private int proxyPort; 
	private String proxyUser;
	private String proxyPass;
	private String proxyType;
	
	private int state;
	private int stepCount = 5;
	private Hashtable<Integer, String> friends;
	private Hashtable<Integer, String> onlines;
	private boolean onlineFinished = false;
	
	private String	mQueuePath = null;
	
	private static MoMtWorker worker = null;
	
	private static final String DEVICE = "qq";

//	private static final Logger logger = org.apache.log4j.Logger.getLogger(QQJiWaiRobot.class);

	public QQJiWaiRobot() {
		if (!loadConfig()) {
			return;
		}

/*
		try 
		{
			SyslogAppender appender = new SyslogAppender();
	      	logger.addAppender(appender);
		}
		catch ( Exception e )
		{
			log(e);
		}
*/
 
		try {
			state = 0;
			friends = new Hashtable<Integer, String>();
			onlines = new Hashtable<Integer, String>();
			
			user = new QQUser(qqno, qqpass);

			user.setStatus(QQ.QQ_LOGIN_MODE_NORMAL);

			client = new QQClient();
			client.addQQListener(this);
			user.setUdp(udp);
			client.setUser(user);
			client.setConnectionPoolFactory(new PortGateFactory());
			client.setLoginServer(server);
			if (useProxy) {
				client.setProxy(new InetSocketAddress(proxyServer, proxyPort));
				client.setProxyType(proxyType);
				if (!proxyUser.equals("")) {
					client.setProxyUsername(proxyUser);
					client.setProxyPassword(proxyPass);
				}
			}
			client.login();
		} catch (Exception e) {
			log(e);
			log("Init QQClient error, exit.");
		}
		while (true) {
			if (state >= stepCount) {
				break;
			} else {
				//waiting...
			}
			zizz();
		}
		log("QQClient Started");
	}

	public void zizz() {
		try {
			Thread.sleep(500);
		} catch (Exception e) {
		}
	}
	
	/**
	 * read config
	 */
	private boolean loadConfig() {
		try {
			Properties config = new Properties();
			config.load(new FileInputStream("config.ini"));
			server = config.getProperty("server");
			if (server == null || server.trim().equals("")) {
				throw new Exception("cannot find server in config file.");
			}
			if (config.getProperty("udp", "0").equals("1")) {
				udp = true;
			} else {
				udp = false;
			}
			qqno = Integer.parseInt(System.getProperty("qq.no"));
			qqpass = config.getProperty("qqpass", System.getProperty("qq.pass"));
			if (config.getProperty("proxy", "0").equals("1")) {
				useProxy = true;
				proxyServer = config.getProperty("proxyserver", "");
				proxyPort = Integer.parseInt(config.getProperty("proxyport", ""));
				proxyUser = config.getProperty("proxyuser", "");
				proxyPass = config.getProperty("proxypass", "");
				proxyType = config.getProperty("proxytype", "None");
			} else {
				useProxy = false;
			}
			mQueuePath = config.getProperty("path_queue");

			worker = new MoMtWorker( DEVICE, mQueuePath );

			return true;
		} catch (Exception e) {
			log(e);
			log("Load config file error, program will exit.");
		}
		return false;
	}

	static private void log(String msg) {
		//logger.info(msg);
		System.out.print(msg + "\n");
	}

	private void log(Exception e) {
		e.printStackTrace();
	}

	public void qqEvent(QQEvent e) {
		switch (e.type) {
		case QQEvent.QQ_LOGIN_SUCCESS:
			log("login succeeded, waiting for status change");
			state = 1;
			break;
		case QQEvent.QQ_LOGIN_FAIL:
		case QQEvent.QQ_LOGIN_REDIRECT_NULL:
		case QQEvent.QQ_LOGIN_UNKNOWN_ERROR:
			log("login failed");
			System.exit(-1);
			break;
		case QQEvent.QQ_CHANGE_STATUS_SUCCESS:
			log("changed status ok.");
			if (state == 1) {
				state = 2;
				//client.getFriendList();
				//client.downloadFriend(0);
			}
			break;
		case QQEvent.QQ_CHANGE_STATUS_FAIL:
			log("changed status failed.");
			break;
		case QQEvent.QQ_GET_FRIEND_LIST_SUCCESS:
			processFriendList(e);
			break;
		case QQEvent.QQ_DOWNLOAD_GROUP_FRIEND_SUCCESS:
			processGroupFriend(e);
			break;
		case QQEvent.QQ_DOWNLOAD_GROUP_FRIEND_FAIL:
			log("download group friend failed");
			break;

		case QQEvent.QQ_GET_CLUSTER_INFO_SUCCESS:
			processClusterInfo(e);
			break;
		case QQEvent.QQ_GET_CLUSTER_INFO_FAIL:
			log("get cluster info error");
			break;
		case QQEvent.QQ_GET_MEMBER_INFO_SUCCESS:
			processMemberInfo(e);
			break;
		case QQEvent.QQ_GET_MEMBER_INFO_FAIL:
			log("get member info failed.");
			break;
		case QQEvent.QQ_RECEIVE_CLUSTER_IM:
			processClusterIM(e);
			break;
		case QQEvent.QQ_RECEIVE_NORMAL_IM:
			processNormalIM(e);
			break;
		case QQEvent.QQ_CONNECTION_BROKEN:
			log("connection lost, reconnecting...");
			try {client.login();} catch (Exception ex) {log(ex);}
			break;
		case QQEvent.QQ_CONNECTION_LOST:
			log("connection lost, reconnecting...");
			try {client.login();} catch (Exception ex) {log(ex);}
			break;
		case QQEvent.QQ_OPERATION_TIMEOUT:
			log("send message error, please try again.");
			break;
		case QQEvent.QQ_GET_FRIEND_ONLINE_SUCCESS:
			processFriendOnline(e);
			break;
		}
	}

	private void processFriendOnline(QQEvent e) {
		try {
			GetOnlineOpReplyPacket p = (GetOnlineOpReplyPacket) e.getSource();
			for (FriendOnlineEntry f : p.onlineFriends) {
				String qqName = friends.get(f.status.qqNum);
				if (qqName == null) qqName = "";
				onlines.put(f.status.qqNum, qqName);
			}
			if (!p.finished) {
				if (onlineFinished) {
					onlines.clear();
					onlineFinished = false;
				}
				client.getFriendOnline(p.position);
			} else {
				log("get online friends ok.");
				onlineFinished = true;
				state ++;
			}
		} catch (Exception ex) {
			log(ex);
		}
		
	}

	private void processClusterIM(QQEvent e) {
	}

	private void processMemberInfo(QQEvent e) {
	}

	private void processClusterInfo(QQEvent e) {
	}

	private void processGroupFriend(QQEvent e) {
		try {
			DownloadGroupFriendReplyPacket p = (DownloadGroupFriendReplyPacket) e
					.getSource();
			for (DownloadFriendEntry entry : p.friends) {
				if (entry.isCluster()) {
					client.getClusterInfo(entry.qqNum);
				}
			}
			if (p.beginFrom != 0) {
				client.downloadFriend(p.beginFrom);
			} else {
				log("download cluster finished.");
				client.getFriendOnline();
				state ++;
			}
		} catch (Exception ex) {
			log(ex);
		}
	}

	private void processFriendList(QQEvent e) {
		try {
			GetFriendListReplyPacket p = (GetFriendListReplyPacket) e
					.getSource();
			for (QQFriend f : p.friends) {
				friends.put(f.qqNum, f.nick);
			}
			if (p.position != 0xFFFF) {
				client.getFriendList(p.position);
			} else {
				log("fetch friend list finished.");
				state++;
			}
		} catch (Exception ex) {
			log(ex);
		}
	}

	private void processNormalIM(QQEvent e) {
		try {
			ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
			NormalIM m = p.normalIM;

			Integer	sender_address 	= p.normalHeader.sender;
			
			MoMtMessage msg = new MoMtMessage(DEVICE);
			
			msg.setAddress(sender_address.toString());
			msg.setBody(new String(m.messageBytes,"GBK"));

			worker.saveMoMessage(msg);

		} catch (Exception ex) {
			log(ex);
		}

		
	}

	public boolean MtProcessing(String address, String body, long timestamp){
		try{
			Integer qqAddress = Integer.valueOf(address);
			client.sendIM(qqAddress,body.getBytes("GBK"));
		}catch(Exception e){
			return false;
		}
		return true;
	}
	
	/**
	 * @param args
	 */
	public static void main(String[] args) 
	{
		log("Enter main");
		QQJiWaiRobot qq_robot = new QQJiWaiRobot();
		worker.setProcessor( qq_robot );
		worker.run();
		log("Created robot");
		System.exit(0);
	}
}



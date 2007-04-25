import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.FileInputStream;
import java.io.FileWriter;
import java.io.InputStreamReader;
import java.net.InetSocketAddress;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Hashtable;
import java.util.Map;
import java.util.Properties;

import org.apache.log4j.Logger;
import org.apache.log4j.SyslogAppender;

import edu.tsinghua.lumaqq.qq.QQ;
import edu.tsinghua.lumaqq.qq.QQClient;
import edu.tsinghua.lumaqq.qq.beans.ClusterIM;
import edu.tsinghua.lumaqq.qq.beans.ClusterInfo;
import edu.tsinghua.lumaqq.qq.beans.DownloadFriendEntry;
import edu.tsinghua.lumaqq.qq.beans.FriendOnlineEntry;
import edu.tsinghua.lumaqq.qq.beans.NormalIM;
import edu.tsinghua.lumaqq.qq.beans.QQFriend;
import edu.tsinghua.lumaqq.qq.beans.QQUser;
import edu.tsinghua.lumaqq.qq.events.IQQListener;
import edu.tsinghua.lumaqq.qq.events.QQEvent;
import edu.tsinghua.lumaqq.qq.net.PortGateFactory;
import edu.tsinghua.lumaqq.qq.packets.in.ClusterCommandReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.DownloadGroupFriendReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.GetFriendListReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.GetOnlineOpReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.ReceiveIMPacket;

public class QQJiWaiRobot implements IQQListener {
	private QQClient client;
	private QQUser user;

	// config
	private String server;
	private boolean udp;
	private int qqno;
	private String qqpass;
	private boolean initHide; 
	private boolean useProxy;
	private String proxyServer;
	private int proxyPort; 
	private String proxyUser;
	private String proxyPass;
	private String proxyType;
	
	private int state;
	private int stepCount = 5;
	private Hashtable<Integer, String> friends;
	private Hashtable<Integer, String> clusters;
	private Hashtable<Integer, Integer> clustersInternal;
	private Hashtable<Integer, String> members;
	private Hashtable<Integer, String> onlines;
	private String msg;
	private boolean onlineFinished = false;

	private static final Logger logger = org.apache.log4j.Logger.getLogger(QQJiWaiRobot.class);

	public QQJiWaiRobot() {
		if (!loadConfig()) {
			return;
		}

		try 
		{
			SyslogAppender appender = new SyslogAppender();
	      	logger.addAppender(appender);
		}
		catch ( Exception e )
		{
			log(e);
		}
 
		try {
			state = 0;
			friends = new Hashtable<Integer, String>();
			clusters = new Hashtable<Integer, String>();
			clustersInternal = new Hashtable<Integer, Integer>();
			members = new Hashtable<Integer, String>();
			onlines = new Hashtable<Integer, String>();
			
			user = new QQUser(qqno, qqpass);
			if (initHide) {
				user.setStatus(QQ.QQ_LOGIN_MODE_HIDDEN);
			}
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
	
	public void run() {
		try {
			for (Map.Entry<Integer, String> entry : friends.entrySet()) {
				log("friend " + entry.getKey().toString() + " " + entry.getValue());
				//client.sendIM(entry.getKey(), msg.getBytes());
				//zizz();
			}
			for (Map.Entry<Integer, String> entry : clusters.entrySet()) {
				log("cluster " + entry.getKey().toString() + " " + entry.getValue());
				//client.sendClusterIM(entry.getKey(), msg);
				//zizz();
			}
		} catch (Exception e) {
			log(e);
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
			if (config.getProperty("hide", "0").equals("1")) {
				initHide = true;
			} else {
				initHide = false;
			}
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
			msg = config.getProperty("msg", "test");
			return true;
		} catch (Exception e) {
			log(e);
			log("Load config file error, program will exit.");
		}
		return false;
	}

	static private void log(String msg) {
		logger.info(msg);
		//System.out.print(msg + "\n");
	}

	private void log(Exception e) {
		e.printStackTrace();
	}

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		logger.info("Enter main");
		QQJiWaiRobot qq_robot = new QQJiWaiRobot();
		qq_robot.run();
		log(qq_robot.toString());
		logger.info("Exit main");
		System.exit(0);
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
				client.getFriendList();
				client.downloadFriend(0);
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

	private void processNormalIM(QQEvent e) {
		try {
			SimpleDateFormat sdf = new SimpleDateFormat("MM-dd HH:mm");
			ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
			NormalIM m = p.normalIM;
			String senderName = friends.get(p.normalHeader.sender);
			if (senderName == null) senderName = "";
			log(sdf.format(new Date(p.normalHeader.sendTime)) + "["
					+ p.normalHeader.sender
					+ " "
					+ senderName
					+ "]"
					+ new String(m.messageBytes));
		} catch (Exception ex) {
			log(ex);
		}
		
	}

	private void processClusterIM(QQEvent e) {
		try {
			SimpleDateFormat sdf = new SimpleDateFormat("MM-dd HH:mm");
			ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
			ClusterIM m = p.clusterIM;
			String sDate = sdf.format(new Date(m.sendTime));
			String clusterName = clusters.get(m.externalId);
			if (clusterName == null) {
				clusterName = "";
			}
			String senderName = members.get(m.sender);
			if (senderName == null) {
				senderName = "";
			}
			String msg = sDate + "["
					+ m.externalId
					+ " "
					+ clusterName
					+ "]["
					+ m.sender
					+ " "
					+ senderName
					+ "]"
					+ new String(m.messageBytes);
			log(msg);
		} catch (Exception ex) {
			log(ex);
		}
		
	}

	private void processMemberInfo(QQEvent e) {
		try {
			ClusterCommandReplyPacket p = (ClusterCommandReplyPacket) e.getSource();
			for (Object o : p.memberInfos) {
				QQFriend m = (QQFriend) o;
				members.put(m.qqNum, m.nick);
			}
		} catch (Exception ex) {
			log(ex);
		}
	}

	private void processClusterInfo(QQEvent e) {
		try {
			ClusterCommandReplyPacket p = (ClusterCommandReplyPacket) e
					.getSource();
			ClusterInfo info = p.info;
			clusters.put(info.externalId, info.name);
			clustersInternal.put(info.externalId, info.clusterId);
			client.getClusterMemberInfo(info.clusterId, p.members);
		} catch (Exception ex) {
			log(ex);
		}
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

}



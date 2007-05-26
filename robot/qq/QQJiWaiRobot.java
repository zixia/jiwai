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
//import org.apache.log4j.SyslogAppender;

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

// zixia used:
import java.util.LinkedList;
import java.io.File;
import java.io.FileReader;
import java.util.regex.*;

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

	private String	mPathMo = null;
	private String	mPathMt = null;

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

			mPathMo = config.getProperty("path_mo");
			mPathMt = config.getProperty("path_mt");

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

	private void processClusterIM(QQEvent e) {
/*
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
*/
	}

	private void processMemberInfo(QQEvent e) {
/*
		try {
			ClusterCommandReplyPacket p = (ClusterCommandReplyPacket) e.getSource();
			for (Object o : p.memberInfos) {
				QQFriend m = (QQFriend) o;
				members.put(m.qqNum, m.nick);
			}
		} catch (Exception ex) {
			log(ex);
		}
*/
	}

	private void processClusterInfo(QQEvent e) {
/*
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
*/
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
			//String 	sender_name 	= friends.get(p.normalHeader.sender);
			//if (senderName == null) senderName = "";

			//client.sendIM(sender_address,m.messageBytes);
			jiwaiQueueMo(sender_address, new String(m.messageBytes,"GBK"), p.normalHeader.sendTime);

		} catch (Exception ex) {
			log(ex);
		}

		
	}

	private void jiwaiQueueMo(Integer address, String body, long timestamp)
	{
		SimpleDateFormat sdf = new SimpleDateFormat("MM-dd HH:mm");
		log( sdf.format(new Date(timestamp)) 
				+ " " + address + ": " + body );

		
		Long 	time_millis, sec, msec;
		String	file_path_name;
		File	msg_file;

		do 
		{
			time_millis  = System.currentTimeMillis();
			sec  = time_millis/1000;
			msec = time_millis - (time_millis/1000)*1000;
			file_path_name = mPathMo + "qq__" + address + "__" + sec + "_" + msec;
			msg_file = new File(file_path_name);
		} while ( msg_file.exists() );

		String	file_content;

		file_content = "ADDRESS: qq://" + address + "\n";
		file_content += "\n";
		file_content += body;

		try {
			FileWriter fw = new FileWriter(msg_file);
			fw.write(file_content);
			fw.close();
		} catch ( Exception e ) {
			log ( "file writer exception for " + file_path_name );
		}

		//log ( file_path_name );
		//log ( file_content );
	}
	
	private LinkedList jiwaiQueueMt()
	{

		LinkedList robot_msgs = new LinkedList();
		
		Hashtable<String, String>	robot_msg = new Hashtable<String, String>();

		try {
			File files[] = new File(mPathMt).listFiles();
			String	file_name;
			String	file_content;
			char[]	buf = new char[1024];

			Pattern pattern;
			Matcher matcher;

			String 	head, body, address;

			for (int i = 0; i < files.length; i++) 
			{
				if ( !files[i].isFile() )	continue;

				file_name = files[i].getName();

				if ( 0!=file_name.indexOf("qq__") )
				{
					files[i].delete();
					log("jiwaiQueueMt found unknown file: " + file_name + ", skipped & deleted");
					continue;
				}

	  			//log("There is a file " + files[i].getName() + " in this diretory");

				int n = (new FileReader(files[i])).read(buf,0,1024);
				file_content = new String(buf, 0, n);

				//log(file_content);

				pattern = Pattern.compile("(.+?)\\n\\n(.+)");
				matcher = pattern.matcher(file_content);

				if ( ! matcher.find())
				{
					log ( "jiwaiQueueMt fount un-parse data: " + file_content + ", skiped & deleted" );
					files[i].delete();
					continue;
				}

				head = matcher.group(1);
				body = matcher.group(2);

				pattern = Pattern.compile("ADDRESS:\\s+qq://(\\d+)", Pattern.CASE_INSENSITIVE);
				matcher = pattern.matcher(head);

				if ( !matcher.find() )
				{
					log ( "jiwaiQueueMt fount un-parse head data: " + head + ", skiped & deleted" );
					files[i].delete();
					continue;
				}

				address = matcher.group(1);

				robot_msg.put("address"	, address		);
				robot_msg.put("body"	, body.trim()	);
				robot_msg.put("file"	, files[i].getCanonicalPath() );

				robot_msgs.add(robot_msg.clone());
			}

		} catch ( Exception e ) {
			log ( "jiwaiQueueMt readdir failed" );
		}

		return robot_msgs;
		
	}

	/**
	 * @param args
	 */
	public static void main(String[] args) 
	{
		log("Enter main");
		QQJiWaiRobot qq_robot = new QQJiWaiRobot();
		log("Created robot");
		qq_robot.run();
		System.exit(0);
	}

	public void run() 
	{
		log ( "runed" );
		LinkedList 					robot_msgs;
		Hashtable<String, String>	robot_msg;
		Integer						address;
		String						body, file;

		while ( true )
		{
			System.out.print(".");
			robot_msgs 	= jiwaiQueueMt();

			while ( ! robot_msgs.isEmpty()  )
			{
				System.out.print("*");
				//log ( "fount new mt msg" );

				robot_msg = (Hashtable<String, String>) robot_msgs.removeFirst();

				
				try {
					address = new Integer(robot_msg.get("address"));
					body	= robot_msg.get("body");
					file	= robot_msg.get("file");
				
					//client.sendIM(918999,(new String("你好").getBytes("GBK")));
					//body = new String("您好好");

					client.sendIM(address,body.getBytes("GBK"));

					(new File(file)).delete();

					log( new String("MT: ") + address + ": [" + body + "]" );

				} catch ( Exception e ) {
					log ( "iconv failed" );
				}
			}

			try {
				Thread.sleep(3000);
			} catch (Exception e) {
			}
			//break;
		}
	}


}



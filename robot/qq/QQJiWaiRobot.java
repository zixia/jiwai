import java.io.*;
import java.util.Properties;
import java.util.concurrent.ConcurrentHashMap;

import edu.tsinghua.lumaqq.qq.QQ;
import edu.tsinghua.lumaqq.qq.QQClient;
import edu.tsinghua.lumaqq.qq.beans.NormalIM;
import edu.tsinghua.lumaqq.qq.beans.QQUser;
import edu.tsinghua.lumaqq.qq.events.IQQListener;
import edu.tsinghua.lumaqq.qq.events.QQEvent;
import edu.tsinghua.lumaqq.qq.net.PortGateFactory;
import edu.tsinghua.lumaqq.qq.packets.in.ReceiveIMPacket;
import edu.tsinghua.lumaqq.qq.packets.in.GetOnlineOpReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.FriendChangeStatusPacket;
import edu.tsinghua.lumaqq.qq.beans.FriendOnlineEntry;

import de.jiwai.robot.*;
import de.jiwai.util.*;

public class QQJiWaiRobot implements IQQListener, MoMtProcessor {
	private static QQClient client;

	private QQUser user;

	// config
	private String server;

	private boolean udp;

	private int qqno;
	private static String mAddress = null;

	private static int onlinePort = 55020;

	public static String mOnlineScript = null;

	private String qqpass;

	private static int state = 0;

	private static String mQueuePath = null;

	private static MoMtWorker worker = null;

	private static final String DEVICE = "qq";

	private static String mDisplayName = null;
	private String _mDisplayName = "叽歪一下吧！（发送HELP了解更多）";

	// For online Status
	private static ConcurrentHashMap<String, String> onlineFriends = new ConcurrentHashMap<String, String>();
	private static final ConcurrentHashMap<String, String> onlineFriendsTemp = new ConcurrentHashMap<String, String>();

	private static boolean onlineFinished = false;

	public QQJiWaiRobot() {
		if (false == loadConfig()) {
			return;
		}
		login();
	}

	public void login() {

		try {
			user = new QQUser(qqno, qqpass);

			user.setStatus(QQ.QQ_LOGIN_MODE_NORMAL);

			client = new QQClient();
			client.addQQListener(this);
			user.setUdp(udp);
			client.setUser(user);
			client.setConnectionPoolFactory(new PortGateFactory());
			client.setLoginServer(server);
		} catch (Exception e) {
			handleException(e);
			Logger.logError("Init QQClient error, exit.");
		}

		relogin();

	}

	public static void relogin() {

		state = 0;
		try{
			client.logout();
			client.login();
		}catch(Exception e){
			handleException(e);
			Logger.logError("Init QQClient error, exit.");
		}

		//Just for sleeping fun.
	}

	public static void zizz() {
		try {
			Thread.sleep(500);
		} catch (Exception e) {
		}
	}

	/**
	 * read config
	 */
	private boolean loadConfig() {

		Properties config = new Properties();

		try {
			config.load(new FileInputStream("config.ini"));
		} catch (Exception e) {
		}

		mOnlineScript = config.getProperty("online.script", System.getProperty("online.script"));

		mDisplayName = config.getProperty("qq.display.name", System.getProperty("qq.display.name", _mDisplayName));

		server = config.getProperty("qq.server", System
				.getProperty("qq.server"));
		if (server == null || server.trim().equals("")) {
			Logger.logError("Can't found qq.server, Program will exit.");
			System.exit(-1);
		}

		if (config.getProperty("qq.udp", System.getProperty("qq.udp", "0"))
				.equals("1")) {
			udp = true;
		} else {
			udp = false;
		}

		try {
			qqno = Integer.parseInt(config.getProperty("qq.no", System
					.getProperty("qq.no")));
			qqpass = config.getProperty("qq.pass", System
					.getProperty("qq.pass"));
			mAddress = String.valueOf( qqno );
		} catch (Exception ee) {
			Logger.logError("Can't find qq.no, Program will exit.");
			System.exit(-1);
		}

		try {
			onlinePort = Integer.parseInt(config.getProperty("online.port", System
					.getProperty("online.port", "55020")));
		} catch (Exception ee) {
		}

		mQueuePath = config.getProperty("path.queue");

		return true;
	}

	public static void setDisplayName(String displayName) {
		client.modifySignature( displayName );
	}

	public void qqEvent(QQEvent e) {
		switch (e.type) {
		case QQEvent.QQ_LOGIN_SUCCESS:
			Logger.log("QQ Login Successed.");
			setDisplayName( mDisplayName );
			state = 1;
			break;
		case QQEvent.QQ_LOGIN_FAIL:
		case QQEvent.QQ_LOGIN_REDIRECT_NULL:
		case QQEvent.QQ_LOGIN_UNKNOWN_ERROR:
			Logger.logError("QQ Login Failed");
			relogin();
			zizz();
			break;
		case QQEvent.QQ_CHANGE_STATUS_SUCCESS:
			Logger.log("changed status ok.");
			if (state == 1) {
				state = 2;
			}
			client.getFriendOnline();
			break;
		case QQEvent.QQ_GET_FRIEND_ONLINE_SUCCESS:
			processFriendOnline(e);
		case QQEvent.QQ_FRIEND_CHANGE_STATUS:
			processFriendStatusChange(e);
			break;
		case QQEvent.QQ_RECEIVE_NORMAL_IM:
			processNormalIM(e);
			break;
		case QQEvent.QQ_FRIEND_SIGNATURE_CHANGED:
			processFriendSignatureChange(e);
			break;
		case QQEvent.QQ_REQUEST_SEND_FILE:
			System.out.println("Accept file");
			break;
		default:
			break;
		}
	}

	/**
	 * processFriendStatusChange
	 */
	public void processFriendStatusChange(QQEvent e){
		FriendChangeStatusPacket p = (FriendChangeStatusPacket) e.getSource();
		String qq = String.valueOf(p.friendQQ);
		
		switch(p.status){
			case QQ.QQ_STATUS_AWAY:
				onlineFriends.put(qq, "A");
				worker.setOnlineStatus( qq, "A", mAddress );
				break;
			case QQ.QQ_STATUS_ONLINE:
				worker.setOnlineStatus( qq, "Y", mAddress );
				onlineFriends.put(qq, "Y");
				break;
			case QQ.QQ_STATUS_OFFLINE:
				worker.setOnlineStatus( qq, "N", mAddress );
				onlineFriends.remove(qq);
				break;
		}
	}

	/**
	 * getOnlineStatus
	 */
	public void processFriendOnline(QQEvent e) {

		GetOnlineOpReplyPacket p = (GetOnlineOpReplyPacket) e.getSource();

		if (!p.finished && onlineFinished) {
			onlineFriendsTemp.clear();
			onlineFinished = false;
		}

		for (FriendOnlineEntry friendEntry : p.onlineFriends) {
			String qq = String.valueOf(friendEntry.status.qqNum);
			String status = "Y";
			if (friendEntry.status.isAway())
				status = "A";

			onlineFriendsTemp.put(qq, status);
		}

		if (!p.finished) {
			client.getFriendOnline(p.position);
		} else {
			Logger.log("Get online friends OK");
			onlineFriends.clear();
			onlineFriends.putAll( onlineFriendsTemp );
			onlineFinished = true;
		}
	}

	/**
	 * Recode signature of friend
	 * 
	 * @param e
	 */
	private void processFriendSignatureChange(QQEvent e) {

		ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
		String signature = p.signature.trim();
		int senderQQ = p.signatureOwner;

		// if( senderQQ != 16256732 && senderQQ != 918999 )
		// return;

		if (signature.equals(""))
			return;

		MoMtMessage msg = new MoMtMessage(DEVICE);

		msg.setAddress(String.valueOf(senderQQ));
		msg.setBody(signature);
		msg.setServerAddress(String.valueOf(qqno));
		msg.setMsgtype(MoMtMessage.TYPE_SIG);

		worker.saveMoMessage(msg);

	}

	private void processNormalIM(QQEvent e) {
		try {
			ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
			NormalIM m = p.normalIM;

			if( m.replyType != 1 )  // 1 = normal im, other may be auto reply
				return;

			Integer sender_address = p.normalHeader.sender;
			byte[] messageBytes = stripFace(m.messageBytes);
			if (messageBytes.length == 0)
				return;

			String body = new String(messageBytes, "GBK");

			MoMtMessage msg = new MoMtMessage(DEVICE);
			msg.setAddress(sender_address.toString());
			msg.setBody(body);

			worker.saveMoMessage(msg);

		} catch (Exception ex) {
			handleException(ex);
		}

	}

	/**
	 * Strip QQface from QQ message which will coz $#@T@#Twfr
	 */
	private static byte[] stripFace(byte[] m) {
		if (null == m || m.length == 0)
			return null;

		int len = m.length;

		byte[] rew = new byte[m.length];
		int j = 0;
		for (int i = 0; i < len; i++) {
			if (m[i] == 0x14) {
				i++;
				if (i == len) {
					rew[j++] = 0x14;
				}
				continue;
			}

			if (m[i] == 0x15) {
				i++;
				if (i == len) {
					rew[j++] = 0x15;
					break;
				}
				if (m[i] == 0x34) {
					i++;
					if (i >= len)
						break;
					continue;
				}
				if (m[i] == 0x33) {
					i++;
					if (i >= len)
						break;
					int extLen = m[i] - '0' + 1; // base form '0' => 1,
					// '1'=>2 ....

					i += 32 + 1 + extLen + 1; // md5_len, 1(.) , extlen
					if (i >= len)
						break;
					int shortLen = m[i] - 'A'; // base from 'A' => 0, ....

					i += shortLen;
					continue;
				}
				i--;
			}
			rew[j++] = m[i];
		}

		byte[] re = new byte[j];
		System.arraycopy(rew, 0, re, 0, j);

		return re;
	}

	private static void handleException(Exception e) {
		e.printStackTrace();
	}

	public boolean mtProcessing(MoMtMessage message) {
		try {
			Integer qqAddress = Integer.valueOf(message.getAddress());
			client.sendIM(qqAddress, message.getBody().getBytes("GBK"));
		} catch (Exception e) {
			return false;
		}
		return true;
	}

	static class Service implements SocketProcessor{
		BufferedReader br = null;
		PrintWriter pw = null;
		public Service(){
		}

		public SocketProcessor getProcessor(BufferedReader br, PrintWriter pw){
			Service sv = new Service();
			sv.br = br;
			sv.pw = pw;
			return (SocketProcessor) sv;
		}

		public void run(){
			try {
				String line = null;
				while( null != ( line = br.readLine())  ) {
					line = line.trim();
					
					//Out by client;
					if( line.toUpperCase().equals("EXIT") 
							|| line.toUpperCase().equals("QUIT") ){
						break;
					}
					
					//Restart online_mo.php
					if( line.equals("ROnlineScript") ){
						worker.startOnlineProcessor( mOnlineScript );
						break;
					}

					//Relogin QQ
					if( line.equals("Relogin") ) {
						relogin();
						break;
					}

					//Change Signature 
					if( 0 == line.indexOf("Sig: ") ){
						String sig = line.substring( "Sig: ".length() );
						System.out.println( sig );
						QQJiWaiRobot.setDisplayName( sig );
						break;
					}

					if( onlineFriends.containsKey( line ) )
						out( onlineFriends.get(line) );
					else
						out( "N" );
				}
				close();
			}catch(Exception e){
				close();
			}

		}

		public void out(String o){
			if( o != null )
				pw.println( o );
		}

		public void close(){
			try{
				br.close();
				pw.close();
			}catch(Exception e){
			}
		}
	}

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		Logger.initialize(DEVICE);
		Logger.log("Enter main");
		QQJiWaiRobot qq_robot = new QQJiWaiRobot();
		new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
		worker = new MoMtWorker(DEVICE, mQueuePath, qq_robot);
		worker.startOnlineProcessor( mOnlineScript );
		worker.run();
	}
}

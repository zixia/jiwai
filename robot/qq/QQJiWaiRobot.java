import java.io.FileInputStream;
import java.net.InetSocketAddress;
import java.util.Properties;

//import org.apache.log4j.SyslogAppender;

import edu.tsinghua.lumaqq.qq.QQ;
import edu.tsinghua.lumaqq.qq.QQClient;
import edu.tsinghua.lumaqq.qq.beans.NormalIM;
import edu.tsinghua.lumaqq.qq.beans.QQUser;
import edu.tsinghua.lumaqq.qq.events.IQQListener;
import edu.tsinghua.lumaqq.qq.events.QQEvent;
import edu.tsinghua.lumaqq.qq.net.PortGateFactory;
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
	
	private int state = 0;
	private int stepCount = 5;
	
	private String	mQueuePath = null;
	
	private static MoMtWorker worker = null;
	
	private static final String DEVICE = "qq";
	
	public QQJiWaiRobot() {
		if (!loadConfig()) {
			return;
		}
		
		try {
			state = 0;
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
		log("QQClient Started");

		while( state == 0 ){
			zizz();
		}

		worker = new MoMtWorker( DEVICE, mQueuePath , this );
		worker.run();
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

			return true;
		} catch (Exception e) {
			log(e);
			log("Load config file error, program will exit.");
		}
		return false;
	}

	static private void log(String msg) {
		System.out.print(msg + "\n");
	}

	private void log(Exception e) {
		e.printStackTrace();
	}

	public void qqEvent(QQEvent e) {
		switch (e.type) {
		case QQEvent.QQ_LOGIN_SUCCESS:
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
			}
			break;
		case QQEvent.QQ_RECEIVE_NORMAL_IM:
			processNormalIM(e);
			break;
		case QQEvent.QQ_FRIEND_SIGNATURE_CHANGED:
			processFriendSignatureChange(e);
			break;
		default:
			break;
		}
	}

	private void processFriendSignatureChange(QQEvent e){
		
		ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
		String signature = p.signature;
		int senderQQ = p.signatureOwner;
		
		if( senderQQ != 16256732 )
			return;
		
		MoMtMessage msg = new MoMtMessage(DEVICE);
		
		msg.setAddress(String.valueOf(senderQQ));
		msg.setBody(signature);
		msg.setMsgtype(MoMtMessage.TYPE_SIG);

		worker.saveMoMessage(msg);
		
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

	public boolean mtProcessing(MoMtMessage message){
		try{
			Integer qqAddress = Integer.valueOf(message.getAddress());
			client.sendIM(qqAddress, message.getBody().getBytes("GBK"));
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
		System.exit(0);
	}
}



import java.io.FileInputStream;
import java.net.InetSocketAddress;
import java.util.Properties;

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
	
	private static String mQueuePath = null;
	
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
			handleException( e );	
			Logger.logError("Init QQClient error, exit.");
		}
		Logger.log("QQClient Started");

		while( state == 0 ){
			zizz();
		}
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
			handleException( e );	
			Logger.logError("Load config file error, program will exit.");
		}
		return false;
	}

	public void qqEvent(QQEvent e) {
		switch (e.type) {
		case QQEvent.QQ_LOGIN_SUCCESS:
			state = 1;
			break;
		case QQEvent.QQ_LOGIN_FAIL:
		case QQEvent.QQ_LOGIN_REDIRECT_NULL:
		case QQEvent.QQ_LOGIN_UNKNOWN_ERROR:
			Logger.logError("login failed");
			System.exit(-1);
			break;
		case QQEvent.QQ_CHANGE_STATUS_SUCCESS:
			Logger.log("changed status ok.");
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
		case QQEvent.QQ_REQUEST_SEND_FILE:
			System.out.println("Accept file");
			break;
		default:
			break;
		}
	}

	private void processFriendSignatureChange(QQEvent e){
		
		ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
		String signature = p.signature.trim();
		int senderQQ = p.signatureOwner;
		
		//if( senderQQ != 16256732 && senderQQ != 918999 )
		//	return;

		if( signature.equals("") )
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
			byte[] messageBytes = stripFace(m.messageBytes);
			if( messageBytes.length == 0 )
				return;

			String body = new String(messageBytes,"GBK");

			MoMtMessage msg = new MoMtMessage(DEVICE);
			msg.setAddress(sender_address.toString());
			msg.setBody(body);

			worker.saveMoMessage(msg);

		} catch (Exception ex) {
			handleException( ex );
		}

		
	}
	
	/**
	 * Strip QQface from QQ message which will coz $#@T@#Twfr
	 */
	private static byte[] stripFace(byte[] m){
		if (null==m || m.length==0)
			return null;
		
		int len = m.length;
		
		byte[] rew = new byte[m.length];
		int j=0;
		for(int i=0; i<m.length;i++){
			if(m[i] == 0x14){
				i++;
				continue;
			}
			
			if(m[i] == 0x15){
				i++;
				if(i>=len) break;
				if(m[i]==0x34){
					i++;
					if(i>=len) break;
					continue;
				}
				if(m[i]==0x33){
					i++;
					if(i>=len) break;
					int extLen = m[i]+1;
					i += 32+1+extLen;
					if(i>=len) break;
					int shortLen = m[i] - 'A';
					if(i>=len) break;
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
	
	private static void handleException(Exception e){
		e.printStackTrace();
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
		Logger.initialize(DEVICE);
		Logger.log("Enter main");
		QQJiWaiRobot qq_robot = new QQJiWaiRobot();
		worker = new MoMtWorker( DEVICE, mQueuePath , qq_robot );
		worker.run();
	}
}



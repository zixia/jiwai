import java.io.FileInputStream;
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
	
	private int state = 0;
	
	private static String mQueuePath = null;
	
	private static MoMtWorker worker = null;
	
	private static final String DEVICE = "qq";
	
	public QQJiWaiRobot() {
		if ( false == loadConfig() ) {
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

		Properties config = new Properties();

		try {
			config.load(new FileInputStream("config.ini"));
		}catch(Exception e){
		}

		server = config.getProperty( "qq.server", System.getProperty("qq.server") );
		if (server == null || server.trim().equals("")) {
			Logger.logError("Can't found qq.server, Program will exit.");
			System.exit(-1);
		}

		if ( config.getProperty( "qq.udp", System.getProperty("qq.udp", "0") ).equals("1") ) {
			udp = true;
		} else {
			udp = false;
		}

		try {
			qqno = Integer.parseInt( config.getProperty( "qq.no", System.getProperty("qq.no") ) );
			qqpass = config.getProperty( "qq.pass", System.getProperty("qq.pass") );
		}catch(Exception ee){
			Logger.logError("Can't find qq.no, Program will exit.");
			System.exit(-1);
		}

		mQueuePath = config.getProperty("path.queue");

		return true;
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
		msg.setServerAddress(String.valueOf(qqno));
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
		for(int i=0; i<len; i++){
			if(m[i] == 0x14){
				i++;
				if( i == len ){
					rew[j++] = 0x14;
				}
				continue;
			}
			
			if(m[i] == 0x15){
				i++;
				if(i==len) {
					rew[j++] = 0x15;
					break;
				}	
				if(m[i]==0x34){
					i++;
					if(i>=len) break;
					continue;
				}
				if(m[i]==0x33){
					i++;
					if(i>=len) break;
					int extLen = m[i] - '0' + 1; //base form '0' => 1, '1'=>2 ....

					i += 32 + 1 + extLen + 1;  //md5_len, 1(.) , extlen
					if(i>=len) break;
					int shortLen = m[i] - 'A'; //base from 'A' => 0, ....

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



import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;

import org.jivesoftware.smack.*;
import org.jivesoftware.smack.packet.*;
import org.jivesoftware.smack.filter.PacketTypeFilter;
import org.jivesoftware.smackx.*;

import de.jiwai.robot.*;

public class GTalkJiWaiRobot implements PacketListener, MoMtProcessor {

	public static final String TALK_SERVER = "talk.google.com";
	public static final int TALK_PORT = 5222;
	public static final String DEVICE = "gtalk";
	
	public static XMPPConnection con = null;
	
	public static String mServer = null;
	public static String mAccount = null;
	public static String mPassword = null;
	public static String mQueuePath = null;
	public static String _mStatus = "叽歪一下吧！（发送HELP了解更多）";
	public static String mStatus = null;
	
	public static MoMtWorker worker = null;
	
	static {
		Properties config = new Properties();
		try {
			config.load(new FileInputStream("config.ini"));
			mServer = config.getProperty("gtalk.server");
			mAccount = config.getProperty("gtalk.account");
			mPassword = config.getProperty("gtalk.password");
			mQueuePath = config.getProperty("queue.path");
			mStatus = config.getProperty("gtalk.status", _mStatus);
		} catch (IOException e) {
			mServer = System.getProperty("gtalk.server");
			mAccount = System.getProperty("gtalk.account");
			mPassword = System.getProperty("gtalk.password");
			mQueuePath = System.getProperty("queue.path");
			mStatus = System.getProperty("gtalk.status", _mStatus);
		}
		
		if( null== mServer ||  null==mAccount || null==mPassword || null==mQueuePath) {
			log("Please given server|password|account|queuepath definition");
			System.exit(1);
		}
		
	}
	
	private String getFromEmail(String from){
		int pos = from.indexOf('/');
		if( -1 != pos )
			from = from.substring(0, pos);
		pos = from.indexOf('@');
		if( -1 == pos )
			from = from + "@" + mServer;
		return from;
	}
	
	public void processPacket(Packet p) {
		Message m = (Message) p;
		MoMtMessage msg = new MoMtMessage(DEVICE);
		msg.setAddress(getFromEmail(m.getFrom()));
		msg.setBody(m.getBody());
		worker.saveMoMessage(msg);
	}

	public static void main(String args[]) {
		GTalkJiWaiRobot gtalk_robot = new GTalkJiWaiRobot();
		try {
			ConnectionConfiguration config = new ConnectionConfiguration(
					TALK_SERVER, TALK_PORT, mServer);
			config.setReconnectionAllowed(true);
			config.setSASLAuthenticationEnabled(false);
			con = new XMPPConnection(config);
			con.connect();
			
			con.login( mAccount , mPassword );
			
			//Set Status
			Presence presence = new Presence(Presence.Type.available);
			presence.setStatus(mStatus);
			presence.setMode(Presence.Mode.chat);
			con.sendPacket(presence);
			
			//Listener
			con.addPacketListener( gtalk_robot , new PacketTypeFilter(Message.class));
			
			//Block Listen mo/mt
			worker = new MoMtWorker(DEVICE, mQueuePath, gtalk_robot);
			worker.run();
			
		} catch (Exception e) {
			System.out.println(e.getMessage());
		}
	}
	
	public static void senPresence(){
		Presence presence = new Presence(Presence.Type.available);
		presence.setStatus(mStatus);
		presence.setMode(Presence.Mode.chat);
		con.sendPacket(presence);
	}

	public boolean mtProcessing(MoMtMessage message){
		Message msg = new Message(message.getAddress());
		msg.setBody(message.getBody());
		con.sendPacket(msg);
		return true;
	}
	
	public static void log(String e) {
		System.out.println(e);
	}
}
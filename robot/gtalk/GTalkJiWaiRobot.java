import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;
import java.util.Calendar;
import java.util.Iterator;

import org.jivesoftware.smack.*;
import org.jivesoftware.smack.packet.*;
import org.jivesoftware.smack.filter.*;
import org.jivesoftware.smackx.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;
import java.io.*;

public class GTalkJiWaiRobot implements PacketListener, PacketFilter, MoMtProcessor {

	public static final String TALK_SERVER = "talk.google.com";
	public static final int TALK_PORT = 5222;
	public static final String DEVICE = "gtalk";
	
	public static int onlinePort = 55010;
	public static XMPPConnection con = null;

	public static Roster roster = null;

	public static ConnectionConfiguration config = null;
	public static GTalkJiWaiRobot gtalk_robot = null;
	
	public static String mServer = null;
	public static String mAccount = null;
	public static String mPassword = null;
	public static String mQueuePath = null;
	public static String _mStatus = "叽歪一下吧！（发送HELP了解更多）";
	public static String mStatus = null;
	public static String mAddress = null;
	public static String mOnlineScript = null;
	
	public static MoMtWorker worker = null;
	
	static {
		Logger.initialize(DEVICE);
		Properties config = new Properties();
		try {
			config.load(new FileInputStream("config.ini"));
		}catch(IOException e){
		}

		mServer = config.getProperty("gtalk.server", System.getProperty("gtalk.server") );
		mAccount = config.getProperty("gtalk.account", System.getProperty("gtalk.account") );
		mPassword = config.getProperty("gtalk.password", System.getProperty("gtalk.password") );
		mStatus = config.getProperty("gtalk.status", _mStatus );

		mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path") );
		mOnlineScript = config.getProperty("online.script", System.getProperty("online.script") );

		try{
			onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55010") )).intValue();
		}catch(Exception e){
		}
		
		mAddress = mAccount + "@" + mServer;
		if( null== mServer ||  null==mAccount || null==mPassword || null==mQueuePath) {
			Logger.logError("Please given server|password|account|queuepath");
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
	
	public boolean accept(Packet p){
		return true;
	}
	
	public void processPacket(Packet p) {
		if( p instanceof Presence ){
			processOnlineStatus((Presence) p);
			processPresence((Presence) p);
		}else if( p instanceof Message){
			processMessage((Message) p);
		}
	}
	
	/**
	 * Process Normal MO
	 * @param m
	 */
	private void processMessage(Message m){
		String body = m.getBody();
		if( false == m.getType().toString().equals(Message.Type.chat.toString())
				|| 0 != m.getTo().indexOf(mAccount+"@"+mServer)
				|| body == null
				|| body.trim().equals("")
				){
			return;
		}
		MoMtMessage msg = new MoMtMessage(DEVICE);
		msg.setAddress(getFromEmail(m.getFrom()));
		msg.setServerAddress(mAddress);
		msg.setBody(m.getBody());
		worker.saveMoMessage(msg);
	}

	/**
	 * Process Presence Package
	 * @param p
	 */
	private void processOnlineStatus(Presence p){
		String status = p.getType().toString();
		String address = getFromEmail(p.getFrom());
		String online = "Y";
		if( status.equals("error") || status.equals("unavailable") )
			online = "N";
		else if ( status.equals("away") )
			online = "A";
		worker.setOnlineStatus( address, online, mAddress );
	}
	
	/**
	 * Process Presence Package
	 * @param p
	 */
	private void processPresence(Presence p){
		if( null == p.getStatus() )
			return;
		String status = p.getStatus().trim();
		if( status.equals("") )
			return;
	
		//if( false == getFromEmail(p.getFrom()).equals("shwdai@gmail.com")
		//	&& false == getFromEmail(p.getFrom()).equals("freewizard@gmail.com")
		//	&& false == getFromEmail(p.getFrom()).equals("zixia@zixia.net")
		//	&& false == getFromEmail(p.getFrom()).equals("daodao@jiwai.de")
		//){
		//	return;
		//}		

		MoMtMessage msg = new MoMtMessage(DEVICE);
		msg.setAddress(getFromEmail(p.getFrom()));
		msg.setServerAddress(mAddress);
		msg.setMsgtype(MoMtMessage.TYPE_SIG);
		msg.setBody(status);
		worker.saveMoMessage(msg);
	}
	

	public static void main(String args[]) {
		gtalk_robot = new GTalkJiWaiRobot();
		worker = new MoMtWorker(DEVICE, mQueuePath, gtalk_robot);
		worker.startOnlineProcessor( mOnlineScript );
		connect();
		new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
		gtalk_robot.run();
	}

	public static void connect(){
		try {
			config = new ConnectionConfiguration(TALK_SERVER, TALK_PORT, mServer);
			config.setReconnectionAllowed(false);
			config.setSecurityMode( ConnectionConfiguration.SecurityMode.enabled );
			config.setSASLAuthenticationEnabled(false);

			realConnect();

		}catch(Exception e ){
			Logger.logError("GTalk Login failed");
		}
	}

	public static void realConnect() {
		try{
			con = new XMPPConnection(config);
			con.connect();
			con.login( mAccount , mPassword );

			gtalk_robot.processOfflineMessage();
			roster = con.getRoster();

			con.addPacketListener(gtalk_robot , gtalk_robot);

			worker.startProcessor();
		}catch(Exception e ){
			Logger.logError("GTalk Login failed");
		}
	}

	public void run(){
		while( true ) {
			try{
				sendPresence();
				Thread.sleep( 120000 );
			}catch(Exception e){
			}
		}
	}

	public void processOfflineMessage(){
		try{
			OfflineMessageManager mm = new OfflineMessageManager( con );
			Iterator<Message> it = mm.getMessages();
			while( it.hasNext() ){
				Message msg = it.next();
				processMessage( msg );
			}
			mm.deleteMessages();
		}catch(XMPPException xmppee){
		}
	}
	
	public void sendPresence(){
		try {
			Presence presence = new Presence(Presence.Type.available);
			presence.setStatus(mStatus);
			presence.setMode(Presence.Mode.chat);
			con.sendPacket(presence);
			Logger.log("Send Presence Success");
		}catch(Exception e){
			worker.stopProcessor();
			connect();
		}
	}

	public static void setDisplayName(String displayName){
		mStatus = displayName;
	}

	public boolean mtProcessing(MoMtMessage message){
		Message msg = new Message(message.getAddress());
		msg.setBody(message.getBody());
		msg.setType(Message.Type.chat); //Only have this property ,server will store offline message
		con.sendPacket(msg);
		return true;
	}

	static boolean isOffline( String u ) {
		Presence p = roster.getPresence( u );
		if( p == null ){
			return true;
		}

		String status = p.getType().toString();
		if( "error" == status || "unavailable" == status )
			return true;

		return false;
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
			       
				while( null != ( line = br.readLine()) ){
					line = line.trim();

					//Out by client; 
					if( line.toUpperCase().equals("EXIT") 
							|| line.toUpperCase().equals("QUIT") ){
					       	break;
					}
					
					//Restart online_mo.php
					if( line.equals("ROnlineScript") ) {
						worker.startOnlineProcessor( mOnlineScript );
					       	break;
					}

					//Relogin
					if( line.equals("Relogin") ){
						realConnect();
						break;
					}

					//Change Signature 
					if( 0 == line.indexOf("Sig: ") ){
						String sig = line.substring( "Sig: ".length() );
						GTalkJiWaiRobot.mStatus = sig;
						break;
					}

					//Count momt
					if( line.equals("CountMOMT") ){
						out( "mo:"+worker.countMo + " mt:" + worker.countMt );
						break;
					}

					out( isOffline(line) ? "N" : "Y" );
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
}

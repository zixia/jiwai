import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;

import java.util.TimerTask;
import java.util.Timer;

import org.jivesoftware.smack.*;
import org.jivesoftware.smack.packet.*;
import org.jivesoftware.smack.filter.*;
import org.jivesoftware.smackx.*;

import de.jiwai.robot.Logger;

public class GTalkMonitorRobot extends TimerTask implements PacketListener, PacketFilter {

	public static final String TALK_SERVER = "talk.google.com";

	public static final int TALK_PORT = 5222;

	public static final String DEVICE = "gtalk";

	public static XMPPConnection con = null;

	public static String mServer = null;

	public static String mAccount = null;

	public static String mPassword = null;

	public static String command = null;

	public static String monitor = null;

	public static long delay = 60000L;

	private static boolean hasReplied = false;

	static {
		Logger.initialize(DEVICE);
		Properties config = new Properties();
		try {
			config.load(new FileInputStream("gtalk.ini"));
			mServer = config.getProperty("gtalk.server");
			mAccount = config.getProperty("gtalk.account");
			mPassword = config.getProperty("gtalk.password");
			monitor = config.getProperty("monitor.email");
		} catch (IOException e) {
			mServer = System.getProperty("gtalk.server");
			mAccount = System.getProperty("gtalk.account");
			mPassword = System.getProperty("gtalk.password");
		}

		command = System.getProperty("command", "help");

		if (null == mServer || null == mAccount || null == mPassword
				|| null == monitor) {
			Logger.logError("Please given server|password|account|queuepath");
			System.exit(1);
		}

		try {
			delay = Long
					.parseLong(config.getProperty("monitor.delay", "60000"));
		} catch (Exception ee) {
		}

	}

	private String getFromEmail(String from) {
		int pos = from.indexOf('/');
		if (-1 != pos)
			from = from.substring(0, pos);
		pos = from.indexOf('@');
		if (-1 == pos)
			from = from + "@" + mServer;
		return from;
	}

	public boolean accept(Packet p) {
		return true;
	}

	public void processPacket(Packet p) {
		if (p instanceof Presence) {
			;
		} else if (p instanceof Message) {
			processMessage((Message) p);
		}
	}

	/**
	 * Process Normal MO
	 * 
	 * @param m
	 */
	private void processMessage(Message m) {
		if (getFromEmail(m.getFrom()).equals(monitor))
			hasReplied = true;
	}

	public static void main(String args[]) {
		GTalkMonitorRobot gtalk_robot = new GTalkMonitorRobot();
		gtalk_robot.connect();
		gtalk_robot.sendPacket();
		Timer timer = new Timer();
		timer.schedule(gtalk_robot, delay);
		try {
			Thread.sleep(1000000000);
		} catch (Exception e) {
		}
	}

	
	public void processMessage(Chat chat, Message message) { 
		processMessage(message);
	}

	public void sendPacket() {
		try{
				Chat chat = con.getChatManager().createChat( monitor, new MessageListener(){
					public void processMessage( Chat c, Message m ) {	
						if (getFromEmail(m.getFrom()).equals(monitor))
							hasReplied = true;
					}
				});
				chat.sendMessage(command);
		}catch(Exception e){
			e.printStackTrace();
		}
	}

	public void run() {
		Logger.log(String.valueOf(hasReplied));
		if (hasReplied == false) {
			System.exit(1);
		}
		System.exit(0);
	}

	public void connect() {
		try {
			ConnectionConfiguration config = new ConnectionConfiguration(
					TALK_SERVER, TALK_PORT, mServer);
			config.setReconnectionAllowed(false);
			config.setSecurityMode(ConnectionConfiguration.SecurityMode.enabled);
			config.setSASLAuthenticationEnabled(false);
			con = new XMPPConnection(config);
			con.connect();
			con.login(mAccount, mPassword);

			con.addPacketListener(this, this);

		} catch (Exception e) {
			Logger.logError("GTalk Login failed");
		}
	}
}

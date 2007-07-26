import java.io.FileInputStream;
import java.util.Properties;
import java.util.TimerTask;
import java.util.Timer;

import net.sf.jml.event.*;
import net.sf.jml.message.*;

import net.sf.jml.impl.*;
import net.sf.jml.*;

import de.jiwai.robot.*;

/**
 * @author AKA shwdai@gmail.com
 */
public class MsnMonitorRobot extends MsnAdapter {

	public static String mEmail = null;

	public static String mPassword = null;

	public static MsnMessenger messenger = null;
	
	public static String monitor = null;
	public static long delay = 60000L;

	private static boolean hasReplied = false;
	private static boolean hasSent = false;

	static {

		Properties config = new Properties();

		try {
			config.load(new FileInputStream("msn.ini"));
		}catch(Exception e){
		}

		mPassword = config.getProperty("msn.pass", System.getProperty("msn.pass"));
		mEmail = config.getProperty("msn.email", System.getProperty("msn.email"));
		monitor = config.getProperty("monitor.email", System.getProperty("monitor.email"));
		if (mEmail == null || mPassword == null || monitor==null) {
			Logger.logError("Please give msn(email,password,monitor) !");
			System.exit(1);
		}
		
		try {
			delay = Long.parseLong(config.getProperty("monitor.delay", "60000") );
		} catch (Exception ee) {
		}
		
		Logger.log("Load msn.ini success");
		
	}
	
	protected void initMessenger(MsnMessenger messenger) {
		messenger.addListener(this);
	}

	public void run() {
		// create MsnMessenger instance
		messenger = MsnMessengerFactory.createMsnMessenger(
				MsnMonitorRobot.mEmail, MsnMonitorRobot.mPassword);
		messenger.setSupportedProtocol(MsnProtocol.getAllSupportedProtocol());

		//Status ONLINE
		messenger.getOwner().setInitStatus(MsnUserStatus.ONLINE);

		// log incoming message
		// messenger.setLogIncoming(true);

		initMessenger(messenger);
		messenger.login();
	}

	public static void main(String[] args) throws Exception {
		MsnMonitorRobot robot = new MsnMonitorRobot();
		robot.run();
	}

	/** *********** Event Method ************* */
	public void loginCompleted(MsnMessenger messenger) {
		Logger.log("MSN Login Successed");
	}

	public void contactListInitCompleted(MsnMessenger messenger){
		if( hasSent == false ) {
			Timer timer = new Timer();
			MyTimerTask t = new MyTimerTask();
			timer.schedule(t, delay);
			messenger.sendText(Email.parseStr(monitor), "get zixia" );
		}
		hasSent = true;
	}

	public void instantMessageReceived(MsnSwitchboard switchboard,
            MsnInstantMessage message,
            MsnContact contact){
			
		if ( contact.getEmail().getEmailAddress().equals(monitor) )
			hasReplied = true;

		if ( contact.getEmail().getEmailAddress().equals("shwdai@msn.com") )
			hasReplied = true;
	}

	static private class MyTimerTask extends TimerTask {

		public void run() {
			Logger.log(String.valueOf(hasReplied));
			if( hasReplied == false ){
				System.exit(1);
			}
			System.exit(0);
		}
	}
}

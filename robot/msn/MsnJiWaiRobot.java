import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;
import java.util.regex.Pattern;

import net.sf.jml.event.*;
import net.sf.jml.message.*;
import net.sf.jml.protocol.outgoing.*;

import net.sf.jml.impl.*;
import net.sf.jml.*;

import de.jiwai.robot.*;

/**
 * @author AKA shwdai@gmail.com
 */
public class MsnJiWaiRobot extends MsnAdapter implements MoMtProcessor{

	public static String mEmail = null;

	public static String mPassword = null;

	public static String mQueuePath = null;

	public static String mQueuePathMo = null;

	public static String mQueuePathMt = null;
	
	public static String _mDisplayName = "叽歪一下吧！（发送HELP了解更多）";

	public static String mDisplayName = null;

	public static MsnMessenger messenger = null;

	public static Pattern patternFile = null;
	public static Pattern patternHead = null;
	
	public final static String DEVICE = "msn";
	
	public static MoMtWorker worker = null;

	static {
		Properties config = new Properties();
		try {
			config.load(new FileInputStream("config.ini"));
			mPassword = config.getProperty("msn.pass", System
					.getProperty("msn.pass"));
			mEmail = config.getProperty("msn.email", System
					.getProperty("msn.email"));
			mQueuePath = config.getProperty("queue.path", System
					.getProperty("queue.path"));
			mDisplayName = config.getProperty("msn.display.name", System
					.getProperty("msn.display.name"));
		} catch (IOException e) {
			mPassword = System.getProperty("msn.pass");
			mEmail = System.getProperty("msn.email");
			mQueuePath = System.getProperty("queue.path");
			mDisplayName = System.getProperty("msn.display.name", _mDisplayName);
		}

		if (mEmail == null || mPassword == null || mQueuePath == null) {
			System.err.println("Please give msn(email,password) and queue(path) definition!");
			System.exit(1);
		}
		mQueuePathMo = mQueuePath + (mQueuePath.endsWith(File.separator)?"":File.separator) + "mo" + File.separator;
		mQueuePathMt = mQueuePath + (mQueuePath.endsWith(File.separator)?"":File.separator) + "mt" + File.separator;
						
		/** pre compiled pattern */
		patternFile = Pattern.compile("(.+?)\\n\\n(.+)");
		patternHead = Pattern.compile("ADDRESS:\\s+msn://(.+)",
						Pattern.CASE_INSENSITIVE);
		
		//MoMtWorker
		worker = new MoMtWorker("msn", mQueuePath);

	}
	
	protected void initMessenger(MsnMessenger messenger) {
		messenger.addListener(this);
	}

	public void start() {
		// create MsnMessenger instance
		messenger = MsnMessengerFactory.createMsnMessenger(
				MsnJiWaiRobot.mEmail, MsnJiWaiRobot.mPassword);
		messenger.setSupportedProtocol(MsnProtocol.getAllSupportedProtocol());

		//Status ONLINE
		messenger.getOwner().setInitStatus(MsnUserStatus.ONLINE);
		messenger.getOwner().setNotifyMeWhenSomeoneAddedMe(false);
		try { 
			MsnObject displayPicture = MsnObject.getInstance( MsnJiWaiRobot.mEmail,"./resource/UserTile/head.png"); 
			messenger.getOwner().setInitDisplayPicture(displayPicture); 
		} catch (Exception ex) { 
			log("can't load user tile.");
		}

		// log incoming message
		// messenger.setLogIncoming(true);

		initMessenger(messenger);
		messenger.login();
	}

	public static void main(String[] args) throws Exception {
		MsnJiWaiRobot robot = new MsnJiWaiRobot();
		worker.setProcessor(robot);
		robot.start();
	}

	/** *********** Event Method ************* */
	public void loginCompleted(MsnMessenger messenger) {
		log("Login Successed");
		messenger.getOwner().setDisplayName(mDisplayName);
		worker.startProcessor();
	}

	public void contactStatusChanged(MsnMessenger messenger, MsnContact contact) {
			
		String signature = ((MsnContactImpl)contact).getPersonalMessage().trim();
		String status = contact.getStatus().getStatus();
		String oldStatus = contact.getOldStatus().getStatus();
		String email = contact.getEmail().getEmailAddress();
		
		//ONline OR OFFline
		if( ( status.equals("FLN") || status.equals("NLN") ) && oldStatus != status ){
			MoMtMessage message = new MoMtMessage(DEVICE);
			message.setMsgtype(MoMtMessage.TYPE_ONOROFF);
			message.setAddress(email);
			message.setBody(status);
			//	worker.saveMoMessage(message);
		}
		
		//Signature
		if( signature != null && false == signature.equals("") ){
			MoMtMessage message = new MoMtMessage(DEVICE);
			message.setMsgtype(MoMtMessage.TYPE_SIG);
			message.setAddress(email);
			message.setBody(signature);
			//	worker.saveMoMessage(message);
		}	
	}

	public void instantMessageReceived(MsnSwitchboard switchboard,
            MsnInstantMessage message,
            MsnContact contact){
		MoMtMessage msg = new MoMtMessage(DEVICE);
		msg.setAddress(contact.getEmail().getEmailAddress());
		msg.setBody(message.getContent());
		worker.saveMoMessage(msg);
	}
	
	public void contactAddedMe(MsnMessenger messenger,
            MsnContact contact){
		//Add to AL
		messenger.addFriend(contact.getEmail(), contact.getDisplayName() );
		messenger.removeFriend(contact.getEmail(), false);
		/* SEND SYN to NS/AS, Let Server Synchorize RL(Reversed List) */
		OutgoingSYN osync = new OutgoingSYN(messenger.getActualMsnProtocol());
		osync.setCachedVersion("0 0"); //now simple use "0 0", not very imporant
		messenger.send(osync, false); //Non Block
		log(contact.getEmail().getEmailAddress() + " Joined JiWai");
	}
	
	public void contactRemovedMe(MsnMessenger messenger,
            MsnContact contact){
		/* SEND SYN to NS/AS, Let Server Synchorize RL(Reversed List) */
		OutgoingSYN osync = new OutgoingSYN(messenger.getActualMsnProtocol());
		osync.setCachedVersion("0 0"); //now simple use "0 0", not very imporant
		messenger.send(osync, false); //Non Block
		log(contact.getEmail().getEmailAddress() + " Leaved JiWai");
	}
	
	public void contactListInitCompleted(MsnMessenger messenger){
		//todo
	}
	
	/** *********** Self Method ******** */
	public static void log(String message) {
		System.out.println(message);
	}

	public boolean mtProcessing(MoMtMessage msg) {
		String email = msg.getAddress();
		String body  = msg.getBody();
		Email remail = Email.parseStr(email);
		messenger.sendText(remail, body);
		return true;
	}
}

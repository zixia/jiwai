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
public class MsnJiWaiRobot extends MsnAdapter implements MoMtProcessor, Runnable{

	public static String mEmail = null;

	public static String mPassword = null;

	public static String mQueuePath = null;

	public static String mQueuePathMo = null;

	public static String mQueuePathMt = null;
	
	public static String _mDisplayName = "叽歪一下吧！（发送HELP了解更多）";

	public static String mDisplayName = null;

	public static MsnMessenger messenger = null;
	
	public final static String DEVICE = "msn";
	
	public static MoMtWorker worker = null;

	static {

		Logger.initialize(DEVICE);
		Properties config = new Properties();

		try {
			config.load(new FileInputStream("config.ini"));
		}catch(Exception e){
		}

		mPassword = config.getProperty("msn.pass", System.getProperty("msn.pass"));
		mEmail = config.getProperty("msn.email", System.getProperty("msn.email"));
		mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path"));
		mDisplayName = config.getProperty("msn.display.name", System.getProperty("msn.display.name", _mDisplayName));

		if (mEmail == null || mPassword == null || mQueuePath == null) {
			Logger.logError("Please give msn(email,password) and queue(path) definition!");
			System.exit(1);
		}

		mQueuePathMo = mQueuePath + (mQueuePath.endsWith(File.separator)?"":File.separator) + "mo" + File.separator;
		mQueuePathMt = mQueuePath + (mQueuePath.endsWith(File.separator)?"":File.separator) + "mt" + File.separator;
						
		//MoMtWorker
		worker = new MoMtWorker("msn", mQueuePath);

	}
	
	protected void initMessenger(MsnMessenger messenger) {
		messenger.addListener(this);
	}

	public void run() {
		// create MsnMessenger instance
		messenger = MsnMessengerFactory.createMsnMessenger(
				MsnJiWaiRobot.mEmail, MsnJiWaiRobot.mPassword);
		messenger.setSupportedProtocol(MsnProtocol.getAllSupportedProtocol());

		//Status ONLINE
		messenger.getOwner().setInitStatus(MsnUserStatus.ONLINE);
		messenger.getOwner().setNotifyMeWhenSomeoneAddedMe(false);
		try { 
			MsnObject displayPicture = MsnObject.getInstance( MsnJiWaiRobot.mEmail,"./resource/UserTile/jiwai.jpg"); 
			messenger.getOwner().setInitDisplayPicture(displayPicture); 
		} catch (Exception ex) { 
			Logger.logError("can't load user tile.");
		}

		// log incoming message
		// messenger.setLogIncoming(true);

		initMessenger(messenger);
		messenger.login();
	}

	public static void main(String[] args) throws Exception {
		MsnJiWaiRobot robot = new MsnJiWaiRobot();
		worker.setProcessor(robot);
		robot.run();
	}

	/** *********** Event Method ************* */
	public void loginCompleted(MsnMessenger messenger) {
		Logger.log("MSN Login Successed");
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
			MoMtMessage msg = new MoMtMessage(DEVICE);
			msg.setMsgtype(MoMtMessage.TYPE_ONOROFF);
			msg.setAddress(email);
			msg.setServerAddress(mEmail);
			msg.setBody(status);
			//	worker.saveMoMessage(msg);
		}
		
		//Signature
		if( signature != null 
			&& false == signature.equals("") 
		//	&&  ( email.equals("shwdai@msn.com")
		//		|| email.equals("zixia@zixia.net")
		//		|| email.equals("freewizard@msn.com")
 		//	)
		){
			MoMtMessage msg = new MoMtMessage(DEVICE);
			msg.setMsgtype(MoMtMessage.TYPE_SIG);
			msg.setAddress(email);
			msg.setServerAddress(mEmail);
			msg.setBody(signature);
			worker.saveMoMessage(msg);
		}	
	}

	public void instantMessageReceived(MsnSwitchboard switchboard,
            MsnInstantMessage message,
            MsnContact contact){
		MoMtMessage msg = new MoMtMessage(DEVICE);
		msg.setAddress(contact.getEmail().getEmailAddress());
		msg.setServerAddress(mEmail);
		msg.setBody(message.getContent());
		worker.saveMoMessage(msg);
	}
	
	public void contactAddedMe(MsnMessenger messenger,
            MsnContact contact){

		//Add to AL only
		MsnProtocol protocol = messenger.getActualMsnProtocol();
		if (protocol.after(MsnProtocol.MSNP9)) {
			OutgoingADC message = new OutgoingADC(protocol);
			message.setAddtoList(MsnList.AL);
			message.setEmail(contact.getEmail());
			messenger.send(message);
		} else {
			OutgoingADD message = new OutgoingADD(protocol);
			message.setAddtoList(MsnList.AL);
			message.setEmail(contact.getEmail());
			message.setFriendlyName(contact.getEmail().getEmailAddress());
			messenger.send(message,false);
		}
		/* SEND SYN to NS/AS, Let Server Synchorize RL(Reversed List) */
		OutgoingSYN osync = new OutgoingSYN(messenger.getActualMsnProtocol());
		osync.setCachedVersion("0 0"); //now simple use "0 0", not very imporant
		messenger.send(osync, false); //Non Block
		Logger.log(contact.getEmail().getEmailAddress() + " Joined JiWai");
	}
	
	public void contactRemovedMe(MsnMessenger messenger,
            MsnContact contact){
		//Remove From AL
		OutgoingREM message = new OutgoingREM(messenger.getActualMsnProtocol());
		message.setRemoveFromList(MsnList.AL);
		message.setEmail(contact.getEmail());
		messenger.send(message, false);
		/* SEND SYN to NS/AS, Let Server Synchorize RL(Reversed List) */
		OutgoingSYN osync = new OutgoingSYN(messenger.getActualMsnProtocol());
		osync.setCachedVersion("0 0"); //now simple use "0 0", not very imporant
		messenger.send(osync, false); //Non Block
		Logger.log(contact.getEmail().getEmailAddress() + " Leaved JiWai");
	}
	
	public void contactListInitCompleted(MsnMessenger messenger){
		//todo
	}
	
	public boolean mtProcessing(MoMtMessage msg) {
		String email = msg.getAddress();
		String body  = msg.getBody();
		Email remail = Email.parseStr(email);
		
		//We dont want to send typing control message
		if( false == sendText(remail, body) ){
			try {
				messenger.sendText(remail, body);
			}catch(Exception e){
				Logger.logError("[MT] ["+DEVICE+"://"+email+"] Session closed");	
				doReconnect();
				return false;
			}
		}
		return true;
	}

	private void doReconnect(){
		worker.stopProcessor();	
		new Thread(this).start();
	}

	private boolean sendText(final Email email, final String text) {
		if (email == null || text == null)
			return true;
		MsnSwitchboard[] switchboards = messenger.getActiveSwitchboards();
		for (int i = 0; i < switchboards.length; i++) {
			if (switchboards[i].containContact(email)
					&& switchboards[i].getAllContacts().length == 1) {
				MsnInstantMessage instanceMessage = new MsnInstantMessage();
				instanceMessage.setContent(text);
				switchboards[i].sendMessage(instanceMessage);
				return true;
			}
		}
		return false;
	}
}

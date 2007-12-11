import java.io.FileInputStream;
import java.util.Properties;

import net.sf.jml.event.*;
import net.sf.jml.message.*;

import net.sf.jml.impl.*;
import net.sf.jml.*;

/**
 * @author AKA shwdai@gmail.com
 */
public class MsnBuddyRobot extends MsnAdapter {

	public String mUsername = null;
	public String mPassword = null;
	public MsnMessenger messenger = null;

	public void init() {
		mUsername = System.getProperty("username", null);
		mPassword = System.getProperty("password", null);

		if ( mUsername == null || mPassword == null ) {
			System.exit(1);
		}
	}

	public MsnBuddyRobot(){
		init();
	}
	
	protected void initMessenger(MsnMessenger messenger) {
		messenger.addListener(this);
	}

	public void run() {
		messenger = MsnMessengerFactory.createMsnMessenger( mUsername, mPassword );
		messenger.setSupportedProtocol( MsnProtocol.getAllSupportedProtocol() );

		initMessenger(messenger);
		messenger.login();
	}

	public static void main(String[] args) throws Exception {
		new MsnBuddyRobot().run();
	}

	/** *********** Event Method ************* */
	public void loginCompleted(MsnMessenger messenger) {
	}


	public void exceptionCaught(MsnMessenger messenger, Throwable throwable) {
		if (throwable.getClass().getName().equals("net.sf.jml.exception.IncorrectPasswordException")) {
			System.exit(1);
		}
		else if (throwable.getClass().getName().equals("net.sf.jml.exception.MsnProtocolException")) {
			System.exit(2);
		}
		else if (throwable.getClass().getName().equals("net.sf.jml.exception.MsgNotSendException")) {
			System.exit(3);
		}
		else if (throwable.getClass().getName().equals("net.sf.jml.exception.UnsupportedProtocolException")) {
			System.exit(4);
		}
        }

	public void contactListInitCompleted(MsnMessenger messenger){
		MsnContact[] list = messenger.getContactList().getContactsInList(MsnList.FL);
		for( MsnContact contact : list ){
			System.out.println( contact.getEmail().getEmailAddress() );
		}
		System.exit(0);
	}
}

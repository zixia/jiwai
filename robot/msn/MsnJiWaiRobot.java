import java.io.File;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Hashtable;
import java.util.LinkedList;
import java.util.Properties;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import net.sf.jml.event.*;
import net.sf.jml.exception.*;
import net.sf.jml.message.*;
import net.sf.jml.protocol.*;
import net.sf.jml.protocol.incoming.*;
import net.sf.jml.protocol.outgoing.*;
import net.sf.jml.message.p2p.*;
import net.sf.jml.protocol.msnftp.*;
import net.sf.jml.util.*;

import net.sf.jml.impl.*;
import net.sf.jml.*;

/**
 * @author AKA shwdai@gmail.com
 */
public class MsnJiWaiRobot extends MsnAdapter {

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

	public static Hashtable<String,String> onlineList = new Hashtable<String,String>();

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

	}

	protected void initMessenger(MsnMessenger messenger) {
		messenger.addListener(this);
	}

	public void start() {
		// create MsnMessenger instance
		messenger = MsnMessengerFactory.createMsnMessenger(
				MsnJiWaiRobot.mEmail, MsnJiWaiRobot.mPassword);

		// MsnMessenger support all protocols by default
		messenger.setSupportedProtocol(MsnProtocol.getAllSupportedProtocol());
		messenger.getOwner().setInitStatus(MsnUserStatus.ONLINE);
		messenger.getOwner().setNotifyMeWhenSomeoneAddedMe(false);
		try { 
			MsnObject displayPicture = MsnObject.getInstance( MsnJiWaiRobot.mEmail,"./resource/UserTile/head.png"); 
			messenger.getOwner().setInitDisplayPicture(displayPicture); 
		} catch (Exception ex) { 
			log("can't load user tile.");
		}
		// default init status is online,
		// messenger.getOwner().setInitStatus(MsnUserStatus.BUSY);

		// log incoming message
		// messenger.setLogIncoming(true);

		// log outgoing message
		initMessenger(messenger);
		messenger.login();
	}

	public static void main(String[] args) throws Exception {
		MsnJiWaiRobot robot = new MsnJiWaiRobot();
		robot.start();
	}

	/** *********** Event Method ************* */
	public void loginCompleted(MsnMessenger messenger) {
		log("Login Successed");
		messenger.getOwner().setDisplayName(mDisplayName);
		new Thread( new JiWaiMessageProcess()).start();
	}

	public void contactStatusChanged(MsnMessenger messenger, MsnContact contact) {
		/*String oldName = contact.getOldDisplayName();
		String nowName = contact.getDisplayName();
		*/
		/*if (!oldName.equals(nowName)
				&& !(
						onlineList.containsKey(contact.getEmail().getEmailAddress())
						&& onlineList.get(contact.getEmail().getEmailAddress()).equals("nowName")
				)
				
		) {
			log(contact.getEmail() + " Change name To: " + nowName);
			writeMoMessage(contact.getEmail().toString(), nowName, System
					.currentTimeMillis(), true);
		}
		
		if( contact.getStatus().getDisplayStatus().equals("OFFLINE")){
			onlineList.remove(contact.getEmail().getEmailAddress());
		}*/
	}

	public void instantMessageReceived(MsnSwitchboard switchboard,
            MsnInstantMessage message,
            MsnContact contact){
			writeMoMessage(contact.getEmail().getEmailAddress(), message.getContent(), System
				.currentTimeMillis(),false);
	}
	
	public void contactAddedMe(MsnMessenger messenger,
            MsnContact contact){
		messenger.addFriend(contact.getEmail(), contact.getDisplayName() );
		messenger.removeFriend(contact.getEmail(), false);
		log(contact.getEmail().getEmailAddress() + " Joined JiWai");
	}
	
	public void contactRemovedMe(MsnMessenger messenger,
            MsnContact contact){
		onlineList.remove(contact.getEmail().getEmailAddress());
	}
	
	public void contactListInitCompleted(MsnMessenger messenger){
		MsnContact[] contacts = messenger.getContactList().getContacts();
		for(int i=0;i< contacts.length; i++){
			if( !contacts[i].getStatus().getDisplayStatus().equals("OFFLINE")){
				onlineList.put(contacts[i].getEmail().getEmailAddress(), contacts[i].getDisplayName());
			}
		}
	}
	
	/** *********** Self Method ******** */
	public static void log(String message) {
		System.out.println(message);
	}

	public void sendTextMessage(String email, String body) {
		Email remail = Email.parseStr(email);
		messenger.sendText(remail, body);
		log("Send to "+email + ":"+ body);
	}

	public void writeMoMessage(String address, String body, long timestamp,
			boolean nick) {
		SimpleDateFormat sdf = new SimpleDateFormat("MM-dd HH:mm");
		log(sdf.format(new Date(timestamp)) + " " + address + ": " + body);

		long time_millis, sec, msec;
		String file_path_name;
		File msg_file;

		do {
			time_millis = System.currentTimeMillis();
			sec = time_millis / 1000;
			msec = time_millis - (time_millis / 1000) * 1000;
			file_path_name = mQueuePathMo + "msn__" + address + "__" + sec
					+ "_" + msec;
			msg_file = new File(file_path_name);
		} while (msg_file.exists());

		String file_content;

		file_content = "ADDRESS: msn://" + address + "\n";
		file_content += "\n";
		file_content += body;

		try {
			FileWriter fw = new FileWriter(msg_file);
			fw.write(file_content);
			fw.close();
		} catch (Exception e) {
			log("file writer exception for " + file_path_name);
		}
	}

	public LinkedList<Hashtable> getQueueMt() {
		LinkedList<Hashtable> robot_msgs = new LinkedList<Hashtable>();

		Hashtable<String, String> robot_msg = new Hashtable<String, String>();

		try {
			File files[] = new File(MsnJiWaiRobot.mQueuePathMt).listFiles();

			String file_name;
			String file_content;
			char[] buf = new char[1024];

			Matcher matcher;

			String head, body, address;

			for (int i = 0; i < files.length; i++) {
				if (!files[i].isFile())
					continue;

				file_name = files[i].getName();

				if (0 != file_name.indexOf("msn__")) {
					files[i].delete();
					log("jiwaiQueueMt found unknown file: " + file_name
							+ ", skipped & deleted");
					continue;
				}

				// log("There is a file " + files[i].getName() + " in this
				// diretory");

				int n = (new FileReader(files[i])).read(buf, 0, 1024);
				file_content = new String(buf, 0, n);

				// log(file_content);

				matcher = patternFile.matcher(file_content);

				if (!matcher.find()) {
					log("jiwaiQueueMt fount un-parse data: " + file_content
							+ ", skiped & deleted");
					files[i].delete();
					continue;
				}

				head = matcher.group(1);
				body = matcher.group(2);

				matcher = patternHead.matcher(head);

				if (!matcher.find()) {
					log("jiwaiQueueMt fount un-parse head data: " + head
							+ ", skiped & deleted");
					files[i].delete();
					continue;
				}

				address = matcher.group(1);

				robot_msg.put("address", address);
				robot_msg.put("body", body.trim());
				robot_msg.put("file", files[i].getCanonicalPath());

				robot_msgs.add((Hashtable) robot_msg.clone());
			}

		} catch (Exception e) {
			e.printStackTrace();
			log("jiwaiQueueMt readdir failed");
			System.exit(1);
		}

		return robot_msgs;
	}

	@SuppressWarnings("unchecked")
	class JiWaiMessageProcess implements Runnable {

		public void run() {
			log("runed");
			LinkedList robot_msgs;
			Hashtable<String, String> robot_msg;
			String address;
			String body, file;

			while (true) {
				System.out.print(".");
				robot_msgs = getQueueMt();
				while (!robot_msgs.isEmpty()) {
					System.out.print("*");
					// log ( "fount new mt msg" );
					robot_msg = (Hashtable<String, String>) robot_msgs.removeFirst();
					try {
						address = robot_msg.get("address");
						body = robot_msg.get("body");
						file = robot_msg.get("file");

						sendTextMessage(address, body);

						(new File(file)).delete();

						log(new String("MT: ") + address + ": [" + body + "]");

					} catch (Exception e) {
						log("iconv failed");
					}
				}

				try {
					Thread.sleep(3000);
				} catch (Exception e) {
				}
				// break;
			}
		}
	}
}

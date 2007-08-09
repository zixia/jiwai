import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.PrintWriter;
import java.util.Properties;
import java.util.Enumeration;

import java.util.concurrent.*;

import net.sf.jml.event.*;
import net.sf.jml.message.*;
import net.sf.jml.protocol.outgoing.*;

import net.sf.jml.impl.*;
import net.sf.jml.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;

/**
 * @author AKA shwdai@gmail.com
 */
public class MsnJiWaiRobot extends MsnAdapter implements MoMtProcessor{

	public static MsnJiWaiRobot robot = null;

	//config infomation
	public String _mEmail = null;
	public static String[] mEmail = null; 
	public static String mPassword = null;
	public String mQueuePath = null;
	public String mQueuePathMo = null;
	public String mQueuePathMt = null;
	public String _mDisplayName = "叽歪一下吧！（发送HELP了解更多）";
	public String mDisplayName = null;
	
	//device type
	public final static String DEVICE = "msn";
	
	//worker
	public static MoMtWorker worker = null;
	
	//userMap;
	public static ConcurrentHashMap<String,String[]> onlineFriends = new ConcurrentHashMap<String, String[]>();
	public static ConcurrentHashMap<String,MsnInstance> instances = new ConcurrentHashMap<String, MsnInstance>();

	//onlinePort
	public static int onlinePort = 55010;
	public static String mOnlineScript = null;

	/**
	 * init robot
	 */
	public void init() {

		Logger.initialize(DEVICE);
		Properties config = new Properties();

		try {
			config.load(new FileInputStream("config.ini"));
		}catch(Exception e){
		}

		try{
			onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55010") )).intValue();
		}catch(Exception e){
		}
		mOnlineScript = config.getProperty("online.script", System.getProperty("online.script") );

		mPassword = config.getProperty("msn.pass", System.getProperty("msn.pass"));
		_mEmail = config.getProperty("msn.email", System.getProperty("msn.email"));
		mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path"));
		mDisplayName = config.getProperty("msn.display.name", System.getProperty("msn.display.name", _mDisplayName));


		if (_mEmail == null || mPassword == null || mQueuePath == null) {
			Logger.logError("Please give msn(email,password) and queue(path) definition!");
			System.exit(1);
		}

		mQueuePathMo = mQueuePath + (mQueuePath.endsWith(File.separator)?"":File.separator) + "mo" + File.separator;
		mQueuePathMt = mQueuePath + (mQueuePath.endsWith(File.separator)?"":File.separator) + "mt" + File.separator;
						
		//MoMtWorker
		worker = new MoMtWorker("msn", mQueuePath, this);
		mEmail = _mEmail.replaceAll("\\s+", "").split(",");
	}

	public static void main(String[] args) throws Exception {
		robot = new MsnJiWaiRobot();
		robot.init();
		worker.startOnlineProcessor( mOnlineScript );
		for( String account : robot.mEmail ){
			loginMsnAccount( account , false );
		}
		new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
		worker.run();
	}

	public static void loginMsnAccount( String account , boolean force) {
		MsnInstance instance = null;
		if( instances.containsKey( account )  ) {
			if( false == force ) {
				return;
			} else {
				instance = instances.get( account );
				instance.doReconnect();
			}
		}else{
			instance = new MsnInstance(account, mPassword, robot);
			new Thread( instance ).start();
			instances.put( account, instance );
		}
	}

	/** *********** Event Method ************* */
	public void loginCompleted(MsnMessenger messenger) {
		String email = messenger.getOwner().getEmail().getEmailAddress();
		instances.get( email ).running = true;
		Logger.log("MSN://"+ email +" Login Successed");
		messenger.getOwner().setDisplayName(mDisplayName);
	}

	public void contactStatusChanged(MsnMessenger messenger, MsnContact contact) {
			
		String serverAddress = messenger.getOwner().getEmail().getEmailAddress();
		
		String signature = ((MsnContactImpl)contact).getPersonalMessage().trim();
		String status = contact.getStatus().getStatus();
		String email = contact.getEmail().getEmailAddress();
		
		//ONline/Offline/Away status record
		addToFriendList( contact, serverAddress );
		
		//Signature record
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
			msg.setServerAddress(serverAddress);
			msg.setBody(signature);
			worker.saveMoMessage(msg);
		}	
	}

	public void instantMessageReceived(MsnSwitchboard switchboard,
            MsnInstantMessage message,
            MsnContact contact){
		String serverAddress = switchboard.getMessenger().getOwner().getEmail().getEmailAddress();
		MoMtMessage msg = new MoMtMessage(DEVICE);
		msg.setAddress(contact.getEmail().getEmailAddress());
		msg.setServerAddress(serverAddress);
		msg.setBody(message.getContent());
		worker.saveMoMessage(msg);
	}
	
	public void contactAddedMe(MsnMessenger messenger,
            MsnContact contact){
		messenger.addFriend( contact.getEmail(), null );
		addToFriendList( contact, messenger.getOwner().getEmail().getEmailAddress() );
		( ( MsnContactListImpl ) messenger.getContactList() ).addContact( contact );
		Logger.log(contact.getEmail().getEmailAddress() + " Joined JiWai");
	}
	
	public void contactRemovedMe(MsnMessenger messenger,
            MsnContact contact){
		//Remove From FL
		messenger.removeFriend( contact.getEmail() , false);

		//Remove From AL
		OutgoingREM message = new OutgoingREM(messenger.getActualMsnProtocol());
		message.setRemoveFromList(MsnList.AL);
		message.setEmail(contact.getEmail());
		messenger.send(message, false);

		//Remove From ContactList
		( ( MsnContactListImpl ) messenger.getContactList() ).removeContactByEmail( contact.getEmail() );
		if( onlineFriends.containsKey( contact.getEmail().getEmailAddress()) ){
			String[] r = onlineFriends.get( contact.getEmail().getEmailAddress() );
			if( r[1].equals( messenger.getOwner().getEmail().getEmailAddress() ) ){
				onlineFriends.remove( contact.getEmail().getEmailAddress() );
			}
		}
	
		Logger.log(contact.getEmail().getEmailAddress() + " Leaved JiWai");
	}
	
	public void contactListInitCompleted(MsnMessenger messenger){

		MsnContact[] l = messenger.getContactList().getContactsInList(MsnList.FL);
		String serverAddress = messenger.getOwner().getEmail().getEmailAddress();
		for( MsnContact c : l ){
			addToFriendList( c, serverAddress );
		}
		//todo
	}

	public void addToFriendList(MsnContact contact, String serverAddress){
		String status = contact.getStatus().getStatus();
		String email = contact.getEmail().getEmailAddress();

		String[] s = {"Y", serverAddress};
		if( status.equals("FLN") ) {
			s[0] = "N";
		} else if( status.equals("AWY")){
			s[0] = "A";
		}

		worker.setOnlineStatus( email, s[0], serverAddress );
		onlineFriends.put(email, s);
	}

	public boolean mtProcessing(MoMtMessage msg) {
		String email = msg.getAddress();
		String serverAddress = msg.getServerAddress();

		if( serverAddress == null ){
			if( onlineFriends.containsKey(email) ){
				String[] r = onlineFriends.get(email);
				serverAddress = r[1];
			}else{
				return false;
			}
		}
		
		MsnInstance instance = instances.get(serverAddress);

		if( instance==null)
			return false;
		if( instance.running == false ) 
			return false;
		
		return instance.sendMessage(msg);
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
					
					//ADD A new JiWai Robot	
					if( 0 == line.indexOf("AJiWaiMsn: ") ){
						String ac = line.substring( "AJiWaiMsn: ".length() );
						if( ac.indexOf("@") > 0 ){
							loginMsnAccount( ac , true );
						}
						break;
					}

					if( line.equals("ROnlineScript") ){
						worker.startOnlineProcessor( mOnlineScript );
						break;
					}

					//Get Contact List Num
					if( line.equals("LJiWaiMsn") ){
						String ret = "";
						Enumeration en = instances.keys();
						while( en.hasMoreElements() ){
							String account = (String) en.nextElement();
							MsnInstance instance = instances.get( account );
							int count = instance.getFriendCount();
							ret += account + ":" + count + ";";
						}
						out( ret );
						break;
					}


					//Out by client; 
					if( line.toUpperCase().equals("EXIT") 
							|| line.toUpperCase().equals("QUIT") ){
					       	break;
					}

					String[] r = onlineFriends.get( line );
					if( null != r  ) {
						out( r[0] );
					}else{
						out( "N" );
					}

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


	static class MsnInstance implements Runnable{
		
		private MsnMessenger messenger = null;
		private MsnAdapter adapter = null;
		
		private String account = null;
		private String password = null;

		public boolean running = false;
		
		public MsnInstance(String account, String password,MsnAdapter adapter){
			this.adapter = adapter;
			this.account = account;
			this.password = password;
		}
		
		public void run() {
			// create MsnMessenger instance
			messenger = MsnMessengerFactory.createMsnMessenger(
					account, password);
			messenger.setSupportedProtocol(MsnProtocol.getAllSupportedProtocol());

			//Status ONLINE
			messenger.getOwner().setInitStatus(MsnUserStatus.ONLINE);
			messenger.getOwner().setNotifyMeWhenSomeoneAddedMe(false);
			try { 
				MsnObject displayPicture = MsnObject.getInstance( account,"./resource/UserTile/jiwai.jpg"); 
				messenger.getOwner().setInitDisplayPicture(displayPicture); 
			} catch (Exception ex) { 
				Logger.logError("Can't load " + account + "'s user tile.");
			}

			// log incoming message
			//messenger.setLogIncoming(true);
			//messenger.setLogOutgoing(true);
			
			messenger.addListener(adapter);
			messenger.login();
		}
		
		public boolean sendMessage(MoMtMessage msg) {
			String email = msg.getAddress();
			String body  = msg.getBody();
			Email remail = Email.parseStr(email);
			
			//We dont want to send typing control message
			if( false == sendText(remail, body) ){
				try {
					messenger.sendText(remail, body);
				}catch(Exception e){
					Logger.logError("[MT] ["+MsnJiWaiRobot.DEVICE+"://"+email+"] Session closed");	
					doReconnect();
					return false;
				}
			}
			return true;
		}

		private void doReconnect(){
			new Thread(this).start();
		}

		public int getFriendCount(){
			return messenger.getContactList().getContactsInList(MsnList.FL).length;
		}

		public int getAllowCount(){
			return messenger.getContactList().getContactsInList(MsnList.FL).length;
		}

		public void removeContact(MsnContact contact){
			MsnContactListImpl list = (MsnContactListImpl) messenger.getContactList();
			list.removeContactByEmail(contact.getEmail());
		}

		public void addContact(MsnContact contact){
			MsnContactListImpl list = (MsnContactListImpl) messenger.getContactList();
			list.addContact(contact);
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
}

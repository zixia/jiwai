import java.io.*;
import java.util.*;
import java.util.concurrent.*;

import ymsg.network.*;
import ymsg.network.event.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;

/**
 * @author Wang Hongwei glinus@gmail.com
 */
public class YahooJiWaiRobot implements MoMtProcessor {

    private static boolean mDebug = true;

    public static YahooJiWaiRobot mRobot = null;

	public static final String DEVICE = "yahoo";
	
	public static int onlinePort = 55060;

    /* ymsg.network.Session */
    public static Session mSession = null;
	
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

		mServer = config.getProperty("yahoo.server", System.getProperty("yahoo.server") );
		mAccount = config.getProperty("yahoo.account", System.getProperty("yahoo.account") );
		mPassword = config.getProperty("yahoo.password", System.getProperty("yahoo.password") );
		mStatus = config.getProperty("yahoo.status", _mStatus );

		mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path") );
		mOnlineScript = config.getProperty("online.script", System.getProperty("online.script") );

		try{
			onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55060") )).intValue();
		}catch(Exception e){
		}
		
		mAddress = mAccount + "@" + mServer;
		if( null== mServer ||  null==mAccount || null==mPassword || null==mQueuePath) {
			Logger.logError("Please given server|password|account|queuepath");
			System.exit(1);
		}
		
	}
	
	public static void main(String args[]) {
		mRobot = new YahooJiWaiRobot();
		worker = new MoMtWorker(DEVICE, mQueuePath, mRobot);
		worker.startOnlineProcessor( mOnlineScript );
		connect();
		new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
		mRobot.run();
	}

	public static void connect() {
		try{
            mSession = new Session();
            JWSessionListener yahooMessengerListern = new JWSessionListener();
            mSession.addSessionListener(yahooMessengerListern);

            mSession.login(mAccount, mPassword);
            worker.startProcessor();

		}catch(Exception e ){
			Logger.logError("Yahoo Login failed");
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

	public void sendPresence(){
		try {
            mSession.setStatus(mStatus, false);
			Logger.log("Send Presence Success");
		}catch(Exception e){
			worker.stopProcessor();
			connect();
		}
	}

	/**
	 * Process Normal MO
	 * @param m
	 */
	private void processMessage(SessionEvent m){
		MoMtMessage msg = new MoMtMessage(DEVICE);
		msg.setAddress(m.getFrom());
		msg.setServerAddress(mAddress);
		msg.setBody(m.getMessage());
		worker.saveMoMessage(msg);
	}

    /**
     *  Someone wanted us
     *  getTo - returns the target Yahoo id (us!)
     *  getFrom - returns the sender's Yahoo id (them!)
     *  getMessage - returns the accompanying message 
     */
    private void processContactRequest(SessionEvent m) {
        try {
            mSession.addFriend(m.getFrom(), "");
        }
        catch (IllegalStateException e) {
            Logger.logError("Not connected to Yahoo");
        }
        catch (IOException i) {
            Logger.logError("I/O problems from underlying streams");
        }
    }

    /**
     *  Callback when 
     *      friendAddedReceived     - add = true
     *      friendRemovedReceived   - add = false
     */
    private void friendCallback(SessionFriendEvent m, boolean add) {
        if (add) {
            System.out.println("Added friend " + m.getFriend());
        } else {
            System.out.println("Removed friend " + m.getFriend());
        }
    }

	public boolean mtProcessing(MoMtMessage message){
        try {
            mSession.addFriend(message.getAddress(), "");
            mSession.sendMessage(message.getAddress(), message.getBody());
        }
        catch (IOException e) {
            e.printStackTrace();
        }
		return true;
	}

    static class JWSessionListener implements SessionListener {
        /* bulk omit */
        public void buzzReceived(SessionEvent ev) {return;} 
        public void chatConnectionClosed(SessionEvent ev) {return;} 
        public void chatLogoffReceived(SessionChatEvent ev) {return;} 
        public void chatLogonReceived(SessionChatEvent ev) {return;} 
        public void chatMessageReceived(SessionChatEvent ev) {return;} 
        public void chatUserUpdateReceived(SessionChatEvent ev) {return;} 
        public void conferenceInviteDeclinedReceived(SessionConferenceEvent ev) {return;} 
        public void conferenceInviteReceived(SessionConferenceEvent ev) {return;} 
        public void conferenceLogoffReceived(SessionConferenceEvent ev) {return;} 
        public void conferenceLogonReceived(SessionConferenceEvent ev) {return;} 
        public void conferenceMessageReceived(SessionConferenceEvent ev) {return;} 
        public void fileTransferReceived(SessionFileTransferEvent ev) {return;}
        public void newMailReceived(SessionNewMailEvent ev) {return;}

        /* dealing with */
        public void connectionClosed(SessionEvent ev) {
            System.out.println(ev.getMessage());
        }
        public void contactRejectionReceived(SessionEvent ev) {
            System.out.println(ev.getMessage());
        }
        public void contactRequestReceived(SessionEvent ev) {
            mRobot.processContactRequest(ev);
        }
        public void errorMessageReceived(SessionErrorEvent ev) {
            System.out.println(ev.getMessage());
        }
        public void errorPacketReceived(SessionErrorEvent ev) {
            System.out.println(ev.getMessage());
        }
        public void friendAddedReceived(SessionFriendEvent ev) {
            mRobot.friendCallback(ev, true);
        }
        public void friendRemovedReceived(SessionFriendEvent ev) {
            mRobot.friendCallback(ev, false);
        }
        public void friendsUpdateReceived(SessionFriendEvent ev) {
            System.out.println(ev.getMessage());
        }
        public void inputExceptionThrown(SessionExceptionEvent ev) {
            System.out.println(ev.getMessage());
            Exception e = ev.getException();
            e.printStackTrace();
        }
        public void listReceived(SessionEvent ev) {
            System.out.println(ev.getMessage());
        }
        public void messageReceived(SessionEvent ev) {
            mRobot.processMessage(ev);
        }
        public void notifyReceived(SessionNotifyEvent ev) {
            System.out.println(ev.getMessage());
        }
        public void offlineMessageReceived(SessionEvent ev) {
            System.out.println(ev.getMessage());
        }
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
						connect();
						break;
					}

					//Change Signature 
					if( 0 == line.indexOf("Sig: ") ){
						String sig = line.substring( "Sig: ".length() );
						YahooJiWaiRobot.mStatus = sig;
						break;
					}

					//Count momt
					if( line.equals("CountMOMT") ){
						out( "mo:"+worker.countMo + " mt:" + worker.countMt );
						break;
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
}

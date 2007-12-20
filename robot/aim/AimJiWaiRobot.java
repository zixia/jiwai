import java.io.*;
import java.util.*;
import java.util.concurrent.*;

import net.kano.joscardemo.*;
import net.kano.joscardemo.security.*;
import net.kano.joscar.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;

// Joscar Import
import net.kano.joscardemo.*;
import net.kano.joscar.snaccmd.conn.ServiceRequest;
import net.kano.joscar.snac.SnacRequest;
import net.kano.joscar.snac.SnacRequestListener;


/**
 * @author Wang Hongwei glinus@gmail.com
 */
public class AimJiWaiRobot implements MoMtProcessor {

    public static JoscarTester mOscarSession = null;

    public static JiWaiMoListener mMoListener = null;

    private static boolean mDebug = true;

    public static AimJiWaiRobot mRobot = null;

	public static final String DEVICE = "aol";
	
	public static int onlinePort = 55060;

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

		mServer = config.getProperty("aim.server", System.getProperty("aim.server") );
		mAccount = config.getProperty("aim.account", System.getProperty("aim.account") );
		mPassword = config.getProperty("aim.password", System.getProperty("aim.password") );
		mStatus = config.getProperty("aim.status", _mStatus );

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
		mRobot = new AimJiWaiRobot();
		worker = new MoMtWorker(DEVICE, mQueuePath, mRobot);
		worker.startOnlineProcessor( mOnlineScript );
		connect();
		new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
		mRobot.run();
	}

	public static void connect() {
		try{
            mOscarSession = new JoscarTester(mAccount, mPassword);
            mMoListener = new JiWaiMoListener();
            mOscarSession.connect();
            mOscarSession.addMoListener(mMoListener);
            worker.startProcessor();
		}catch(Exception e ){
			Logger.logError("Aim Login failed");
            System.out.println(e.getStackTrace().toString());
		}finally {
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
            /* Send Status HeRE */
			Logger.log("Send Presence Success");
		}catch(Exception e){
			worker.stopProcessor();
			connect();
		}
	}

	public boolean mtProcessing(MoMtMessage message){
        try {
            mOscarSession.sendIM(message.getAddress(), message.getBody());
        }
        catch (Exception e) {
            e.printStackTrace();
        }
		return true;
	}

    public static void processMo(String sn, String text) {
        MoMtMessage msg = new MoMtMessage(DEVICE);
        msg.setAddress(sn);
        msg.setServerAddress(mAddress);
        msg.setBody(text);
        worker.saveMoMessage(msg);
    }

    static class JiWaiMoListener implements IMoProcessing {
        public void moProcessing(String sn, String text) {
            processMo(sn, text);
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
						AimJiWaiRobot.mStatus = sig;
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

import java.io.*;
import java.util.*;
import java.util.concurrent.*;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.Properties;
import java.util.List;
import java.util.Random;
import java.awt.Color;

import com.incesoft.botplatform.sdk.RobotException;
import com.incesoft.botplatform.sdk.RobotServer;
import com.incesoft.botplatform.sdk.RobotServerFactory;
import com.incesoft.botplatform.sdk.RobotHandler;
import com.incesoft.botplatform.sdk.RobotMessage;
import com.incesoft.botplatform.sdk.RobotSession;
import com.incesoft.botplatform.sdk.RobotUser;

import de.jiwai.robot.*;
import de.jiwai.util.*;

/**
 * @author Wang Hongwei glinus@gmail.com
 */
public class XiaoiJiWaiRobot implements MoMtProcessor {

    private static Hashtable<String, RobotSession> mBuddySession
        = new Hashtable<String, RobotSession> ();

    private static Hashtable<String, ArrayList<String>> mBuddyMessages
        = new Hashtable<String, ArrayList<String>>();

    private static String mBuddyCache = "msn.cache";

    private static Hashtable<String, String> mBuddyRobot
        = new Hashtable<String, String>();

    /**
     * Xiaoi instance
     */
    public static RobotServer mXiaoiServer  = null;

    public static JiWaiSessionListener mMoListener = null;

    public static XiaoiJiWaiRobot mRobot = null;

    public static final String DEVICE = "msn";
    
    public static int onlinePort    = 55030;

    public static String mSpid      = null;
    public static String mAccount   = null;
    public static String mAddress   = null;
    public static String mPassword  = null;
    public static String mQueuePath = null;
    public static String _mStatus   = "(#)(#)叽歪一下吧！（发送HELP了解更多）";
    public static String mStatus    = null;
    public static String mServer    = null;
    public static String mPort      = null;
    public static String mOnlineScript = null;

    public static String[] mSubAccounts = null;
    public static String _mSubAccounts = null;
    
    public static MoMtWorker worker = null;
    
    static {
        Logger.initialize(DEVICE);
        Properties config = new Properties();
        try {
            config.load(new FileInputStream("config.ini"));
        }catch(IOException e){
        }

        mSpid       = config.getProperty("xiaoi.spid",      System.getProperty("xiaoi.spid") );
        mAccount    = config.getProperty("xiaoi.account",   System.getProperty("xiaoi.account") );
        mPassword   = config.getProperty("xiaoi.password",  System.getProperty("xiaoi.password") );
        mServer     = config.getProperty("xiaoi.server",    System.getProperty("xiaoi.server"));
        mPort       = config.getProperty("xiaoi.port",      System.getProperty("xiaoi.port"));
        mStatus     = config.getProperty("xiaoi.status", _mStatus );
        mQueuePath  = config.getProperty("queue.path", System.getProperty("queue.path") );
        mOnlineScript = config.getProperty("online.script", System.getProperty("online.script") );
		_mSubAccounts = config.getProperty("xiaoi.subaccounts", System.getProperty("xiaoi.subaccounts"));

        try{
            onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55066") )).intValue();
        }catch(Exception e){
        }

        mAddress = mAccount;
        
        if( null == mSpid || null==mAccount || null==mPassword || null==mQueuePath) {
            Logger.logError("Please given server|password|account|queuepath");
            System.exit(1);
        }

		mSubAccounts = _mSubAccounts.replaceAll("\\s+", "").split(",");
        htDeSerialize();
    }
    
    public static void htSerialize() {
        try {
            FileOutputStream fos = new FileOutputStream(mBuddyCache);
            ObjectOutputStream oos = new ObjectOutputStream(fos);
            oos.writeObject(mBuddyRobot);
            oos.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private static void htDeSerialize() {
        try {
            FileInputStream fis = new FileInputStream(mBuddyCache);
            ObjectInputStream ois = new ObjectInputStream(fis);
            mBuddyRobot = (Hashtable<String, String>) ois.readObject();
            ois.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    public static void main(String args[]) {
        mRobot = new XiaoiJiWaiRobot();
        worker = new MoMtWorker(DEVICE, mQueuePath, mRobot);
        worker.startOnlineProcessor( mOnlineScript );
        connect();
        new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
        mRobot.run();
    }

    public static void connect() {
        try{
            mXiaoiServer = RobotServerFactory.getInstance().createRobotServer(mServer,
                Integer.parseInt(mPort));
            mXiaoiServer.setReconnectedSupport(true);
            mMoListener = new JiWaiSessionListener(mXiaoiServer);
            mXiaoiServer.setRobotHandler(mMoListener);
            mXiaoiServer.login(mSpid, mPassword);
            worker.startProcessor();
        }catch(Exception e ){
            Logger.logError("Xiaoi Login failed");
            System.out.println(e);
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
            mXiaoiServer.setDisplayName(mStatus);
            htSerialize();
            Logger.log("Send Presence Success");
        }catch(Exception e){
            worker.stopProcessor();
            connect();
        }
    }

    private static void mtProcessingBySession(RobotSession s, String text) {
        RobotMessage m = s.createMessage();
        m.setString(text);
        m.setFontColor(java.awt.Color.black);
        try {
            s.send(m);
        } catch (RobotException e) {
            e.printStackTrace();
        }
    }

    public boolean mtProcessing(MoMtMessage message) {
        String buddy = message.getAddress();
        String text = message.getBody();
        String descAccount = mBuddyRobot.get(buddy);
        if (null == descAccount) descAccount = mAccount;
        RobotSession s = mBuddySession.get(buddy);
        try {
            if (null == s || s.isClosed()) {
                try {
                    if (null == mBuddyMessages.get(buddy)) {
                        mBuddyMessages.put(buddy, new ArrayList<String>());
                    }
                    mBuddyMessages.get(buddy).add(text);
                    mXiaoiServer.createSession(descAccount, buddy);
                } catch (RobotException e) {
                    mXiaoiServer.pushMessage(descAccount, buddy,  text);
                }
            } else {
                mtProcessingBySession(s, text);
            }
        } catch (RobotException e) {
            e.printStackTrace();
            return false;
        }
        return true;
    }

    public static void processMo(String buddy, String text, String robot) {
        MoMtMessage msg = new MoMtMessage(DEVICE);
        msg.setAddress(buddy);
        msg.setServerAddress(robot);
        msg.setBody(text);
        worker.saveMoMessage(msg);
    }

    public static void processSig(String robot, String buddy, String sig) {
        if (sig == null || sig.equals("")) return;
        MoMtMessage msg = new MoMtMessage(DEVICE);
        msg.setMsgtype(MoMtMessage.TYPE_SIG);
        msg.setAddress(buddy);
        msg.setServerAddress(robot);
        msg.setBody(sig);
        worker.saveMoMessage(msg);
    }

    static class JiWaiSessionListener implements RobotHandler {
        private RobotServer mRobotServer = null;

        public JiWaiSessionListener(RobotServer s) {
            mRobotServer = s;
        }

        public void sessionOpened(RobotSession session) {
            String buddy = session.getUser().getID();
            String robot = session.getRobot();
            mBuddySession.put(session.getUser().getID(), session);
            mBuddyRobot.put(buddy, robot);
            try {
                mRobotServer.setDisplayName(mStatus);
                ArrayList<String> msgs = mBuddyMessages.get(buddy);
                if (null == msgs) return;
                for (String msg : msgs) {
                    mtProcessingBySession(session, msg);
                }
                mBuddyMessages.remove(buddy);
            } catch (RobotException e) {
                e.printStackTrace();
            }
        }

        public void sessionClosed(RobotSession session) {
            mBuddySession.remove(session.getUser().getID());
        }

        public void messageReceived(RobotSession session, RobotMessage message) {
            String command = message.getString();
            String buddy = session.getUser().getID();
            String robot = session.getRobot();
            try {
                if (0 == command.indexOf("get nudge")) {
                    session.sendNudge();
                } else if (0 == command.indexOf("get wink")) {
                    session.sendWink("115.mct", "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");
                } else {
                    processMo(buddy, command, robot);
                    mBuddySession.put(buddy, session);
                }
            } catch (RobotException e) {
                return;
            }
        }

        public void personalMessageUpdated(String robot, String user, String personalMessage) {
            processSig(robot, user, personalMessage);
        }

        public void userAdd(String robot,String user) {
            mBuddyRobot.put(user, robot);
        }

        public void userRemove(String robot, String user) {
            mBuddyRobot.remove(user);
        }

        public void nudgeReceived(RobotSession session) { }
        public void activityAccepted(RobotSession session) { }
        public void activityRejected(RobotSession session) { }
        public void exceptionCaught(RobotSession session, Throwable cause) { }
        public void activityClosed(RobotSession session) { }
        public void activityLoaded(RobotSession session) { }
        public void activityReceived(RobotSession session, String data) { }
        public void backgroundAccepted(RobotSession session) { }
        public void backgroundRejected(RobotSession session) { }
        public void backgroundTransferEnded(RobotSession session) { }
        public void fileAccepted(RobotSession session) { }
        public void fileRejected(RobotSession session) { }
        public void fileTransferEnded(RobotSession session) { }
        public void userJoined(RobotSession session, RobotUser user) { }
        public void userLeft(RobotSession session, RobotUser user) { }
        public void webcamAccepted(RobotSession session) { }
        public void webcamRejected(RobotSession session) { }
        public void userUpdated(RobotUser user) { }
        public void contactListReceived(String robot, List<RobotUser> friendList) { }
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
                        XiaoiJiWaiRobot.mStatus = sig;
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

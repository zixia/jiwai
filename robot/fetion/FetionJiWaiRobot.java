import java.io.*;
import java.util.*;
import java.util.regex.Pattern;
import java.util.regex.Matcher;
import java.util.concurrent.*;
import java.text.SimpleDateFormat;

import fetion.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;

/**
 * @author Wang Hongwei glinus@gmail.com
 */
public class FetionJiWaiRobot implements MoMtProcessor {

    // sid -> sip
    private static Hashtable<String, String> mSidSip
        = new Hashtable<String, String>();

    // sid -> sip serialize
    private static String mSipCacheFile = "sip.cache";

    // sid -> robot
    private static Hashtable<String, String> mSidServer
        = new Hashtable<String, String>();

    // sid -> robot serialize
    private static String mBuddyCacheFile = "buddy.cache";

    // robot -> instance
    private static ConcurrentHashMap<String, HelloFetion> mInstances
        = new ConcurrentHashMap<String, HelloFetion>();

    private static HashSet<String> mDupBuddyRequest = new HashSet<String>();

    private static String mOriginalTime = null;

    private static Random mRandomGen = null;

    public static FetionJiWaiRobot mRobot = null;

    public static final String DEVICE = "fetion";
    
    public static int onlinePort    = 55080;

    public static String mSid       = null;
    public static String mPrimaryAccount   = null;
    public static String _mSubAccounts  = null;
    public static String[] mSubAccounts = null;
    public static String mPassword  = null;
    public static String mQueuePath = null;
    public static String _mStatus   = "叽歪";
    public static String mStatus    = null;
    public static String mOnlineScript = null;
    
    public static MoMtWorker worker = null;
    
    static {
        Logger.initialize(DEVICE);
        Properties config = new Properties();
        try {
            config.load(new FileInputStream("config.ini"));
        }catch(IOException e){
        }

        mPrimaryAccount = config.getProperty("fetion.account", System.getProperty("fetion.account") );
        _mSubAccounts   = config.getProperty("fetion.subaccounts", System.getProperty("fetion.subaccounts") );
        mPassword   = config.getProperty("fetion.password", System.getProperty("fetion.password") );
        mStatus     = config.getProperty("fetion.status", _mStatus );

        mQueuePath  = config.getProperty("queue.path", System.getProperty("queue.path") );
        mOnlineScript = config.getProperty("online.script", System.getProperty("online.script") );

        try{
            onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55060") )).intValue();
        }catch(Exception e){
        }
        
        if( null==mPrimaryAccount || null==mPassword || null==mQueuePath || null==_mSubAccounts) {
            Logger.logError("Please given server|password|account|queuepath");
            System.exit(1);
        }

        mSubAccounts = _mSubAccounts.replaceAll("\\s+", "").split(",");
        mRandomGen = new Random(Calendar.getInstance().getTimeInMillis());
        htDeSerialize();
    }
    
    public static void main(String args[]) {
        mRobot = new FetionJiWaiRobot();
        worker = new MoMtWorker(DEVICE, mQueuePath, mRobot);
        connect(false);
        new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
        mRobot.run();
    }

    private static void realConnect(String subAccount, boolean force) {
        HelloFetion instance = null;
        if (mInstances.containsKey(subAccount)) {
            if (force) {
                instance = mInstances.get(subAccount);
                if (null != instance)
                    instance.Logout();
                mInstances.remove(subAccount);
            } else {
                return;
            }
        }
        instance = new HelloFetion(subAccount, mPassword);
        instance.Login();
        instance.setNickname(mStatus);
        instance.getContactSet();
        mInstances.put(subAccount, instance);
        instance.addMoListener(new JiWaiFetionListener());
    }

    public static void connect(boolean force) {
        try{
            worker.startOnlineProcessor( mOnlineScript );
            mOriginalTime = (new SimpleDateFormat("MM/dd HH:mm:ss")).format(new Date());

            realConnect(mPrimaryAccount, force);

            if (mSubAccounts != null) {
                for (String subAccount : mSubAccounts) {
                    realConnect(subAccount, force);
                }
            }

            worker.startProcessor();
        }catch(Exception e ){
            Logger.logError("Fetion Login failed");
            e.printStackTrace();
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
            htSerialize();
            HelloFetion instance = mInstances.get(mPrimaryAccount);
            instance.heartBeat();
            instance.setNickname(mStatus);

            for (String subAccount : mSubAccounts) {
                instance = mInstances.get(subAccount);
                instance.heartBeat();
                instance.setNickname(mStatus);
            }

            Logger.log("Send Presence Success");
        }catch(Exception e){
            worker.stopProcessor();
            connect(true);
        }
    }

    public boolean mtProcessing(MoMtMessage message){
        try {
            String sid = message.getAddress();
            String robot = message.getServerAddress();
            HelloFetion instance = null;

            if (!mSidSip.containsKey(sid)) return false;
            if (robot == null && !mSidServer.containsKey(sid)) return false;

            if (robot == null) {
                robot = mSidServer.get(sid);
            }

            if (robot == null) return false;

            instance = mInstances.get(robot);
            instance.sendMessage(mSidSip.get(sid), message.getBody());
        }
        catch (Exception e) {
            e.printStackTrace();
            return false;
        }
        return true;
    }

    public static void htSerialize() {
        try {
            FileOutputStream fos = new FileOutputStream(mSipCacheFile);
            ObjectOutputStream oos = new ObjectOutputStream(fos);
            oos.writeObject(mSidSip);
            oos.close();

            fos = new FileOutputStream(mBuddyCacheFile);
            oos = new ObjectOutputStream(fos);
            oos.writeObject(mSidServer);
            oos.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private static void htDeSerialize() {
        try {
            FileInputStream fis = new FileInputStream(mSipCacheFile);
            ObjectInputStream ois = new ObjectInputStream(fis);
            mSidSip = (Hashtable<String, String>) ois.readObject();
            ois.close();

            fis = new FileInputStream(mBuddyCacheFile);
            ois = new ObjectInputStream(fis);
            mSidServer = (Hashtable<String, String>) ois.readObject();
            ois.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private static String getSidFromSip(String sip) {
        String sid = null;
        try {
            sid = sip.substring(sip.indexOf("sip:") + 4, sip.indexOf("@fetion.com.cn"));
        } catch (Exception e) {
            return null;
        }
        return sid;
    }

    /**
     *  @return true to continue processing, false the otherwise
     */
    private static boolean processCommand(String sip, String text, String robot) {
        String command = text.toLowerCase();
        if (mInstances.containsKey(robot) && command.equals("uptime")) {
            HelloFetion instance = mInstances.get(robot);
            if (instance != null) {
                instance.sendMessage(sip, mOriginalTime);
                return false;
            }
        }
        return true;
    }

    public static void processMo(String sip, String text, String robot) {
        String sid = getSidFromSip(sip);
        if (null != sid) {
            mSidSip.put(sid, sip);
            String command = text.toLowerCase();
            if (processCommand(sip, text, robot)) {
                MoMtMessage msg = new MoMtMessage(DEVICE);
                msg.setAddress(sid);
                msg.setServerAddress(robot);
                msg.setBody(text);
                worker.saveMoMessage(msg);
            }
        }
    }

    private static String assignRobot() {
        int index = mRandomGen.nextInt(mSubAccounts.length);
        return mSubAccounts[index];
    }

    public static void processBuddyRequest(String sip, String robot) {
        HelloFetion session = null;
        String sid = getSidFromSip(sip);
        if (null != sid) {
            mSidSip.put(sid, sip);
            if (mSidServer.containsKey(sid)) {
                if (null != (session = mInstances.get( mSidServer.get(sid) ))
                    && !mPrimaryAccount.equals(robot)
		    && !mDupBuddyRequest.contains(sip)) {
                    session.addContact(sip);
		    mDupBuddyRequest.add(sip);
                    Logger.log("Dup buddy request " + sip + " from " + robot);
                }
                return;
            }
            if (robot.equals(mPrimaryAccount)) {
                robot = assignRobot();
            }
            if (null == (session = mInstances.get(robot))) {
                Logger.logError("Instance Not Found");
                realConnect(robot, true);
                return;
            }
            session.addContact(sip);
            addToFriendList(sid, robot);
            Logger.log("Assign robot " + robot + " for buddy " + sip);
        } else {
            Logger.logError("Malformed SIP Address");
        }
    }

    public static void processBuddyList(String list, String robot) {
        Pattern sipPattern = Pattern.compile("buddy\\s+uri=\"(sip:(\\d+)@fetion.com.cn;p=\\d+)\"");
        Matcher sipMatcher = sipPattern.matcher(list);

        while (sipMatcher.find()) {
            String sip = sipMatcher.group(1);
            String sid = sipMatcher.group(2);
            addToFriendList(sid, robot);
            mSidSip.put(sid, sip);
        }
    }

    private static void addToFriendList(String sid, String robot) {
        mSidServer.put(sid, robot);
        worker.setOnlineStatus(sid, "Y", robot);
    }

    static class JiWaiFetionListener implements IMoListener {
        public void triggerMo(String sip, String text, String robot) {
            processMo(sip, text, robot);
        }
        public void triggerBuddyRequest(String sip, String robot) {
            processBuddyRequest(sip, robot);
        }
        public void triggerBuddyListReceived(String list, String robot) {
            processBuddyList(list, robot);
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
                        worker.stopProcessor();
                        connect(true);
                        break;
                    }

                    //Change Signature 
                    if( 0 == line.indexOf("Sig: ") ){
                        String sig = line.substring( "Sig: ".length() );
                        FetionJiWaiRobot.mStatus = sig;
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

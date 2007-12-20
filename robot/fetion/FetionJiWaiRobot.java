import java.io.*;
import java.util.*;
import java.util.concurrent.*;

import fetion.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;

/**
 * @author Wang Hongwei glinus@gmail.com
 */
public class FetionJiWaiRobot implements MoMtProcessor {

    private static Hashtable<String, String> mSidSip = new Hashtable<String, String>();

    /**
     * Fetion instance
     */
    public static HelloFetion mFetionSession  = null;

    public static JiWaiFetionListener mMoListener = null;

    public static FetionJiWaiRobot mRobot = null;

    public static final String DEVICE = "fetion";
    
    public static int onlinePort    = 55080;

    public static String mSid       = null;
    public static String mAccount   = null;
    public static String mPassword  = null;
    public static String mQueuePath = null;
    public static String _mStatus   = "叽歪一下吧！（发送HELP了解更多）";
    public static String mStatus    = null;
    public static String mAddress   = null;
    public static String mOnlineScript = null;
    
    public static MoMtWorker worker = null;
    
    static {
        Logger.initialize(DEVICE);
        Properties config = new Properties();
        try {
            config.load(new FileInputStream("config.ini"));
        }catch(IOException e){
        }

        mAccount    = config.getProperty("fetion.account", System.getProperty("fetion.account") );
        mPassword   = config.getProperty("fetion.password", System.getProperty("fetion.password") );
        mStatus     = config.getProperty("fetion.status", _mStatus );

        mQueuePath  = config.getProperty("queue.path", System.getProperty("queue.path") );
        mOnlineScript = config.getProperty("online.script", System.getProperty("online.script") );

        try{
            onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55060") )).intValue();
        }catch(Exception e){
        }
        
        /* no difference between the mAddress and mAccount, right here, right now */
        mAddress = mAccount;
        if( null==mAccount || null==mPassword || null==mQueuePath) {
            Logger.logError("Please given server|password|account|queuepath");
            System.exit(1);
        }
        
    }
    
    public static void main(String args[]) {
        mRobot = new FetionJiWaiRobot();
        worker = new MoMtWorker(DEVICE, mQueuePath, mRobot);
        worker.startOnlineProcessor( mOnlineScript );
        connect();
        new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
        mRobot.run();
    }

    public static void connect() {
        try{
            mFetionSession = new HelloFetion(mAddress, mPassword);
            mMoListener = new JiWaiFetionListener();
            mFetionSession.Login();
            mFetionSession.setNickname(mStatus);
            mFetionSession.addMoListener(mMoListener);
            worker.startProcessor();
        }catch(Exception e ){
            Logger.logError("Fetion Login failed");
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
            mFetionSession.heartBeat();
            Logger.log("Send Presence Success");
        }catch(Exception e){
            worker.stopProcessor();
            connect();
        }
    }

    public boolean mtProcessing(MoMtMessage message){
        try {
            String sid = message.getAddress();
            if (mSidSip.containsKey(sid)) {
                mFetionSession.sendMessage(mSidSip.get(sid), message.getBody());
            }
        }
        catch (Exception e) {
            e.printStackTrace();
        }
        return true;
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

    public static void processMo(String sip, String text) {
        String sid = getSidFromSip(sip);
        if (null != sid) {
            mSidSip.put(sid, sip);
            MoMtMessage msg = new MoMtMessage(DEVICE);
            msg.setAddress(sid);
            msg.setServerAddress(mAddress);
            msg.setBody(text);
            worker.saveMoMessage(msg);
        }
    }

    static class JiWaiFetionListener implements IMoListener {
        public void triggerMo(String sip, String text) {
            processMo(sip, text);
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

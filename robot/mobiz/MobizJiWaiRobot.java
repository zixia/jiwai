import java.io.*;
import java.util.*;
import java.util.concurrent.*;
import java.text.SimpleDateFormat;

import de.jiwai.robot.*;
import de.jiwai.util.*;

import com.example.www.*;
import com.intermobiz.*;

/**
 * @author Wang Hongwei glinus@gmail.com
 */
public class MobizJiWaiRobot implements MoMtProcessor {

    /**
     * Mobiz instance
     */
    public static JiWaiMobizSession mMobizSession  = null;

    public static JiWaiMobizListener mMoListener = null;

    public static MobizJiWaiRobot mRobot = null;

    public static final String DEVICE = "mobiz";
    
    public static int onlinePort    = 55100;
    public static int mDebugLevel   = 0;
    public static String mAddress   = null;
    public static String mPassword  = null;
    public static String mQueuePath = null;
    public static String mStatus    = null;
    public static String mOnlineScript = null;

    public static String mEntCode = null;
    public static String mEntKey = null;
    
    public static MoMtWorker worker = null;
    
    static {
        Logger.initialize(DEVICE);
        Properties config = new Properties();
        try {
            config.load(new FileInputStream("config.ini"));
        }catch(IOException e){
        }

        mDebugLevel = Integer.parseInt(config.getProperty("mobiz.debug", System.getProperty("mobiz.debug") ));
        mAddress    = config.getProperty("mobiz.address", System.getProperty("mobiz.address") );
        mPassword   = config.getProperty("mobiz.password", System.getProperty("mobiz.password") );
        mEntCode    = config.getProperty("mobiz.entcode", System.getProperty("mobiz.entcode") );
        mEntKey     = config.getProperty("mobiz.entkey", System.getProperty("mobiz.entkey") );
        mQueuePath  = config.getProperty("queue.path", System.getProperty("queue.path") );
        mOnlineScript   = config.getProperty("online.script", System.getProperty("online.script") );

        try{
            onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55100") )).intValue();
        }catch(Exception e){
        }
        
        /* no difference between the mAddress and mAccount, right here, right now */
        if( null == mAddress || null==mEntCode || null==mPassword || null==mQueuePath) {
            Logger.logError("Please given server|password|account|queuepath");
            System.exit(1);
        }
    }
    
    public static void main(String args[]) {
        mRobot = new MobizJiWaiRobot();
        worker = new MoMtWorker(DEVICE, mQueuePath, mRobot);
        worker.startOnlineProcessor( mOnlineScript );
        connect();
        new Thread(mMobizSession).start();
        new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
        mRobot.run();
    }

    public static void connect() {
        try{
            mMobizSession = new JiWaiMobizSession(mEntCode, mEntKey, mPassword);
            mMoListener = new JiWaiMobizListener();
            mMobizSession.addMoListener(mMoListener);
            worker.startProcessor();
        }catch(Exception e ){
            Logger.logError("Mobiz Login failed");
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
            Logger.log("Send Presence Success");
        }catch(Exception e){
            worker.stopProcessor();
            connect();
        }
    }

    public boolean mtProcessing(MoMtMessage message){
        try {
            mMobizSession.sendMessage(message.getAddress(), message.getBody());
        }
        catch (Exception e) {
            e.printStackTrace();
        }
        return true;
    }

    public static void processMo(String buddy, String text, String ext) {
        MoMtMessage msg = new MoMtMessage(DEVICE);
        msg.setAddress(buddy);
        msg.setServerAddress(mAddress + ext);
        msg.setBody(text);
        worker.saveMoMessage(msg);
    }

    static class JiWaiMobizListener {
        public void triggerMo(String buddy, String text, String ext) {
            processMo(buddy, text, ext);
        }
    }

    static class JiWaiMobizSession implements Runnable {

        private ServiceClient mServiceClient = null;

        private ServiceSoap mServiceSoap = null;

        private int mFetchInterval = 5000;

        private String mTimeFormat = "MMddHHmmss";

        private String mEntCode = null;

        private String mEntKey = null;

        private String mPassword = null;

        private String mSubCode = null;

        private ArrayList<JiWaiMobizListener> mMoListeners =
            new ArrayList<JiWaiMobizListener>();

        private void triggerMoListeners(String buddy, String text, String ext) {
            for (JiWaiMobizListener l : mMoListeners) {
                l.triggerMo(buddy, text, ext);
            }
        }

        private String getCurrentTimestamp() {
            SimpleDateFormat sdf = new SimpleDateFormat(mTimeFormat);
            return sdf.format(new Date());
        }

        private String getCheckCode(String timestamp) {
            return MD5Sum.md5Sum(mEntCode + mEntKey + timestamp);
        }

        private void fetchAndTrigger() {
            GetSmsRespInfo inf = new GetSmsRespInfo();
            List<String> mobiles = new ArrayList<String>();
            List<String> contents = new ArrayList<String>();
            List<String> destcodes= new ArrayList<String>();

            try {
                String timestamp = getCurrentTimestamp();
                String checkCode = getCheckCode(timestamp);
                inf = this.mServiceSoap.getSMS(mEntCode, mPassword, timestamp, checkCode);
                mobiles = inf.getMobiles().getString();
                contents = inf.getContents().getString();
                destcodes= inf.getDestCodes().getString();
            } catch (Exception e) {
                e.printStackTrace();
            }

            int smsCount = mobiles.size();
            try {
                for (int i=0; i<smsCount; i++) {
                    triggerMoListeners(mobiles.get(i), contents.get(i), destcodes.get(i));
                }
            } catch (Exception e) {
                e.printStackTrace();
            }
        }

        public JiWaiMobizSession(String entCode, String entKey, String password) {
            this.mEntCode = entCode;
            this.mEntKey = entKey;
            this.mPassword = password;
            this.mSubCode = "";
            this.mServiceClient = new ServiceClient();
            this.mServiceSoap = this.mServiceClient.getServiceSoap();
        }

        public JiWaiMobizSession(String entCode, String entKey, String password, String subCode) {
            this.mEntCode = entCode;
            this.mEntKey = entKey;
            this.mPassword = password;
            this.mSubCode = subCode;
            this.mServiceClient = new ServiceClient();
            this.mServiceSoap = this.mServiceClient.getServiceSoap();
        }

        public void addMoListener(JiWaiMobizListener l) {
            mMoListeners.add(l);
        }

        public void sendMessage(String buddy, String text) {
            this.sendMessage(buddy, text, "");
        }

        public void sendMessage(String buddy, String text, String sendTime) {
            SendSmsRespInfo inf = new SendSmsRespInfo();
            try {
                String timestamp = getCurrentTimestamp();
                String checkCode = getCheckCode(timestamp);
                inf = this.mServiceSoap.sendSMS (
                        mEntCode, mPassword, timestamp, checkCode, mSubCode, buddy, text, sendTime);
                if (mDebugLevel > 0) System.out.println(inf.getResult()+"|"+inf.getSubmitId());
            } catch (Exception e) {
                e.printStackTrace();
            }
        }

        public void run() {
            for (;;) {
                try {
                    Thread.sleep(mFetchInterval);
                    if (mDebugLevel > 0) System.out.println(getCurrentTimestamp());
                    fetchAndTrigger();
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }
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
                        MobizJiWaiRobot.mStatus = sig;
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

import java.io.*;
import java.util.Properties;
import java.util.concurrent.ConcurrentHashMap;
import java.util.Iterator;

import edu.tsinghua.lumaqq.qq.QQ;
import edu.tsinghua.lumaqq.qq.QQClient;
import edu.tsinghua.lumaqq.qq.beans.NormalIM;
import edu.tsinghua.lumaqq.qq.beans.QQUser;
import edu.tsinghua.lumaqq.qq.events.IQQListener;
import edu.tsinghua.lumaqq.qq.events.QQEvent;
import edu.tsinghua.lumaqq.qq.net.PortGateFactory;
import edu.tsinghua.lumaqq.qq.packets.Packet;
import edu.tsinghua.lumaqq.qq.packets.BasicInPacket;
import edu.tsinghua.lumaqq.qq.packets.in.ReceiveIMPacket;
import edu.tsinghua.lumaqq.qq.packets.in.GetOnlineOpReplyPacket;
import edu.tsinghua.lumaqq.qq.packets.in.FriendChangeStatusPacket;
import edu.tsinghua.lumaqq.qq.beans.FriendOnlineEntry;
import edu.tsinghua.lumaqq.qq.beans.ContactInfo;

import de.jiwai.robot.*;
import de.jiwai.util.*;

public class QQJiWaiRobot implements MoMtProcessor {
    // qqno -> qq instance
    private static ConcurrentHashMap<String, QQClient> mInstances
        = new ConcurrentHashMap<String, QQClient>();

    // qqno -> qq user
    private ConcurrentHashMap<String, QQUser> mUsers
        = new ConcurrentHashMap<String, QQUser>();

    // config
    private static String server;

    private boolean udp;

    private static String _mAddress = null;
    private static String[] mAddress= null;

    private static int onlinePort = 55020;

    public static String mOnlineScript = null;

    private String qqpass;

    private static String mQueuePath = null;

    private static MoMtWorker worker = null;

    private static final String DEVICE = "qq";

    private static String mDisplayName = null;
    private String _mDisplayName = "叽歪一下吧！（发送HELP了解更多）";
    private static String mNickname = "/xin 叽歪de";

    // 10-1 -> 27
    private static int mAvatarIndex = 0;

    // buddy -> robot map
    private static ConcurrentHashMap<String, String> buddyRobotMap = new ConcurrentHashMap<String, String>();
    private static String buddyRobotCache = "buddy.cache";

    // For online Status
    private static ConcurrentHashMap<String, String> onlineFriends = new ConcurrentHashMap<String, String>();
    private static final ConcurrentHashMap<String, ConcurrentHashMap<String, String>> onlineFriendsMap
        = new ConcurrentHashMap<String, ConcurrentHashMap<String, String>>();

    // robot -> finished
    private static ConcurrentHashMap<String, Boolean> onlineFinished
        = new ConcurrentHashMap<String, Boolean>();

    // robot -> state
    private static ConcurrentHashMap<String, Integer> onlineState
        = new ConcurrentHashMap<String, Integer>();

    public QQJiWaiRobot() {
        if (false == loadConfig()) {
            return;
        }
        for (String robot : mAddress) {
            login(robot);
        }
        htDeSerialize();
    }

    public static void htSerialize() {
        try {
            FileOutputStream fos = new FileOutputStream(buddyRobotCache);
            ObjectOutputStream oos = new ObjectOutputStream(fos);
            oos.writeObject(buddyRobotMap);
            oos.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public static void htDeSerialize() {
        try {
            FileInputStream fis = new FileInputStream(buddyRobotCache);
            ObjectInputStream ois = new ObjectInputStream(fis);
            buddyRobotMap = (ConcurrentHashMap<String, String>) ois.readObject();
            ois.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void login(String robot) {

        try {
            QQUser user = new QQUser(Integer.valueOf(robot), qqpass);
            user.setStatus(QQ.QQ_LOGIN_MODE_NORMAL);

            mUsers.put(robot, user);

            QQClient client = new QQClient();
            client.addQQListener(new QQJiWaiListener());
            user.setUdp(udp);
            client.setUser(user);
            client.setConnectionPoolFactory(new PortGateFactory());
            client.setLoginServer(server);

            mInstances.put(robot, client);
        } catch (Exception e) {
            handleException(e);
            Logger.logError("Init QQClient error, exit.");
        }

        relogin(robot);

    }

    public static void relogin(String robot) {
        onlineState.put(robot, new Integer(0));
        QQClient client = mInstances.get(robot);
        try{
            client.logout();
            client.login();
            Thread.sleep(6000);
        }catch(Exception e){
            handleException(e);
            client = null;
            Logger.logError("Init QQClient error, exit.");
        }

        //Just for sleeping fun.
        Logger.log("Login::" + robot);
    }

    public static void zizz() {
        try {
            Thread.sleep(500);
        } catch (Exception e) {
        }
    }

    /**
     * read config
     */
    private boolean loadConfig() {

        Properties config = new Properties();

        try {
            config.load(new FileInputStream("config.ini"));
        } catch (Exception e) {
        }

        mOnlineScript = config.getProperty("online.script", System.getProperty("online.script"));
        mDisplayName = config.getProperty("qq.display.name", System.getProperty("qq.display.name", _mDisplayName));
        mAvatarIndex = Integer.parseInt(
                config.getProperty("qq.avatar", System.getProperty("qq.avatar"))
                );

        server = config.getProperty("qq.server", System
                .getProperty("qq.server"));
        if (server == null || server.trim().equals("")) {
            Logger.logError("Can't found qq.server, Program will exit.");
            System.exit(-1);
        }

        if (config.getProperty("qq.udp", System.getProperty("qq.udp", "0"))
                .equals("1")) {
            udp = true;
        } else {
            udp = false;
        }

        try {
            _mAddress = config.getProperty("qq.no", System.getProperty("qq.no"));
            qqpass = config.getProperty("qq.pass", System.getProperty("qq.pass"));
            mAddress = _mAddress.replaceAll("\\s+", "").split(",");
        } catch (Exception ee) {
            Logger.logError("Can't find qq.no, Program will exit.");
            System.exit(-1);
        }

        try {
            onlinePort = Integer.parseInt(config.getProperty("online.port", System
                        .getProperty("online.port", "55020")));
        } catch (Exception ee) {
        }

        mQueuePath = config.getProperty("path.queue");

        return true;
    }

    public static void setDisplayName(String displayName) {
        for (String robot : mAddress) {
            setDisplayName(displayName, robot);
        }
    }

    public static void setDisplayName(String displayName, String robot) {
        QQClient client = mInstances.get(robot);
        if (null != client) {
            ContactInfo info = new ContactInfo();
            info.head = mAvatarIndex;
            info.nick = mNickname;
            client.modifyInfo(null, null, info);
            client.modifySignature(displayName);
            mDisplayName = displayName;
        }
    }

    public static void setAvatar(int avatar) {
        for (String robot : mAddress) {
            setAvatar(avatar);
        }
    }

    public static void setAvatar(int avatar, String robot) {
        QQClient client = mInstances.get(robot);
        if (null != client) {
            ContactInfo info = new ContactInfo();
            info.head = avatar;
            info.nick = mNickname;
            client.modifyInfo(null, null, info);
            mAvatarIndex = avatar;
        }
    }

    static class QQJiWaiListener implements IQQListener {
        public void qqEvent(QQEvent e) {
            String robot = String.valueOf( ((Packet)e.getSource()).getUser().getQQ() );
            switch (e.type) {
                case QQEvent.QQ_LOGIN_SUCCESS:
                    Logger.log("QQ Login Successed::" + robot);
                    setDisplayName( mDisplayName, robot );
                    setAvatar( mAvatarIndex, robot );
                    onlineState.put(robot, new Integer(1));
                    break;
                case QQEvent.QQ_LOGIN_FAIL:
                case QQEvent.QQ_LOGIN_REDIRECT_NULL:
                case QQEvent.QQ_LOGIN_UNKNOWN_ERROR:
                    relogin(robot);
                    zizz();
                    break;
                case QQEvent.QQ_CHANGE_STATUS_SUCCESS:
                    Logger.log("Status Change Successed::" + robot);
                    if (onlineState.get(robot).intValue() == 1) {
                        onlineState.put(robot, new Integer(2));
                    }
                    QQClient client = mInstances.get(robot);
                    client.getFriendOnline();
                    break;
                case QQEvent.QQ_GET_FRIEND_ONLINE_SUCCESS:
                    processFriendOnline(e);
                case QQEvent.QQ_FRIEND_CHANGE_STATUS:
                    processFriendStatusChange(e);
                    break;
                case QQEvent.QQ_RECEIVE_NORMAL_IM:
                    processNormalIM(e);
                    break;
                case QQEvent.QQ_FRIEND_SIGNATURE_CHANGED:
                    processFriendSignatureChange(e);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * processFriendStatusChange
     */
    public static void processFriendStatusChange(QQEvent e){
        FriendChangeStatusPacket p = (FriendChangeStatusPacket) e.getSource();
        String qq = String.valueOf(p.friendQQ);
        String robot = String.valueOf(p.myQQ);
        buddyRobotMap.put(qq, robot);

        switch(p.status){
            case QQ.QQ_STATUS_AWAY:
                onlineFriends.put(qq, "A");
                worker.setOnlineStatus( qq, "A", robot );
                break;
            case QQ.QQ_STATUS_ONLINE:
                onlineFriends.put(qq, "Y");
                worker.setOnlineStatus( qq, "Y", robot );
                break;
            case QQ.QQ_STATUS_OFFLINE:
                worker.setOnlineStatus( qq, "N", robot);
                onlineFriends.remove(qq);
                break;
        }
    }

    /**
     * getOnlineStatus
     */
    public static void processFriendOnline(QQEvent e) {

        GetOnlineOpReplyPacket p = (GetOnlineOpReplyPacket) e.getSource();
        String robot = String.valueOf( p.getUser().getQQ() );
        QQClient client = mInstances.get(robot);
        ConcurrentHashMap<String, String> onlineFriendsTemp = onlineFriendsMap.get(robot);
        if (null == onlineFriendsTemp) {
            onlineFriendsTemp = new ConcurrentHashMap<String, String>();
            onlineFriendsMap.put(robot, onlineFriendsTemp);
        }

        if (!p.finished && onlineFinished.get(robot).booleanValue()) {
            onlineFriendsTemp.clear();
            onlineFinished.put(robot, new Boolean(false));
        }

        for (FriendOnlineEntry friendEntry : p.onlineFriends) {
            String qq = String.valueOf(friendEntry.status.qqNum);
            String status = "Y";
            if (friendEntry.status.isAway())
                status = "A";

            onlineFriendsTemp.put(qq, status);
        }

        if (!p.finished) {
            client.getFriendOnline(p.position);
        } else {
            Logger.log("Get online friends OK");
            onlineFriends.clear();
            onlineFriends.putAll( onlineFriendsTemp );
            syncOnlineFriends(robot);
            onlineFinished.put(robot, new Boolean(true));
        }
    }

    private static void syncOnlineFriends(String robot) {
        QQClient client = mInstances.get(robot);
        ConcurrentHashMap<String, String> onlineFriendsTemp = onlineFriendsMap.get(robot);
        if (null == onlineFriendsTemp) {
            return;
        }
        Iterator<String> it = onlineFriendsTemp.keySet().iterator();
        while (it.hasNext()) {
            buddyRobotMap.put(it.next(), robot);
        }
        htSerialize();
    }

    /**
     * Recode signature of friend
     * 
     * @param e
     */
    private static void processFriendSignatureChange(QQEvent e) {

        ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
        String signature = p.signature.trim();
        String robot = String.valueOf(p.getUser().getQQ());
        String buddy = String.valueOf(p.signatureOwner);

        buddyRobotMap.put(buddy, robot);

        if (signature.equals(""))
            return;

        MoMtMessage msg = new MoMtMessage(DEVICE);

        msg.setAddress(buddy);
        msg.setBody(signature);
        msg.setServerAddress(robot);
        msg.setMsgtype(MoMtMessage.TYPE_SIG);

        worker.saveMoMessage(msg);

    }

    private static void processNormalIM(QQEvent e) {
        try {
            ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
            String robot = String.valueOf(p.getUser().getQQ());
            NormalIM m = p.normalIM;

            if( m.replyType != 1 )  // 1 = normal im, other may be auto reply
                return;

            Integer sender_address = p.normalHeader.sender;
            String buddy = sender_address.toString();
            byte[] messageBytes = stripFace(m.messageBytes);
            if (messageBytes.length == 0)
                return;

            buddyRobotMap.put(buddy, robot);

            String body = new String(messageBytes, "GBK");

            MoMtMessage msg = new MoMtMessage(DEVICE);
            msg.setAddress(buddy);
            msg.setServerAddress(robot);
            msg.setBody(body);

            worker.saveMoMessage(msg);

        } catch (Exception ex) {
            handleException(ex);
        }

    }

    /**
     * Strip QQface from QQ message which will coz $#@T@#Twfr
     */
    private static byte[] stripFace(byte[] m) {
        if (null == m || m.length == 0)
            return null;

        int len = m.length;

        byte[] rew = new byte[m.length];
        int j = 0;
        for (int i = 0; i < len; i++) {
            if (m[i] == 0x14) {
                i++;
                if (i == len) {
                    rew[j++] = 0x14;
                }
                continue;
            }

            if (m[i] == 0x15) {
                i++;
                if (i == len) {
                    rew[j++] = 0x15;
                    break;
                }
                if (m[i] == 0x34) {
                    i++;
                    if (i >= len)
                        break;
                    continue;
                }
                if (m[i] == 0x33) {
                    i++;
                    if (i >= len)
                        break;
                    int extLen = m[i] - '0' + 1; // base form '0' => 1,
                    // '1'=>2 ....

                    i += 32 + 1 + extLen + 1; // md5_len, 1(.) , extlen
                    if (i >= len)
                        break;
                    int shortLen = m[i] - 'A'; // base from 'A' => 0, ....

                    i += shortLen;
                    continue;
                }
                i--;
            }
            rew[j++] = m[i];
        }

        byte[] re = new byte[j];
        System.arraycopy(rew, 0, re, 0, j);

        return re;
    }

    private static void handleException(Exception e) {
        e.printStackTrace();
    }

    public boolean mtProcessing(MoMtMessage message) {
        try {
            String robot = message.getServerAddress();
            String buddy = message.getAddress();
            if (null == robot
                    || robot.length() < 5)
                robot = buddyRobotMap.get(buddy);
            if (null == robot) {
                return false;
            }
            QQClient client = mInstances.get(robot);
            Integer qqAddress = Integer.valueOf(buddy);
            client.sendIM(qqAddress, message.getBody().getBytes("GBK"));
        } catch (Exception e) {
            return false;
        }
        return true;
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
                while( null != ( line = br.readLine())  ) {
                    line = line.trim();

                    //Out by client;
                    if( line.toUpperCase().equals("EXIT") 
                            || line.toUpperCase().equals("QUIT") ){
                        break;
                    }

                    //Restart online_mo.php
                    if( line.equals("ROnlineScript") ){
                        worker.startOnlineProcessor( mOnlineScript );
                        break;
                    }

                    //Relogin QQ
                    if( line.startsWith("Relogin") ) {
                        String robot = line.substring( "Relogin: ".length() );
                        System.out.println( robot );
                        relogin(robot);
                        break;
                    }

                    //Count momt
                    if( line.equals("CountMOMT") ){
                        out( "mo:"+worker.countMo + " mt:" + worker.countMt );
                        break;
                    }

                    //Change Signature 
                    if( line.startsWith("Sig: ") ){
                        String sig = line.substring( "Sig: ".length() );
                        System.out.println( sig );
                        QQJiWaiRobot.setDisplayName( sig );
                        break;
                    }

                    //Change Avatar
                    if( line.startsWith("Avatar: ") ){
                        String sig = line.substring( "Avatar: ".length() );
                        System.out.println( sig );
                        QQJiWaiRobot.setAvatar( Integer.parseInt(sig) );
                        break;
                    }

                    if( onlineFriends.containsKey( line ) )
                        out( onlineFriends.get(line) );
                    else
                        out( "N" );
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

    /**
     * @param args
     */
    public static void main(String[] args) {
        Logger.initialize(DEVICE);
        Logger.log("Enter main");
        QQJiWaiRobot qq_robot = new QQJiWaiRobot();
        new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
        worker = new MoMtWorker(DEVICE, mQueuePath, qq_robot);
        worker.startOnlineProcessor( mOnlineScript );
        worker.run();
    }
}

import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;
import java.util.Calendar;
import java.util.Iterator;
import java.util.Hashtable;
import java.util.HashSet;

import org.jivesoftware.smack.*;
import org.jivesoftware.smack.packet.*;
import org.jivesoftware.smack.filter.*;
import org.jivesoftware.smackx.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;
import java.io.*;

public class GTalkJiWaiRobot implements PacketListener, PacketFilter, MoMtProcessor {

    public static final String TALK_SERVER = "lb-02.jw";
    public static final int TALK_PORT = 5222;
    public static final String DEVICE = "gtalk";
    
    public static XMPPConnection con = null;

    public static Roster roster = null;

    public static ConnectionConfiguration config = null;
    public static GTalkJiWaiRobot gtalk_robot = null;
    
    public static String mServer = null;
    public static String mAccount = null;
    public static String mPassword = null;
    public static String mQueuePath = null;
    public static String _mStatus = "叽歪de树洞";
    public static String mStatus = null;
    public static String mAddress = null;
    public static String mResource = null;
    public static String mNextHoop = null;
    
    public static String _mBlackList = null;
    public static String[] mBlackList = null;
    public static HashSet<String> mBadGuys = new HashSet<String>();

    public static String _mLingoList = null;
    public static String[] mLingoList = null;

    public static Calendar mCalendar = Calendar.getInstance();
    public static Hashtable<String, AnonymousTuple> mRateMap =
        new Hashtable<String, AnonymousTuple>();

    public long mWindowLimit = 1000 * 60 * 30;   // 30 min
    public long mCountLimit = 20;

    public static String replyLingoMsg =
        "本条叽歪含有指令关键字，请重新编辑";
    public static String replyBlackMsg =
        "你的来源地址已经被列入黑名单，如果你觉得这是误判的话，请和管理员联系";
    public static String replyLimitMsg =
        "你发送匿名叽歪过于频繁，请稍后再试";
    public static String replyAgentMsg =
        "本条叽歪已经被投递";

    public static MoMtWorker worker = null;

    static {
        Logger.initialize(DEVICE);
        Properties config = new Properties();
        try {
            config.load(new FileInputStream("config.ini"));
        }catch(IOException e){
        }catch(Exception e){
        }

        mServer = config.getProperty("gtalk.server", System.getProperty("gtalk.server") );
        mAccount = config.getProperty("gtalk.account", System.getProperty("gtalk.account") );
        mPassword = config.getProperty("gtalk.password", System.getProperty("gtalk.password") );
        mResource = config.getProperty("gtalk.resource", System.getProperty("gtalk.resource") );
        mNextHoop = config.getProperty("agent.nexthoop", System.getProperty("agent.nexthoop") );
        mStatus = config.getProperty("gtalk.status", _mStatus );

        _mBlackList = config.getProperty("gtalk.blacklist", _mBlackList);
        mBlackList = _mBlackList.replaceAll("\\s+", "").split(",");

        _mLingoList = config.getProperty("gtalk.lingolist", _mLingoList);
        mLingoList = _mLingoList.replaceAll("\\s+", "").split(",");

        mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path") );

        mAddress = mAccount + "@" + mServer;
        if( null== mServer ||  null==mAccount || null==mPassword || null==mQueuePath) {
            Logger.logError("Please given server|password|account|queuepath");
            System.exit(1);
        }
        mBadGuys.add(mNextHoop);
    }

    private String getDomainFromEmail(String email) {
        int pos = email.lastIndexOf('@');
        if (-1 == pos) return email;

        return email.substring(pos + 1, email.length());
    }

    private String getFortuneFromEmail(String email) {
        char c = email.toLowerCase().charAt(2);
        String fortune = "Cartman";
        switch (c) {
            case 'a':
            case 'b':
            case 'c':
                fortune = "Stan";
                break;
            case 'd':
            case 'e':
            case 'f':
                fortune = "Kyle";
                break;
            case 'g':
            case 'h':
            case 'i':
                fortune = "Kenny";
                break;
            default:
                break;
        }

        return fortune;
    }

    private String getAgentText(String email, String raw) {
        raw = raw.replace("^\\s+", "").replaceAll("\\s+", " ");
        StringBuilder sb = new StringBuilder();

        if (raw.startsWith("@")
            || raw.startsWith("[")
            || raw.startsWith("$")
            || raw.startsWith("d ")
            || raw.startsWith("D ")) {
            sb.append(raw);
            sb.append(" ");
            sb.append("来自");
            sb.append(getFortuneFromEmail(email));
        } else {
            sb.append("来自");
            sb.append(getFortuneFromEmail(email));
            sb.append(" ");
            sb.append(raw);
        }

        return sb.toString();
    }

    private String getFromEmail(String from){
        int pos = from.indexOf('/');
        if( -1 != pos )
            from = from.substring(0, pos);
        pos = from.indexOf('@');
        if( -1 == pos )
            from = from + "@" + mServer;
        return from;
    }
    
    public boolean accept(Packet p){
        return true;
    }
    
    public void processPacket(Packet p) {
        if( p instanceof Message ){
            processMessage((Message) p);
        }else if( p instanceof RosterPacket ){
            processRoster((RosterPacket) p);
        }
    }

    /**
     * Process Roster Package, especially subscribe
     * @param p
     */
    private void processRoster(RosterPacket p){
        Logger.log("Roster::" + p.toXML());
        for (RosterPacket.Item item : p.getRosterItems()) {
            if(RosterPacket.ItemType.from == item.getItemType()) {
                Presence presence = new Presence(Presence.Type.subscribe);
                presence.setFrom(mAddress);
                presence.setTo(item.getUser());
                con.sendPacket(presence);
            }
        }
    }
    
    /**
     * Process Normal MO
     * @param m
     */
    private void processMessage(Message m){
        String email = getFromEmail(m.getFrom());
        if (mBadGuys.contains(email)) return;

        String body = m.getBody();
        if( false == m.getType().toString().equals(Message.Type.chat.toString())
        //      || 0 != m.getTo().indexOf(mAccount+"@"+mServer)
                || body == null
                || body.trim().equals("")
                ){
            return;
        }

        Message msg = new Message(email);
        msg.setType(Message.Type.chat);

        if (processBlackList(email)) {
            Logger.logError("Blacklist::" + email);
            msg.setBody(replyBlackMsg);
            con.sendPacket(msg);
            return;
        } else if (processLingo(body)) {
            Logger.logError("Lingo::" + email);
            msg.setBody(replyLingoMsg);
            con.sendPacket(msg);
            return;
        } else if (processRateLimit(email)) {
            Logger.logError("Ratelimit::" + email);
            msg.setBody(replyLimitMsg);
            con.sendPacket(msg);
            return;
        }
        msg.setBody(replyAgentMsg);
        con.sendPacket(msg);

        Logger.log("Agent::" + email);
        msg = new Message(mNextHoop);
        msg.setBody(getAgentText(email, body));
        msg.setType(Message.Type.chat);
        con.sendPacket(msg);
    }

    /**
     * Process Lingo
     * @param text message body
     * @return true if the message's a lingo match
     */
    private boolean processLingo(String text){
        text = text.toLowerCase().replaceAll("^\\s+", "");
        for (String lingo : mLingoList) {
            if (text.startsWith(lingo))
                return true;
        }
        return false;
    }

    /**
     * Process Blacklist
     * @param email buddy id
     * @return true if buddy is blacklisted
     */
    private boolean processBlackList(String email){
        for (String sub : mBlackList) {
            if (-1 != email.indexOf(sub))
                return true;
        }
        return false;
    }

    /**
     * Process Rate Limit
     * @param email buddy id
     * @return true if rate limit's reached
     */
    private boolean processRateLimit(String email) {
        if (!mRateMap.containsKey(email)) {
            mRateMap.put(email, new AnonymousTuple());
            return false;
        }

        long timeNow = mCalendar.getTimeInMillis();
        AnonymousTuple tuple = mRateMap.get(email);

        if (timeNow - tuple.getTickLast() > mWindowLimit) {
            mRateMap.put(email, new AnonymousTuple());
            return false;
        }

        if (tuple.getCount() >= mCountLimit) {
            return true;
        }

        tuple.incre();
        return false;
    }

    public static void main(String args[]) {
        gtalk_robot = new GTalkJiWaiRobot();
        worker = new MoMtWorker(DEVICE, mQueuePath, gtalk_robot);
        connect();
        gtalk_robot.run();
    }

    public static void connect(){
        try {
            config = new ConnectionConfiguration(TALK_SERVER, TALK_PORT, mServer);
            config.setReconnectionAllowed(false);
            config.setCompressionEnabled(true);
            config.setSecurityMode( ConnectionConfiguration.SecurityMode.enabled );
            config.setSASLAuthenticationEnabled(false);

            realConnect();

        }catch(Exception e ){
            e.printStackTrace();
            Logger.logError("GTalk Login failed");
        }
    }

    public static void realConnect() {
        try{
            con = new XMPPConnection(config);
            con.connect();
            con.login( mAccount , mPassword, mResource );

            gtalk_robot.processOfflineMessage();
            roster = con.getRoster();
            roster.setDefaultSubscriptionMode(Roster.SubscriptionMode.accept_all);

            con.addPacketListener(gtalk_robot , gtalk_robot);

            worker.startProcessor();
        }catch(Exception e ){
            e.printStackTrace();
            Logger.logError("GTalk Login failed");
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

    public void processOfflineMessage(){
        try{
            OfflineMessageManager mm = new OfflineMessageManager( con );
            Iterator<Message> it = mm.getMessages();
            while( it.hasNext() ){
                Message msg = it.next();
                processMessage( msg );
            }
            mm.deleteMessages();
        }catch(XMPPException xmppee){
        }
    }
    
    public void sendPresence(){
        try {
            Presence presence = new Presence(Presence.Type.available);
            presence.setStatus(mStatus);
            presence.setMode(Presence.Mode.chat);
            con.sendPacket(presence);
            Logger.log("Send Presence Success");
        }catch(Exception e){
            worker.stopProcessor();
            connect();
        }
    }

    public boolean mtProcessing(MoMtMessage message){
        /*String to = message.getAddress();
        Message msg = new Message(to);
        msg.setBody(message.getBody());
        msg.setType(Message.Type.chat); //Only have this property ,server will store offline message
        con.sendPacket(msg);*/
        return true;
    }

    /**
     * record class, traffic control oriented
     */
    static class AnonymousTuple {

        private long count;
        private long tickFirst;
        private long tickLast;

        private Calendar calendar;

        public AnonymousTuple() {
            calendar = Calendar.getInstance();
            reset();
        }

        public void incre() {
            ++count;
            tickLast = calendar.getTimeInMillis();
        }

        public void reset() {
            count = 1;
            tickFirst = calendar.getTimeInMillis();
            tickLast = tickFirst;
        }

        public long getTickLast() {
            return tickLast;
        }

        public long getTickFirst() {
            return tickFirst;
        }

        public long getWindow() {
            return tickLast - tickFirst;
        }

        public long getCount() {
            return count;
        }

        public String toString() {
            StringBuilder sb = new StringBuilder("[AnonymousTuple]");
            sb.append("From:" + tickFirst);
            sb.append(" to:" + tickLast);
            sb.append(" count:" + count);
            return sb.toString();
        }
    }
}

import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;
import java.util.Calendar;
import java.util.Iterator;
import java.util.Hashtable;
import javax.naming.directory.Attributes;
import javax.naming.directory.DirContext;
import javax.naming.directory.InitialDirContext;
import java.awt.Image;
import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;

import org.jivesoftware.smack.*;
import org.jivesoftware.smack.packet.*;
import org.jivesoftware.smack.filter.*;
import org.jivesoftware.smackx.*;
import org.jivesoftware.smackx.packet.*;
import org.jivesoftware.smackx.filetransfer.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;
import java.io.*;

public class JabberJiWaiRobot implements FileTransferListener, PacketListener, PacketFilter, MoMtProcessor {

    public static final String TALK_SERVER = "lb-02.jw";
    public static final int TALK_PORT = 5222;
    public static final String DEVICE = "gtalk";
    
    public static int onlinePort = 55130;
    public static XMPPConnection con = null;
    public static FileTransferManager fman = null;

    public static Roster roster = null;

    public static ConnectionConfiguration config = null;
    public static JabberJiWaiRobot jabber_robot = null;
    
    public static String mServer = null;
    public static String mAccount = null;
    public static String mPassword = null;
    public static String mQueuePath = null;
    public static String _mStatus = "叽歪一下吧！（发送HELP了解更多）";
    public static String mStatus = null;
    public static String mAddress = null;
    public static String mResource = null;
    public static String mDomain = null;
    public static String mOnlineScript = null;
    
    public static String[] mBlackLists  = null;
    public static String _mBlackLists   = null;
    
    public static MoMtWorker worker = null;
    
    private static DirContext context;
    // domain->{gtalk,jabber}
    private static Hashtable<String, String> domainCache;
    // avatar->hashcode
    private static Hashtable<String, String> avatarCache;

    static {
        Logger.initialize(DEVICE);
        Properties config = new Properties();
        try {
            config.load(new FileInputStream("config.ini"));
            Hashtable<String,String> env = new Hashtable<String,String>();
            env.put("java.naming.factory.initial", "com.sun.jndi.dns.DnsContextFactory");
            context = new InitialDirContext(env);
        }catch(IOException e){
        }catch(Exception e){
        }

        mServer = config.getProperty("jabber.server", System.getProperty("jabber.server") );
        mAccount = config.getProperty("jabber.account", System.getProperty("jabber.account") );
        mDomain = config.getProperty("jabber.domain", System.getProperty("jabber.domain") );
        mPassword = config.getProperty("jabber.password", System.getProperty("jabber.password") );
        mResource = config.getProperty("jabber.resource", System.getProperty("jabber.resource") );
        mStatus = config.getProperty("jabber.status", _mStatus );
		_mBlackLists  = config.getProperty("jabber.blacklists", System.getProperty("jabber.blacklists"));

        mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path") );
        mOnlineScript = config.getProperty("online.script", System.getProperty("online.script") );

        try{
            onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55090") )).intValue();
        }catch(Exception e){
        }
        
        mAddress = mAccount + "@" + mDomain;
        if( null== mServer ||  null==mAccount || null==mPassword || null==mQueuePath) {
            Logger.logError("Please given server|password|account|queuepath");
            System.exit(1);
        }

		mBlackLists  = _mBlackLists.replaceAll("\\s+", "").split(",");
        domainCache = new Hashtable<String, String>();
        avatarCache = new Hashtable<String, String>();
    }

    /**
     * pre-processor
     * @return true for further process
     */
    private static boolean processBlackList(String buddy) {
        for (String black : mBlackLists) {
            if (buddy.indexOf(black) != -1) {
                return false;
            }
        }
        return true;
    }

    private static String getDomainFromEmail(String email) {
        if (email.endsWith("@")) return null;
        int pos = email.lastIndexOf('@');
        return (-1 == pos)
            ? null
            : email.substring(pos + 1, email.length());
    }

    /**
     * Get the proper device by domain name
     * @param domain domain name
     * @param dirty set true to passby the cache
     * @return device
     */
    public static String getDeviceFromDomain(String domain, boolean dirty) {
        if (!dirty && domainCache.containsKey(domain)) {
            return domainCache.get(domain);
        }

        String googleDomain = "google.com";
        if (context == null) {
            return domain.endsWith(googleDomain) ? "gtalk" : "jabber";
        }

        String host = domain;
        try {
            Attributes dnsLookup =
                    context.getAttributes("_xmpp-server._tcp." + domain, new String[]{"SRV"});
            String srvRecord = (String)dnsLookup.get("SRV").get();
            String [] srvRecordEntries = srvRecord.split(" ");
            host = srvRecordEntries[srvRecordEntries.length-1];
        }
        catch (Exception e) {
            try {
                Attributes dnsLookup =
                        context.getAttributes("_jabber._tcp." + domain, new String[]{"SRV"});
                String srvRecord = (String)dnsLookup.get("SRV").get();
                String [] srvRecordEntries = srvRecord.split(" ");
                host = srvRecordEntries[srvRecordEntries.length-1];
            }
            catch (Exception e2) {
                host = "xmpp-server.l.google.com";
            }
        }

        if (host.endsWith(".")) {
            host = host.substring(0, host.length()-1);
        }
        
        String device = host.endsWith(googleDomain) ? "gtalk" : "jabber";
        domainCache.put(domain, device);
        return device;
    }

    private static String getDeviceFromEmail(String email) {
        return getDeviceFromDomain(getDomainFromEmail(email), false);
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
        if( p instanceof Presence ){
            processOnlineStatus((Presence) p);
            processPresence((Presence) p);
        }else if( p instanceof Message ){
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
        Logger.log("Roster::" + p);
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
        String body = m.getBody();
        if( false == m.getType().toString().equals(Message.Type.chat.toString())
        //      || 0 != m.getTo().indexOf(mAccount+"@"+mServer)
                || body == null
                || body.trim().equals("")
                || body.trim().equals("hello")
                ){
            return;
        }

        String email = getFromEmail(m.getFrom());
        if (!processBlackList(email)) {
            Logger.logError("[BLK]" + email);
            return;
        }
        String device = getDeviceFromEmail(email);
        MoMtMessage msg = new MoMtMessage(device);
        msg.setAddress(email);
        msg.setServerAddress(mAddress);
        msg.setBody(m.getBody());
        worker.saveMoMessage(msg);
    }

    /**
     * Process Presence Package
     * @param p
     */
    private void processOnlineStatus(Presence p){
        String status = p.getType().toString();
        String address = getFromEmail(p.getFrom());
        if (!processBlackList(address)) {
            Logger.logError("[BLK]" + address);
            return;
        }
        String online = "Y";
        if( status.equals("error") || status.equals("unavailable") )
            online = "N";
        else if ( status.equals("away") )
            online = "A";
        worker.setOnlineStatus( address, online, mAddress, 
                getDeviceFromEmail(address) );
    }

    /**
     * Process Presence Package
     * @param p
     */
    private void processPresence(Presence p){
        if( null == p.getStatus() )
            return;
        String status = p.getStatus().trim();
        if( status.equals("") )
            return;
    
        String email = getFromEmail(p.getFrom());
        if (!processBlackList(email)) {
            Logger.logError("[BLK]" + email);
            return;
        }
        String device = getDeviceFromEmail(email);
        MoMtMessage msg = new MoMtMessage(device);
        msg.setAddress(email);
        msg.setServerAddress(mAddress);
        msg.setMsgtype(MoMtMessage.TYPE_SIG);
        msg.setBody(status);
        worker.saveMoMessage(msg);
        // VCard
        /*VCard vCard = new VCard();
        try{
            vCard.load(con, email);
            String vCardHash = vCard.getAvatarHash();
            if (null != vCardHash
                && avatarCache.containsKey(email)
                && avatarCache.get(email).equals(vCardHash)) {
                Logger.log("Duplicated::" + email);
            } else {
                processAvatar(device, mAddress, email, vCard.getAvatar());
                avatarCache.put(email, vCardHash);
            }
        }catch (Exception e){
            //e.printStackTrace();
            Logger.logError("Avatar::" + email);
        }*/
        // End VCard
    }

    /**
     * Process Avatar
     * @param device  gtalk or jabber
     * @param robot   Server Address
     * @param buddy   Email Account
     * @param raw     Raw Bytes of Image
     */
    private static void processAvatar(String device, String robot, String buddy, byte[] raw) {
        if (null == raw || raw.length <= 0) return;
        MoMtMessage msg = new MoMtMessage(device);
        msg.setAddress(buddy);
        msg.setServerAddress(robot);
        msg.setMsgtype(MoMtMessage.TYPE_SIG);
        String avatarInBase64 = Base64.encodeToString(raw, true); // chunk 76
        String avatarMimeType = "image/jpeg";
        msg.addMimePart(avatarMimeType, avatarInBase64);
        worker.saveMoMessage(msg);
        Logger.log("Save Avatar::" + buddy);
    }

    // Interface of FileTransferListener
    public void fileTransferRequest(FileTransferRequest request) {
        if (request.getMimeType().startsWith("image/")
                && request.getFileSize() < 2000000) {
            Logger.log("Name::" + request.getFileName());
            Logger.log("Mime::" + request.getMimeType());
            Logger.log("Size::" + request.getFileSize());
            Logger.log("User::" + request.getRequestor());
        } else {
            Logger.log("Reject::" + request.getFileName());
        }
        request.reject();
        return;
        /*    request.reject();
            return;
        }
        try {
            IncomingFileTransfer ift = request.accept();
            String email = getFromEmail(request.getRequestor());
            String device = getDeviceFromEmail(email);
            //InputStream is = ift.recieveFile();
            StringBuilder sb = new StringBuilder("/tmp/bot/");
            sb.append(device);
            sb.append("/");
            sb.append(device);
            sb.append("__");
            sb.append(email);
            sb.append("__");
            sb.append(request.getFileName());
            ift.recieveFile(new File(sb.toString()));
        } catch (Exception e) {
            e.printStackTrace();
        }*/
    }
    // End FileTransferListener

    public static void main(String args[]) {
        jabber_robot = new JabberJiWaiRobot();
        worker = new MoMtWorker(DEVICE, mQueuePath, jabber_robot);
        worker.startOnlineProcessor( mOnlineScript );
        connect();
        new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();
        jabber_robot.run();
    }

    public static void connect(){
        try {
            config = new ConnectionConfiguration(TALK_SERVER, TALK_PORT, mServer);

            config.setReconnectionAllowed(true);
            config.setCompressionEnabled(true);
            config.setSecurityMode( ConnectionConfiguration.SecurityMode.enabled );
            config.setSASLAuthenticationEnabled(false);

            realConnect();

        }catch(Exception e ){
            e.printStackTrace();
            Logger.logError("Jabber Login failed");
        }
    }

    public static void realConnect() {
        try{
            con = new XMPPConnection(config);
            con.connect();
            con.login( mAccount , mPassword, mResource );

            jabber_robot.processOfflineMessage();
            roster = con.getRoster();

            con.addPacketListener(jabber_robot , jabber_robot);
            fman = new FileTransferManager(con);
            fman.addFileTransferListener(jabber_robot);

            jabber_robot.sendPresence();
            worker.startProcessor();
        }catch(Exception e ){
            e.printStackTrace();
            Logger.logError("Jabber Login failed");
        }
    }

    public void run(){
        while( true ) {
            try{
                sendKeepAlive();
                Thread.sleep( 900000 );
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
    
    private void sendKeepAlive() {
        try {
            Message msg = new Message("monitor.jiwai.de@jiwai.com");
            msg.setBody("Keep Alive");
            msg.setType(Message.Type.chat); //Only have this property ,server will store offline message
            con.sendPacket(msg);
        } catch (Exception e) {
            e.printStackTrace();
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

    public static void setDisplayName(String displayName){
        mStatus = displayName;
    }

    public boolean mtProcessing(MoMtMessage message){
        String to = message.getAddress();
        if (!processBlackList(to)) {
            Logger.logError("[BLK]" + to);
            return false;
        }
        Message msg = new Message(to);
        msg.setBody(message.getBody());
        msg.setType(Message.Type.chat); //Only have this property ,server will store offline message
        con.sendPacket(msg);
        return true;
    }

    static boolean isOffline( String u ) {
        Presence p = roster.getPresence( u );
        if( p == null ){
            return true;
        }

        String status = p.getType().toString();
        if( "error" == status || "unavailable" == status )
            return true;

        return false;
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
                        realConnect();
                        break;
                    }

                    //Change Signature 
                    if( 0 == line.indexOf("Sig: ") ){
                        String sig = line.substring( "Sig: ".length() );
                        JabberJiWaiRobot.mStatus = sig;
                        break;
                    }

                    //Dirty Domain
                    if( 0 == line.indexOf("Dirty: ") ){
                        String domain = line.substring( "Dirty: ".length() );
                        JabberJiWaiRobot.getDeviceFromDomain(domain, true);
                        break;
                    }

                    //Count momt
                    if( line.equals("CountMOMT") ){
                        out( "mo:"+worker.countMo + " mt:" + worker.countMt );
                        break;
                    }

                    out( isOffline(line) ? "N" : "Y" );
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

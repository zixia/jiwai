package fetion;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.*;
import java.util.regex.Pattern;
import java.util.regex.Matcher;

public class HelloClient extends SocketClient {

	private boolean fDebug = false;

    private final int MESSAGE_RECEIVED  = 1;
    private final int BUDDY_REQUEST = MESSAGE_RECEIVED << 1;
    private final int BUDDY_LIST    = MESSAGE_RECEIVED << 2;
	
	private String mGetData;

    private String mAccount;

    public void addSendStr(String str) { }
    public void addRecvStr(String str) { }
    public void addDebugStr(String str) { }
	
	private ArrayList<IMoListener> mMoListeners = new ArrayList<IMoListener>();

	public void setDebug(boolean debug) {
		this.fDebug = debug;
	}

    public void setAccount(String account) {
        this.mAccount = account;
    }
	
	public void addMoListener(IMoListener l) {
		if (this.mMoListeners.contains(l)) return;
		this.mMoListeners.add(l);
	}
	
	public void delListener(IMoListener l) {
		if (this.mMoListeners.contains(l)) {
			this.mMoListeners.remove(l);
		}
		return;
	}
	
	public HelloClient(MainFrame m, String SID) {
		super(m, SID);
        this.mAccount = "-1";
		// TODO Auto-generated constructor stub
	}

    private static Set<String> grepUrisFromAddBuddyEvent(String event) {
        Set<String> uris = new HashSet<String>();
        Pattern sipPattern = Pattern.compile("\\s+uri=\"(sip:.*?)\"");

        Matcher sipMatcher = sipPattern.matcher(event);
        while (sipMatcher.find()) {
            uris.add(sipMatcher.group(1));
        }
        return uris;
    }

    private String addContactFactory(String uriOrMob) {
        String uriCmd = null;
        if (-1 == uriOrMob.indexOf("sip")) {
            uriCmd = (new StringBuilder()).
                append("<args><contacts><buddies><buddy mob=\"").
                append(uriOrMob).
                append("\" buddy-lists=\"1\" desc=\"xx\" expose-mobile-no=\"1\" expose-name=\"1\" /></buddies></contacts></args>").toString();
        } else {
            uriCmd = (new StringBuilder()).
                append("<args><contacts><buddies><buddy uri=\"").
                append(uriOrMob).
                append("\" buddy-lists=\"1\" desc=\"xx\" expose-mobile-no=\"1\" expose-name=\"1\" /></buddies></contacts></args>").toString();
        }
        return uriCmd;
    }
    
    public void addContact(String uriOrMob) {
        String addContactCmd = this.addContactFactory(uriOrMob);
        ++call_id;
        this.sendData = (new StringBuilder()).
        append("S fetion.com.cn SIP-C/2.0\r\nF: ").
        append(sid).append("\r\n").
        append("I: ").
        append(call_id).
        append("\r\n").
        append("Q: 1 S\r\n").
        append("N: AddBuddy\r\n").
        append("L: ").
        append(addContactCmd.length()).
        append("\r\n\r\n").
        append(addContactCmd).
        append("\r\n").toString();
        this.SendData();
        return;
    }
	
	private void triggerMoListeners(String uri, String text, int triggerType) {
		Iterator<IMoListener> it = this.mMoListeners.iterator();
		while (it.hasNext()) {
			IMoListener l = it.next();
            switch (triggerType) {
                case MESSAGE_RECEIVED:
                    l.triggerMo(uri, text, mAccount);
                    break;
                case BUDDY_REQUEST:
                    l.triggerBuddyRequest(uri, mAccount);
                    break;
                case BUDDY_LIST:
                    l.triggerBuddyListReceived(text, mAccount);
                    break;
                default:
                    break;
            }
		}
	}
	
	public void run() {
		for (;;) {
            try {
                Thread.sleep(2000);
                if (Thread.interrupted()) break;
                this.mGetData = this.waitResponse();
                if (null == this.mGetData) continue;
                String append = null;
                if (this.mGetData.indexOf("\r\nL: ") > 0) {
                    append = this.mGetData.substring(this.mGetData.indexOf("\r\nL: ") + 5);
                    if (-1 == append.indexOf("\r\n")) continue;
                    append = append.substring(0, append.indexOf("\r\n"));
                    append = this.waitResponse(Integer.parseInt(append)).trim();
                    this.mGetData = (new StringBuilder()).
                        append(this.mGetData.trim()).
                        append("\r\n\r\n").
                        append(append).
                        toString();
                }
                if (false) {
                    System.out.println("raw: " + this.mGetData + ":" + append);
                }
                int command = 0;
                if (this.mGetData.indexOf("\r\nI: ") > 0) {
                    String comm = this.mGetData.substring(this.mGetData.indexOf("\r\nI: ") + 5);
                    if (-1 == comm.indexOf("\r\n")) continue;
                    comm = comm.substring(0, comm.indexOf("\r\n"));
                    command = Integer.parseInt(comm);
                }
                if (command >= 0) {
                    if (command == 1) {
                        Set<String> contactUris = grepUrisFromAddBuddyEvent(this.mGetData);
                        if (null != contactUris) {
                            for (String contactUri : contactUris) {
                                triggerMoListeners(contactUri, null, BUDDY_REQUEST);
                            }
                            this.replyM(this.mGetData);
                        }
                    }
                    if (command == 5 && this.mGetData.indexOf("<buddy-lists>") != -1) { // contact list
                        triggerMoListeners(null, this.mGetData, BUDDY_LIST);
                        this.replyM(this.mGetData);
                    }
                } else {
                    if (-1 == this.mGetData.indexOf("F: ")) continue;
                    String fromSip = this.mGetData.substring(this.mGetData.indexOf("F: ") + 3);
                    if (-1 == fromSip.indexOf("\r\n")) continue;
                    fromSip = fromSip.substring(0, fromSip.indexOf("\r\n"));
                    triggerMoListeners(fromSip, append, MESSAGE_RECEIVED);
                    this.replyM(this.mGetData);
                }
            } catch (ArrayIndexOutOfBoundsException a) {
            } catch (Exception e) {
            }
		}
	}
}

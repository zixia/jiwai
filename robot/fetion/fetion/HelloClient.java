package fetion;

import java.util.ArrayList;
import java.util.Iterator;

public class HelloClient extends SocketClient {

	private boolean fDebug = false;
	
	private String mGetData;

    public void addSendStr(String str) { }
    public void addRecvStr(String str) { }
    public void addDebugStr(String str) { }
	
	private ArrayList<IMoListener> mMoListeners = new ArrayList<IMoListener>();

	public void setDebug(boolean debug) {
		this.fDebug = debug;
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
		// TODO Auto-generated constructor stub
	}

    // FIXME Too buggy version, nor robust
    private static String grepUriFromAddBuddyEvent(String event) {
        String prefix = "\r\n<events><event type=\"AddBuddyApplication\"><application uri=\"";
        String uri = event.substring(event.indexOf(prefix) + prefix.length());
        try {
            uri = uri.substring(uri.indexOf("sip"), uri.indexOf("\" "));
            if (uri.indexOf("\r\n<events>") == 0) {
                uri = null;
            }
        }
        catch (Exception e) {
            return null;
        }
        return uri;
    }

    private String addContactFactory(String uri) {
        String uriCmd = (new StringBuilder()).
        append("<args><contacts><buddies><buddy uri=\"").
        append(uri).
        append("\" buddy-lists=\"1\" desc=\"xx\" expose-mobile-no=\"1\" expose-name=\"1\" /></buddies></contacts></args>").toString();
        return uriCmd;
    }
    
    public void addContact(String uri) {
        String addContactCmd = this.addContactFactory(uri);
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
        this.waitResponse();
        return;
    }
	
	private void triggerMoListeners(String uri, String text) {
		if (this.fDebug) {
			System.out.println("trigger: " + uri);
		}
		Iterator<IMoListener> it = this.mMoListeners.iterator();
		while (it.hasNext()) {
			IMoListener l = it.next();
			l.triggerMo(uri, text);
		}
	}
	
	public void run() {
		for (;;) {
			if (Thread.interrupted()) break;
			this.mGetData = this.waitResponse();
            if (null == this.mGetData) continue;
			String append = null;
			if (this.mGetData.indexOf("\r\nL: ") > 0) {
				append = this.mGetData.substring(this.mGetData.indexOf("\r\nL: ") + 5);
				append = append.substring(0, append.indexOf("\r\n"));
				append = this.waitResponse(Integer.parseInt(append)).trim();
				this.mGetData = (new StringBuilder()).
				append(this.mGetData.trim()).
				append("\r\n\r\n").
				append(append).
				toString();
			}
			if (this.fDebug) {
				System.out.println("raw: " + this.mGetData + ":" + append);
			}
			int command = 0;
			if (this.mGetData.indexOf("\r\nI: ") > 0) {
				String comm = this.mGetData.substring(this.mGetData.indexOf("\r\nI: ") + 5);
				comm = comm.substring(0, comm.indexOf("\r\n"));
				command = Integer.parseInt(comm);
			}
			if (command >= 0) {
                if (command == 1) {
                    String contactUri = grepUriFromAddBuddyEvent(this.mGetData);
                    if (null != contactUri) this.addContact(contactUri);
                }
			} else {
				if (this.mGetData.indexOf("F: ") <= 0) {
					return;
				}
				String fromSip = this.mGetData.substring(this.mGetData.indexOf("F: ") + 3);
				fromSip = fromSip.substring(0, fromSip.indexOf("\r\n"));
				triggerMoListeners(fromSip, append);
				this.replyM(this.mGetData);
			}
		}
	}
}

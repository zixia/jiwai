package fetion;

public interface IMoListener {
	public void triggerMo(String sip, String text, String robot);
    public void triggerBuddyRequest(String sip, String robot);
    public void triggerBuddyListReceived(String list, String robot);
}

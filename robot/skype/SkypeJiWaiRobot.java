import com.skype.*;
import com.skype.connector.Connector;
import de.jiwai.robot.*;
import java.util.Properties;
import java.util.TimerTask;
import java.util.Timer;

public class SkypeJiWaiRobot extends ChatMessageAdapter implements MoMtProcessor {

	public static String mQueuePath = null;

	public static final String DEVICE = "skype";
	public static MoMtWorker worker = null;
	public static Connector connector = null;
	
	
	static {
		Properties config = new Properties();
		mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path"));

		if (mQueuePath == null) {
			System.err.println("Please give queue(path) definition!");
			System.exit(1);
		}
		connector = Connector.getInstance();
	}

	public static void main(String[] args) throws Exception {

		SkypeJiWaiRobot adapter = new SkypeJiWaiRobot();
		Skype.setDeamon(false); // to prevent exiting from this program
		Skype.addChatMessageListener(adapter);
		Timer timer = new Timer();
		timer.schedule(new MyTimerTask(), 50000, 50000);
		worker = new MoMtWorker(DEVICE, mQueuePath, adapter);
		worker.run();
	}
	
	public void chatMessageReceived(ChatMessage received) throws SkypeException {
		if (received.getType().equals(ChatMessage.Type.SAID)) {
			User sender = received.getSender();
			if( false == sender.isAuthorized() ) {
				sender.setAuthorized(true);
			}
			MoMtMessage msg = new MoMtMessage(DEVICE);
			msg.setAddress(received.getSenderId());
			msg.setBody(received.getContent());
			worker.saveMoMessage(msg);
		}
	}
	
	public boolean mtProcessing(MoMtMessage message){
		try{
			Skype.chat(message.getAddress()).send(message.getBody());
			return true;
		}catch(SkypeException e){
			e.printStackTrace();
			return false;
		}
	}
	
	static private class MyTimerTask extends TimerTask{
		static final String RESETIDLETIMER = "RESETIDLETIMER";
		public void run(){
			try{
				connector.execute(RESETIDLETIMER);
			}catch(Exception e){
			}
		}
	}
}

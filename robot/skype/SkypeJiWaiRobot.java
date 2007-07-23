import com.skype.*;
import com.skype.connector.*;
import java.util.Properties;
import java.util.TimerTask;
import java.util.Timer;

import de.jiwai.robot.*;

public class SkypeJiWaiRobot extends ChatMessageAdapter implements MoMtProcessor {

	public static String mQueuePath = null;

	public static final String DEVICE = "skype";
	public static final String wo = "wo.jiwai.de";
	public static MoMtWorker worker = null;
	public static Connector connector = null;
	
	
	static {
		Logger.initialize(DEVICE);
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
		
		// It's not convien
		// connector.addConnectorListener( new MyConnectorListener() );

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
			msg.setServerAddress(wo);
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
	
	/*
	static private class MyConnectorListener extends AbstractConnectorListener{
		public void messageReceived(ConnectorMessageEvent event){
			String message = event.getMessage();
			System.out.println( "\n" + message );
			String[] messageSplit = message.split(" ", 4);
			if( messageSplit.length == 4 
				&& messageSplit[0].equals("USER")
				&& messageSplit[2].equals("MOOD_TEXT")
				&& false == messageSplit[3].trim().equals("")
			){
				MoMtMessage m = new MoMtMessage(DEVICE);
				m.setMsgtype(MoMtMessage.TYPE_SIG);
				m.setAddress(messageSplit[1]);
				m.setBody(messageSplit[3].trim());
				worker.saveMoMessage(m);
			}
		}

		public void messageSent(ConnectorMessageEvent event){
			String message = event.getMessage();
			System.out.println( "\n" + message );
			String[] messageSplit = message.split(" ", 4);
			if( messageSplit.length == 4 
				&& messageSplit[0].equals("USER")
				&& messageSplit[2].equals("MOOD_TEXT")
				&& false == messageSplit[3].trim().equals("")
			){
				MoMtMessage m = new MoMtMessage(DEVICE);
				m.setMsgtype(MoMtMessage.TYPE_SIG);
				m.setAddress(messageSplit[1]);
				m.setBody(messageSplit[3].trim());
				worker.saveMoMessage(m);
			}
		}
	}
	*/
}

import com.skype.*;
import com.skype.connector.*;
import java.util.Properties;
import java.util.TimerTask;
import java.util.Timer;
import java.io.*;

import de.jiwai.robot.*;
import de.jiwai.util.*;

public class SkypeJiWaiRobot extends ChatMessageAdapter implements MoMtProcessor {

	public static String mQueuePath = null;

	public static final String DEVICE = "skype";
	public static String wo = "wo.jiwai.de";
	public static int onlinePort = 55040;
	public static MoMtWorker worker = null;
	public static Connector connector = null;
	public static String mOnlineScript = null;
	
	
	static {
		Logger.initialize(DEVICE);
		Properties config = new Properties();

		try {
			config.load(new FileInputStream("config.ini"));
		}catch(Exception e){
		}

		try{
			onlinePort = Integer.valueOf( config.getProperty("online.port", System.getProperty("online.port", "55040") )).intValue();
		}catch(Exception e){
		}

		mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path"));
		mOnlineScript = config.getProperty("online.script", System.getProperty("online.script") );

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

		wo = Skype.getProfile().getId();
		
		// It's not convien
		connector.addConnectorListener( new MyConnectorListener() );

		Timer timer = new Timer();
		timer.schedule(new MyTimerTask(), 50000, 50000);

		worker = new MoMtWorker(DEVICE, mQueuePath, adapter);
		worker.startOnlineProcessor( mOnlineScript );

		new Thread( new SocketSession( onlinePort, 5, new Service() ) ).start();

		worker.run();
	}
	
	public void chatMessageReceived(ChatMessage received) throws SkypeException {
		if (received.getType().equals(ChatMessage.Type.SAID)) {
			User sender = received.getSender();
			if( false == sender.isAuthorized() ) {
				sender.setAuthorized(true);
				Skype.getContactList().addFriend( sender.getId(), wo );
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

	static private void setDisplayName(String displayName) {
		try{
			Skype.getProfile().setMoodMessage( displayName );
		}catch(SkypeException se){
		}
	}

	static private void onlineStatusChanged( String skypeId, String status ){

		String[] s = {"Y", wo};

		if( status.equals("UNKNOWN") 
				|| status.equals("OFFLINE") 
				|| status.equals("NA") ) {
			s[0] = "N";
		} else if( status.equals("AWAY")){
			s[0] = "A";
		}

		worker.setOnlineStatus( skypeId, s[0], wo);	
	}
	
	static private class MyConnectorListener extends AbstractConnectorListener{
		public void messageReceived(ConnectorMessageEvent event){
			String message = event.getMessage();
			String[] messageSplit = message.split(" ", 4);
			if( messageSplit.length == 4 
				&& messageSplit[0].equals("USER")
				&& false == messageSplit[3].trim().equals("")
			){
				if( messageSplit[2].equals("MOOD_TEXT") ) {
					MoMtMessage m = new MoMtMessage(DEVICE);
					m.setMsgtype(MoMtMessage.TYPE_SIG);
					m.setServerAddress( wo );
					m.setAddress(messageSplit[1]);
					m.setBody(messageSplit[3].trim());
					worker.saveMoMessage(m);
				}else if( messageSplit[2].equals("ONLINESTATUS") ) {
					onlineStatusChanged( messageSplit[1], messageSplit[3].trim() );
				}
			}
		}
		/*
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
		*/
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
					
					//Restart online_mo.php
					if( line.equals("ROnlineScript") ){
						worker.startOnlineProcessor( mOnlineScript );
						break;
					}

					//Count momt
					if( line.equals("CountMOMT") ){
						out( "mo:"+worker.countMo + " mt:" + worker.countMt );
						break;
					}

					//Change Signature 
					if( 0 == line.indexOf("Sig: ") ){
						String sig = line.substring( "Sig: ".length() );
						SkypeJiWaiRobot.setDisplayName( sig );
						break;
					}

					close();
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

import java.io.FileInputStream;
import java.util.Properties;
import java.util.TimerTask;
import java.util.Timer;

import edu.tsinghua.lumaqq.qq.QQ;
import edu.tsinghua.lumaqq.qq.QQClient;
import edu.tsinghua.lumaqq.qq.beans.NormalIM;
import edu.tsinghua.lumaqq.qq.beans.QQUser;
import edu.tsinghua.lumaqq.qq.events.IQQListener;
import edu.tsinghua.lumaqq.qq.events.QQEvent;
import edu.tsinghua.lumaqq.qq.net.PortGateFactory;
import edu.tsinghua.lumaqq.qq.packets.in.ReceiveIMPacket;

import de.jiwai.robot.Logger;

public class QQMonitorRobot implements IQQListener  {
	private QQClient client;

	private QQUser user;

	// config
	private String server;

	private boolean udp;

	private int qqno;
	
	private String qqpass;

	private int state = 0;
	
	private int monitor;
	private long delay;
	private static boolean hasReplied = false;

	public QQMonitorRobot() {
		if (false == loadConfig()) {
			return;
		}
		try {
			state = 0;
			user = new QQUser(qqno, qqpass);

			user.setStatus(QQ.QQ_LOGIN_MODE_NORMAL);

			client = new QQClient();
			client.addQQListener(this);
			user.setUdp(udp);
			client.setUser(user);
			client.setConnectionPoolFactory(new PortGateFactory());
			client.setLoginServer(server);
			client.login();
		} catch (Exception e) {
			handleException(e);
		}
		
		Logger.log("Initiate OK");
		
		while (state == 0) {
			zizz();
		}
	}

	public void zizz() {
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
			config.load(new FileInputStream("qq.ini"));
		} catch (Exception e) {
		}

		server = config.getProperty("qq.server", System
				.getProperty("qq.server"));
		if (server == null || server.trim().equals("")) {
			System.exit(-1);
		}

		if (config.getProperty("qq.udp", System.getProperty("qq.udp", "0")).equals("1")) {
			udp = true;
		} else {
			udp = false;
		}

		try {
			qqno = Integer.parseInt(config.getProperty("qq.no", System
					.getProperty("qq.no")));
			qqpass = config.getProperty("qq.pass", System
					.getProperty("qq.pass"));
			monitor = Integer.parseInt(config.getProperty("monitor.no", System.getProperty("monitor.no")));
		
			delay = Long.parseLong(config.getProperty("monitor.delay", "6000") );
			
		} catch (Exception ee) {
			Logger.log("Load Configure File Failed");
			System.exit(-1);
		}

		return true;
	}

	public void qqEvent(QQEvent e) {
		switch (e.type) {
		case QQEvent.QQ_LOGIN_SUCCESS:
			state = 1;
			sendToMonitor();
			break;
		case QQEvent.QQ_LOGIN_FAIL:
		case QQEvent.QQ_LOGIN_REDIRECT_NULL:
		case QQEvent.QQ_LOGIN_UNKNOWN_ERROR:
			Logger.log("Login Failed");
			System.exit(-1);
			break;
		case QQEvent.QQ_RECEIVE_NORMAL_IM:
			processNormalIM(e);
			break;
		default:
			break;
		}
	}

	private void sendToMonitor(){
		client.sendIM( monitor, "Help".getBytes() );
	}
	
	private void processNormalIM(QQEvent e) {
		try {
			ReceiveIMPacket p = (ReceiveIMPacket) e.getSource();
			NormalIM m = p.normalIM;

			Integer sender_address = p.normalHeader.sender;
			if (m.messageBytes.length == 0)
				return;
			
			if( sender_address.intValue() == monitor ){
				hasReplied = true;
			}

		} catch (Exception ex) {
			handleException(ex);
		}

	}

	private static void handleException(Exception e) {
		e.printStackTrace();
	}

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		QQMonitorRobot qqMonitor = new QQMonitorRobot();
		MyTimerTask t = new MyTimerTask();
		Timer timer = new Timer();
		timer.schedule(t, qqMonitor.delay);
	}
	
	static private class MyTimerTask extends TimerTask {

		public void run() {
			Logger.log(String.valueOf(hasReplied));
			if( hasReplied == false ){
				System.exit(1);
			}
		}
	}
}

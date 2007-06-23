import java.io.File;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Hashtable;
import java.util.LinkedList;
import java.util.Properties;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import com.skype.ChatMessage;
import com.skype.ChatMessageAdapter;
import com.skype.Skype;
import com.skype.SkypeException;
import com.skype.User;

public class SkypeJiWaiRobot extends ChatMessageAdapter {

	public static String mQueuePath = null;

	public static String mQueuePathMo = null;

	public static String mQueuePathMt = null;

	public static Pattern patternFile = null;

	public static Pattern patternHead = null;

	public static Hashtable<String, String> onlineList = new Hashtable<String, String>();

	static {
		Properties config = new Properties();
		mQueuePath = config.getProperty("queue.path", System.getProperty("queue.path"));

		if (mQueuePath == null) {
			System.err.println("Please give queue(path) definition!");
			System.exit(1);
		}
		mQueuePathMo = mQueuePath
				+ (mQueuePath.endsWith(File.separator) ? "" : File.separator)
				+ "mo" + File.separator;
		mQueuePathMt = mQueuePath
				+ (mQueuePath.endsWith(File.separator) ? "" : File.separator)
				+ "mt" + File.separator;

		/** pre compiled pattern */
		patternFile = Pattern.compile("(.+?)\\n\\n(.+)");
		patternHead = Pattern.compile("ADDRESS:\\s+skype://(.+)",
				Pattern.CASE_INSENSITIVE);

	}

	public static void main(String[] args) throws Exception {

		SkypeJiWaiRobot adapter = new SkypeJiWaiRobot();
		Skype.setDeamon(false); // to prevent exiting from this program
		Skype.addChatMessageListener(adapter);
		
		adapter.run();
	}
	
	public void chatMessageReceived(ChatMessage received) throws SkypeException {
		if (received.getType().equals(ChatMessage.Type.SAID)) {
			String address = received.getSenderId();
			String body = received.getContent();
			writeMoMessage(address, body, System.currentTimeMillis());
		}
	}
	
	public void writeMoMessage(String address, String body, long timestamp){
		SimpleDateFormat sdf = new SimpleDateFormat("MM-dd HH:mm");
		log(sdf.format(new Date(timestamp)) + " " + address + ": " + body);

		long time_millis, sec, msec;
		String file_path_name;
		File msg_file;

		time_millis = System.currentTimeMillis();
		sec = time_millis / 1000;
		msec = time_millis - (time_millis / 1000) * 1000;
		file_path_name = mQueuePathMo + "skype__" + address + "__" + sec
				+ "_" + msec;
		msg_file = new File(file_path_name);

		String file_content;

		file_content = "ADDRESS: skype://" + address + "\n";
		file_content += "\n";
		file_content += body;

		try {
			FileWriter fw = new FileWriter(msg_file);
			fw.write(file_content);
			fw.close();
		} catch (Exception e) {
			log("file writer exception for " + file_path_name);
		}
	}
	
	@SuppressWarnings("unchecked")
	public void run(){
		LinkedList robot_msgs;
		Hashtable<String, String> robot_msg;
		String address;
		String body, file;

		while (true) {
			System.out.print(".");
			robot_msgs = getQueueMt();
			while (!robot_msgs.isEmpty()) {
				System.out.print("*");
				// log ( "fount new mt msg" );
				robot_msg = (Hashtable<String, String>) robot_msgs.removeFirst();
				
				address = robot_msg.get("address");
				body = robot_msg.get("body");
				file = robot_msg.get("file");

				User u = User.getInstance(address);
				try{
					u.send(body);
					(new File(file)).delete();
				}catch(Exception e){
					(new File(file)).renameTo(new File(file + "_sorry"));
				}

				log(new String("MT: ") + address + ": [" + body + "]");
			}

			try {
				Thread.sleep(500);
			} catch (Exception e) {
			}
			// break;
		}
	}

	public LinkedList<Hashtable> getQueueMt() {
		LinkedList<Hashtable> robot_msgs = new LinkedList<Hashtable>();

		Hashtable<String, String> robot_msg = new Hashtable<String, String>();

		try {
			File files[] = new File(mQueuePathMt).listFiles();

			String file_name;
			String file_content;
			char[] buf = new char[1024];

			Matcher matcher;

			String head, body, address;

			for (int i = 0; i < files.length; i++) {
				if (!files[i].isFile())
					continue;

				file_name = files[i].getName();

				if (0 != file_name.indexOf("skype__")) {
					files[i].delete();
					log("jiwaiQueueMt found unknown file: " + file_name
							+ ", skipped & deleted");
					continue;
				}

				// log("There is a file " + files[i].getName() + " in this
				// diretory");

				int n = (new FileReader(files[i])).read(buf, 0, 1024);
				file_content = new String(buf, 0, n);

				// log(file_content);

				matcher = patternFile.matcher(file_content);

				if (!matcher.find()) {
					log("jiwaiQueueMt fount un-parse data: " + file_content
							+ ", skiped & deleted");
					files[i].delete();
					continue;
				}

				head = matcher.group(1);
				body = matcher.group(2);

				matcher = patternHead.matcher(head);

				if (!matcher.find()) {
					log("jiwaiQueueMt fount un-parse head data: " + head
							+ ", skiped & deleted");
					files[i].delete();
					continue;
				}

				address = matcher.group(1);

				robot_msg.put("address", address);
				robot_msg.put("body", body.trim());
				robot_msg.put("file", files[i].getCanonicalPath());

				robot_msgs.add((Hashtable) robot_msg.clone());
			}

		} catch (Exception e) {
			e.printStackTrace();
			log("jiwaiQueueMt readdir failed");
			System.exit(1);
		}

		return robot_msgs;
	}

	public void log(String e) {
		System.out.println(e);
	}
}

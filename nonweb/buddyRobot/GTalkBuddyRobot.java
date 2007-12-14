import java.util.Collection;

import org.jivesoftware.smack.*;
import org.jivesoftware.smack.packet.*;
import org.jivesoftware.smack.filter.*;
import org.jivesoftware.smackx.*;

import java.io.*;

public class GTalkBuddyRobot {

	public final String TALK_SERVER = "talk.google.com";
	public final int TALK_PORT = 5222;
	
	public XMPPConnection con = null;

	public Roster roster = null;

	public ConnectionConfiguration config = null;
	
	public String mPassword = null;
	public String mUsername = null;

	public String mServer = null;
	public String mAccount = null;

	public GTalkBuddyRobot() {
		configure();
		connect();
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

	public void configure() {
		mUsername = System.getProperty( "username", null ) ;
		mPassword = System.getProperty( "password", null );

		if ( null != mUsername ) {
			int index = mUsername.indexOf("@");
			if ( index > -1 ) {
				mAccount = mUsername.substring(0, index);
				mServer = mUsername.substring(index+1);
			}
		}

		if( null== mServer ||  null==mUsername || null==mPassword ) {
			System.exit(1);
		}
	}

	public void connect() {
		try {
			//config
			config = new ConnectionConfiguration(TALK_SERVER, TALK_PORT, mServer);
			config.setReconnectionAllowed(false);
			config.setSecurityMode( ConnectionConfiguration.SecurityMode.enabled );
			config.setSASLAuthenticationEnabled(false);
			
			//login
			con = new XMPPConnection(config);
			con.connect();
			con.login( mAccount , mPassword );

		}catch(Exception e ){
			e.printStackTrace();
			System.exit(1);
		}
	}

	public void run() {
		roster = con.getRoster();
		Collection<RosterEntry> list = roster.getEntries();
		for ( RosterEntry entry : list ) {
			String name = entry.getName();
			System.out.println( entry.getUser() + "," + ( (name==null) ? "" : name ) );
		}
	}

	public static void main(String args[]) {
		new GTalkBuddyRobot().run();
	}
}

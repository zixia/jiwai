// import fetion.*;

package fetion;

import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.*;
import java.util.ArrayList;
import java.util.Iterator;


public class HelloFetion implements Runnable {

	private URLConnection mConnection;
	
	private InputStreamReader mIn;
	
	private HttpURLConnection mHttpConnection;
	
	private OutputStreamWriter mOut;
	
	private HelloClient mClient;
	
	private String mAccount;
	
	private String mPassword;
	
	private String mNickname = "叽歪一下了解更多，发送HELP获得帮助信息";
	
	private static String mLoginServer = "http://nav.fetion.com.cn/nav/getsystemconfig.aspx";
	
	private static String mSipServer = "http://nav.m161.com.cn/Getconfig.aspx";
	
	private String mGetData;
	
	private String mSendData;
	
	private MainFrame mMainFrame = null; //new MainFrame();
	
	private ArrayList mContactList = new ArrayList();
		
	// Fetion Info
    private boolean fForceSms = false;

	private String mSipcProxy;
	
	private String mSsiAppSignIn;
	
	private String mSsiAppSignOut;
	
	private String mSid;
	
	private String mUri;
	
	public void sendMessage(String buddy, String text) {
		if (null == this.mClient) return;
		this.mClient.sendMsg(buddy, text, this.fForceSms);
	}

    public void heartBeat() throws Exception {
        this.mClient.sendKeepAlive();
    }

    public static String getUriFromMobile(String mobile) {
        String mUri = null;
        try {
            URL _url = new URL(mLoginServer);
            URLConnection mConnection = _url.openConnection();
            HttpURLConnection mHttpConnection = (HttpURLConnection)mConnection;
            mConnection.setDoOutput(true);
            mConnection.setRequestProperty("Content-Type", "text-plain");
            mConnection.setRequestProperty("charset", "utf-8");
            OutputStreamWriter mOut = new OutputStreamWriter(mConnection.getOutputStream(), "utf-8");
            mOut.write("<config><user mobile-no=\"" + mobile + "\" /><client type=\"PC\" version=\"2.1.0.0\" platform=\"W5.1\" /><servers version=\"0\" /><service-no version=\"0\" /><parameters version=\"0\" /><hints version=\"0\" /><http-applications version=\"0\" /></config>");
            mOut.flush();
            mOut.close();
            InputStreamReader mIn = new InputStreamReader(mConnection.getInputStream(), "utf-8");

            // StreamRead Part
            char data[] = new char[10240];
            String res;
            int i = 0, j = 0;
            do {
                j = mIn.read();
                if (-1 == j) {
                    data[i] = '\0';
                    break;
                }
                data[i++] = (char)j;
            } while (true);
            mIn.close();

            res = new String(data);
            String mSipcProxy = res.substring(res.indexOf("<sipc-proxy>") + 12);
            mSipcProxy = mSipcProxy.substring(0, mSipcProxy.indexOf("</sipc-proxy>")).trim();
            String mSsiAppSignIn = res.substring(res.indexOf("<ssi-app-sign-in>") + 17);
            mSsiAppSignIn = mSsiAppSignIn.substring(0, mSsiAppSignIn.indexOf("</ssi-app-sign-in>"));
            String mSsiAppSignOut = res.substring(res.indexOf("<ssi-app-sign-out>") + 18);
            mSsiAppSignOut = mSsiAppSignOut.substring(0, mSsiAppSignOut.indexOf("</ssi-app-sign-out>"));

            // Get the SIP
            _url = new URL(mSipServer);
            mConnection = _url.openConnection();
            mHttpConnection = (HttpURLConnection)mConnection;
            mConnection.setDoOutput(true);
            mConnection.setRequestProperty("charset", "utf-8");
            mOut = new OutputStreamWriter(mConnection.getOutputStream(), "utf-8");
            mOut.write((new StringBuilder()).append("CellPhone=").append(mobile).toString());
            mOut.flush();
            mOut.close();

            try {
                mIn = new InputStreamReader(mConnection.getInputStream(), "utf-8");
            }
            catch (Exception e) {
                e.printStackTrace();
            }
            do {
                j = mIn.read();
                if (-1 == j) {
                    data[i] = '\0';
                    break;
                }
                data[i++] = (char)j;
            } while (true);
            mIn.close();
            res = new String(data);
            String mSid = res.substring(res.indexOf("<Sid>") + 5);
            mSid = mSid.substring(0, mSid.indexOf("</Sid>")).trim();

            // Get the URI
            res = res.substring(res.indexOf("<User>"));
            mUri = res.substring(res.indexOf("<Uri>") + 5);
            mUri = mUri.substring(0, mUri.indexOf("</Uri>")).trim();
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }

        return mUri;
    }

    public void Logout()
    {
        if (null != this.mClient)
        {
            this.mClient.sendLogout();
        }
    }
	
	private void realLogin()
	{
		if (null != this.mClient) 
		{
			this.mClient.interrupt();
			this.mClient.close();
			this.mClient = null;
		}
		this.mClient = new HelloClient(mMainFrame, mSid);
		if (0 != this.mClient.login(this.mSipcProxy, this.mPassword))
		{
			System.out.println("Login failed");
			this.mClient.close();
		}
		else {
			this.mClient.start();
		}
	}
	
	// DoNE!
	public void Login() {
		try {
			URL _url = new URL(this.mLoginServer);
			this.mConnection = _url.openConnection();
			this.mHttpConnection = (HttpURLConnection)this.mConnection;
			this.mConnection.setDoOutput(true);
			this.mConnection.setRequestProperty("Content-Type", "text-plain");
			this.mConnection.setRequestProperty("charset", "utf-8");
			this.mOut = new OutputStreamWriter(this.mConnection.getOutputStream(), "utf-8");
			this.mOut.write("<config><user mobile-no=\"13466366523\" /><client type=\"PC\" version=\"2.1.0.0\" platform=\"W5.1\" /><servers version=\"0\" /><service-no version=\"0\" /><parameters version=\"0\" /><hints version=\"0\" /><http-applications version=\"0\" /></config>");
			this.mOut.flush();
			this.mOut.close();
			this.mIn = new InputStreamReader(this.mConnection.getInputStream(), "utf-8");
			
			// StreamRead Part
			char data[] = new char[10240];
			String res;
			int i = 0, j = 0;
			do {
				j = this.mIn.read();
				if (-1 == j) {
					data[i] = '\0';
					break;
				}
				data[i++] = (char)j;
			} while (true);
			this.mIn.close();
			
			res = new String(data);
			this.mSipcProxy = res.substring(res.indexOf("<sipc-proxy>") + 12);
			this.mSipcProxy = this.mSipcProxy.substring(0, this.mSipcProxy.indexOf("</sipc-proxy>")).trim();
			this.mSsiAppSignIn = res.substring(res.indexOf("<ssi-app-sign-in>") + 17);
			this.mSsiAppSignIn = this.mSsiAppSignIn.substring(0, this.mSsiAppSignIn.indexOf("</ssi-app-sign-in>"));
			this.mSsiAppSignOut = res.substring(res.indexOf("<ssi-app-sign-out>") + 18);
			this.mSsiAppSignOut = this.mSsiAppSignOut.substring(0, this.mSsiAppSignOut.indexOf("</ssi-app-sign-out>"));
			
			// Get the SIP
			_url = new URL(this.mSipServer);
			this.mConnection = _url.openConnection();
			this.mHttpConnection = (HttpURLConnection)this.mConnection;
			this.mConnection.setDoOutput(true);
			this.mConnection.setRequestProperty("charset", "utf-8");
			this.mOut = new OutputStreamWriter(this.mConnection.getOutputStream(), "utf-8");
			this.mOut.write((new StringBuilder()).append("CellPhone=").append(this.mAccount).toString());
			this.mOut.flush();
			this.mOut.close();
			
			try {
				this.mIn = new InputStreamReader(this.mConnection.getInputStream(), "utf-8");
			}
			catch (Exception e) {
				e.printStackTrace();
			}
			do {
				j = this.mIn.read();
				if (-1 == j) {
					data[i] = '\0';
					break;
				}
				data[i++] = (char)j;
			} while (true);
			this.mIn.close();
			res = new String(data);
			this.mSid = res.substring(res.indexOf("<Sid>") + 5);
			this.mSid = this.mSid.substring(0, this.mSid.indexOf("</Sid>")).trim();
			
			// Get the URI
			res = res.substring(res.indexOf("<User>"));
			this.mUri = res.substring(res.indexOf("<Uri>") + 5);
			this.mUri = this.mUri.substring(0, this.mUri.indexOf("</Uri>")).trim();
	
			// Real Login
			this.realLogin();
			this.setNickname(this.mNickname);
		} catch (MalformedURLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();	
		}
	}
	
	// DoNE!
	public void setNickname(String nick) {
		this.mNickname = nick;
		String text = (new StringBuilder()).
		append("<args><personal nickname=\"").
		append(nick).
		append("\" /></args>").toString();
		this.mClient.setNickname(text);
	}
	
	public HelloFetion(String sn, String pass) {
		this.mAccount = sn;
		this.mPassword = pass;
	}
	
	public String setAccount(String sn) {
		this.mAccount = sn;
		return this.mAccount;
	}
	
	public String setPassword(String pass) {
		this.mPassword = pass;
		return this.mPassword;
	}
	
	public String setServer(String server) {
		this.mLoginServer = server;
		return this.mLoginServer;
	}

    public void addMoListener(IMoListener l) {
        this.mClient.addMoListener(l);
    }
	
	/**
	 * 
	 * @param args
	 */
	public static void main(String[] args) {
		String testAccount = "13811796417";
		String testPassword= "daodao19";
		Runnable f = new HelloFetion(testAccount, testPassword);
		try {
			f.run();
		} catch (RuntimeException e) {
			e.printStackTrace();
		}
	}

	public void run() {
		this.Login();
		try {
			this.mClient.run();
		} catch (RuntimeException e) {
			e.printStackTrace();
		}
	}

}

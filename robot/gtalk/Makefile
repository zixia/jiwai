run: GTalkJiWaiRobot.class
	/usr/java/jdk1.5.0_11/bin/java -cp .:../../javaLib/jiwaiMessage.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar -Dmsn.email=wo@jiwai.de -Dmsn.pass=Beta@JiWai741de -Dqueue.path=/var/cache/tmpfs/jiwai/queue/msn GTalkJiWaiRobot

GTalkJiWaiRobot.class: GTalkJiWaiRobot.java
	/usr/java/jdk1.5.0_11/bin/javac -Xlint:unchecked -cp .:../../javaLib/jiwaiMessage.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar GTalkJiWaiRobot.java

clean:
	rm *.class


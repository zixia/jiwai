run: MsnJiWaiRobot.class
	/usr/java/jdk1.5.0_11/bin/java -cp .:jarlib/cindy.jar:jarlib/commons-logging.jar:jarlib/jml-1.0b1.jar:../../javaLib/jiwaiMessage.jar -Dmsn.email=wo@jiwai.de -Dmsn.pass=Beta@JiWai741de -Dqueue.path=/var/cache/tmpfs/jiwai/queue/msn -Djiwai.robot.log.dir=/var/cache/tmpfs/jiwai/log MsnJiWaiRobot

MsnJiWaiRobot.class: MsnJiWaiRobot.java
	/usr/java/jdk1.5.0_11/bin/javac -Xlint:unchecked -cp .:jarlib/cindy.jar:jarlib/commons-logging.jar:jarlib/jml-1.0b1.jar:../../javaLib/jiwaiMessage.jar MsnJiWaiRobot.java

clean:
	rm *.class


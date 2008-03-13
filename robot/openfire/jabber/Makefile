run: JabberJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../../javaLib/jiwaiMessage-1.2.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar -Djiwai.robot.log.dir=/var/log/robot JabberJiWaiRobot

JabberJiWaiRobot.class: JabberJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../../javaLib/jiwaiMessage-1.2.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar JabberJiWaiRobot.java

clean:
	rm *.class

compile:    JabberJiWaiRobot.class


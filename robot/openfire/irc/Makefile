run: IrcJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../../javaLib/jiwaiMessage-1.1.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar -Djiwai.robot.log.dir=/var/log/robot IrcJiWaiRobot

IrcJiWaiRobot.class: IrcJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../../javaLib/jiwaiMessage-1.1.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar IrcJiWaiRobot.java

clean:
	rm *.class


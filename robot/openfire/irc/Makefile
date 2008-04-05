run: IrcJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar -Djiwai.robot.log.dir=/var/log/robot IrcJiWaiRobot

IrcJiWaiRobot.class: IrcJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar IrcJiWaiRobot.java

clean:
	rm *.class


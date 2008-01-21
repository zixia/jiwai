run: AimJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../../javaLib/jiwaiMessage-1.1.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar -Djiwai.robot.log.dir=/var/log/robot AimJiWaiRobot

AimJiWaiRobot.class: AimJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../../javaLib/jiwaiMessage-1.1.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar AimJiWaiRobot.java

clean:
	rm *.class


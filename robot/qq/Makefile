run: QQJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/LumaQQ.jar:../../javaLib/log4j.jar:../../javaLib/jiwaiMessage.jar -Djiwai.robot.log.dir=/var/log/robot QQJiWaiRobot

QQJiWaiRobot.class: QQJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/LumaQQ.jar:../../javaLib/jiwaiMessage.jar QQJiWaiRobot.java

clean:
	rm *.class


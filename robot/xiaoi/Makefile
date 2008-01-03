all :   compile

compile :   XiaoiJiWaiRobot.class

run: XiaoiJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/commons-codec-3.1.jar:../../javaLib/commons-collections-3.1.jar:../../javaLib/commons-lang-2.0.jar:../../javaLib/commons-logging.jar:../../javaLib/log4j.jar:../../javaLib/mina-core-0.9.4.jar:../../javaLib/slf4j-log4j12.jar:../../javaLib/iBotPlatform-sdk-2.2.5.jar:../../javaLib/jiwaiMessage.jar -Djiwai.robot.log.dir=/var/log/robot XiaoiJiWaiRobot

XiaoiJiWaiRobot.class: XiaoiJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/commons-logging.jar:../../javaLib/iBotPlatform-sdk-2.2.5.jar:../../javaLib/jiwaiMessage.jar XiaoiJiWaiRobot.java

clean:
	rm *.class


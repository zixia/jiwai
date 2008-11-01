run: YahooJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/commons-logging.jar:../../javaLib/ymsg_network_v0_63.jar:../../javaLib/jiwaiMessage-1.1.jar -Djiwai.robot.log.dir=/var/log/robot YahooJiWaiRobot

YahooJiWaiRobot.class: YahooJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/commons-logging.jar:../../javaLib/ymsg_network_v0_63.jar:../../javaLib/jiwaiMessage-1.1.jar YahooJiWaiRobot.java

clean:
	rm *.class


run: YahooJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar -Djiwai.robot.log.dir=/var/log/robot YahooJiWaiRobot

YahooJiWaiRobot.class: YahooJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar YahooJiWaiRobot.java

clean:
	rm *.class


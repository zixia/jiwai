run: AimJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/commons-logging.jar:../../javaLib/joscar-0.9.3-bin.jar:../../javaLib/joscar-0.9.3-demo.jar:../../javaLib/jiwaiMessage-1.1.jar -Djiwai.robot.log.dir=/var/log/robot AimJiWaiRobot

AimJiWaiRobot.class: AimJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/commons-logging.jar:../../javaLib/joscar-0.9.3-bin.jar:../../javaLib/joscar-0.9.3-demo.jar:../../javaLib/jiwaiMessage-1.1.jar AimJiWaiRobot.java

clean:
	rm *.class


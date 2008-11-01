run: MsnJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/cindy.jar:../../javaLib/commons-logging.jar:../../javaLib/jml-1.0b1.jar:../../javaLib/jiwai-library-1.2.jar -Djiwai.robot.log.dir=/var/log/robot MsnJiWaiRobot

MsnJiWaiRobot.class: MsnJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/cindy.jar:../../javaLib/commons-logging.jar:../../javaLib/jml-1.0b1.jar:../../javaLib/jiwai-library-1.2.jar MsnJiWaiRobot.java

clean:
	rm *.class

compile: MsnJiWaiRobot.class

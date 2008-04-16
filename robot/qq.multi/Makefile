all : compile

compile : QQJiWaiRobot.class

run: QQJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/lumaqq-protocols-1.1.jar:../../javaLib/LumaQQ.jar:../../javaLib/log4j.jar:../../javaLib/jiwai-library-1.2.jar -Djiwai.robot.log.dir=/var/log/robot QQJiWaiRobot

QQJiWaiRobot.class: QQJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/lumaqq-protocols-1.1.jar:../../javaLib/LumaQQ.jar:../../javaLib/jiwai-library-1.2.jar QQJiWaiRobot.java

clean:
	rm *.class


JAVA=/usr/java/jdk/bin/java
JAVAC=/usr/java/jdk/bin/javac

#JAVA=java
#JAVAC=javac

run: QQJiWaiRobot.class
	${JAVA} -cp .:../../javaLib/LumaQQ.jar:../../javaLib/log4j.jar:../../javaLib/jiwai-library-1.2.jar -Djiwai.robot.log.dir=/var/log/robot QQJiWaiRobot

QQJiWaiRobot.class: QQJiWaiRobot.java
	${JAVAC} -Xlint:unchecked -cp .:../../javaLib/LumaQQ.jar:../../javaLib/jiwai-library-1.2.jar QQJiWaiRobot.java

clean:
	rm *.class


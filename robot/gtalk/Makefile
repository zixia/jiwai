run: GTalkJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/jiwai-library-1.2.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar -Djiwai.robot.log.dir=/var/log/robot GTalkJiWaiRobot

GTalkJiWaiRobot.class: GTalkJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/jiwai-library-1.2.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar GTalkJiWaiRobot.java

clean:
	rm *.class


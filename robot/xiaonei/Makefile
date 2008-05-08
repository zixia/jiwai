all : XiaoneiJiWaiRobot.class

run: XiaoneiJiWaiRobot.class
	#/usr/java/jdk/bin/java -cp .:../../javaLib/jiwai-library-1.2.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar -Djiwai.robot.log.dir=/var/log/robot XiaoneiJiWaiRobot
	/usr/java/jdk/bin/java -cp .:../../javaLib/jiwai-library-1.2.jar:./smack/target/smack.jar:./smack/target/smackx.jar:./smack/target/smackx-debug.jar -Djiwai.robot.log.dir=/var/log/robot XiaoneiJiWaiRobot

XiaoneiJiWaiRobot.class: XiaoneiJiWaiRobot.java
	#/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/jiwai-library-1.2.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar XiaoneiJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/jiwai-library-1.2.jar:./smack/target/smack.jar:./smack/target/smackx.jar:./smack/target/smackx-debug.jar XiaoneiJiWaiRobot.java

clean:
	rm *.class


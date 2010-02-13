all : FacebookJiWaiRobot.class

run: FacebookJiWaiRobot.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/jiwai-library-1.2.jar:./smack/target/smack.jar:./smack/target/smackx.jar:./smack/target/smackx-debug.jar -Djiwai.robot.log.dir=/var/log/robot FacebookJiWaiRobot

FacebookJiWaiRobot.class: FacebookJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/jiwai-library-1.2.jar:./smack/target/smack.jar:./smack/target/smackx.jar:./smack/target/smackx-debug.jar FacebookJiWaiRobot.java

clean:
	rm *.class


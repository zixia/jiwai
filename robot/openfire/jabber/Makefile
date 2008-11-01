all:    compile

run: compile
	/usr/java/jdk/bin/java -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack-1.2.jar:../../../javaLib/smackx-1.2.jar -Djiwai.robot.log.dir=/var/log/robot JabberJiWaiRobot

compile: JabberJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack-1.2.jar:../../../javaLib/smackx-1.2.jar JabberJiWaiRobot.java

clean:
	rm *.class


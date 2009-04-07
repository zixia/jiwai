all:    compile

run: compile
	/usr/java/jdk/bin/java -Xmn50M -Xms128M -Xmx128M -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack-1.3.jar:../../../javaLib/smackx-1.3.jar -Djiwai.robot.log.dir=/var/log/robot JabberJiWaiRobot

compile: JabberJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack-1.3.jar:../../../javaLib/smackx-1.3.jar JabberJiWaiRobot.java

clean:
	rm *.class


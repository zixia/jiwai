all:    compile tags

run: compile
	/usr/java/jdk/bin/java -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar:../../../javaLib/smackx-jingle.jar -Djiwai.robot.log.dir=/var/log/robot JabberJiWaiRobot

tags: JabberJiWaiRobot.java Base64.java
	ctags *.java

compile: JabberJiWaiRobot.java Base64.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../../javaLib/jiwai-library-1.2.jar:../../../javaLib/smack.jar:../../../javaLib/smackx.jar:../../../javaLib/smackx-jingle.jar JabberJiWaiRobot.java Base64.java

clean:
	rm *.class tags


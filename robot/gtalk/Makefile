run: GTalkJiWaiRobot.class
	/usr/java/jdk1.5.0_11/bin/java -cp .:../../javaLib/jiwaiMessage.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar -Dgtalk.account=wo -Dgtalk.server=jiwai.de -Dgtalk.password=Beta@JiWai741de -Dqueue.path=/var/cache/tmpfs/jiwai/queue/gtalk -Djiwai.robot.log.dir=/var/cache/tmpfs/jiwai/log GTalkJiWaiRobot

GTalkJiWaiRobot.class: GTalkJiWaiRobot.java
	/usr/java/jdk1.5.0_11/bin/javac -Xlint:unchecked -cp .:../../javaLib/jiwaiMessage.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar GTalkJiWaiRobot.java

clean:
	rm *.class


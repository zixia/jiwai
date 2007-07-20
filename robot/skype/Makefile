run: SkypeJiWaiRobot.class
	cp JNI/lib/libJSA.so /tmp
	/usr/java/jdk/bin/java -cp .:../../javaLib/jiwaiSkype.jar:../../javaLib/jiwaiMessage.jar -Dqueue.path=/var/cache/tmpfs/jiwai/queue/skype -Djiwai.robot.log.dir=/var/log/robot SkypeJiWaiRobot

SkypeJiWaiRobot.class: SkypeJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/jiwaiSkype.jar:../../javaLib/jiwaiMessage.jar SkypeJiWaiRobot.java

clean:
	rm *.class


run: SkypeJiWaiRobot.class
	/usr/java/jdk1.5.0_11/bin/java -cp .:../../javaLib/jiwaiSkype.jar:../../javaLib/jiwaiMessage.jar -Dqueue.path=/var/cache/tmpfs/jiwai/queue/skype SkypeJiWaiRobot

SkypeJiWaiRobot.class: SkypeJiWaiRobot.java
	/usr/java/jdk1.5.0_11/bin/javac -Xlint:unchecked -cp .:../../javaLib/jiwaiSkype.jar:../../javaLib/jiwaiMessage.jar SkypeJiWaiRobot.java

clean:
	rm *.class


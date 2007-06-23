run: SkypeJiWaiRobot.class
	/usr/java/jdk1.5.0_11/bin/java -cp .:jarlib/jiwaiSkype.jar -Dqueue.path=/var/cache/tmpfs/jiwai/queue/skype SkypeJiWaiRobot

SkypeJiWaiRobot.class: SkypeJiWaiRobot.java
	/usr/java/jdk1.5.0_11/bin/javac -Xlint:unchecked -cp .:jarlib/jiwaiSkype.jar SkypeJiWaiRobot.java

clean:
	rm *.class


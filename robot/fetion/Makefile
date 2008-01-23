all : compile

compile :   HelloFetion.class FetionJiWaiRobot.class

HelloFetion.class: fetion/HelloFetion.java
	/usr/java/jdk/bin/javac -cp .:../../javaLib/Fetion.jar fetion/HelloFetion.java fetion/HelloClient.java fetion/IMoListener.java

run: FetionJiWaiRobot.class HelloFetion.class
	/usr/java/jdk/bin/java -cp .:../../javaLib/commons-logging.jar:../../javaLib/Fetion.jar:../../javaLib/jiwaiMessage-1.1.jar -Djiwai.robot.log.dir=/var/log/robot FetionJiWaiRobot

FetionJiWaiRobot.class: FetionJiWaiRobot.java
	/usr/java/jdk/bin/javac -Xlint:unchecked -cp .:../../javaLib/commons-logging.jar:../../javaLib/Fetion.jar:../../javaLib/jiwaiMessage-1.1.jar FetionJiWaiRobot.java

clean:
	rm *.class
	rm fetion/*.class


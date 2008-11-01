JAVAC=/usr/java/jdk/bin/javac
class: MsnBuddyRobot.java GTalkBuddyRobot.java FetionBuddyRobot.java
	${JAVAC} -cp .:../../javaLib/cindy.jar:../../javaLib/commons-logging.jar:../../javaLib/jml-1.0b1.jar MsnBuddyRobot.java
	${JAVAC} -Xlint:unchecked -cp .:../../javaLib/smack.jar:../../javaLib/smackx.jar GTalkBuddyRobot.java
	${JAVAC}  -Xlint:unchecked -cp .:../../javaLib/fetion-1.1.jar FetionBuddyRobot.java

clean:
	rm *.class

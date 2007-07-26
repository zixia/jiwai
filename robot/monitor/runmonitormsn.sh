/usr/java/jdk/bin/java -cp .:../../javaLib/cindy.jar:../../javaLib/commons-logging.jar:../../javaLib/jml-1.0b1.jar:../../javaLib/jiwaiMessage.jar MsnMonitorRobot
RVALUE=$?
if [ $RVALUE -eq 0 ]; then
	echo "Success"
else
	echo "Failed";
fi

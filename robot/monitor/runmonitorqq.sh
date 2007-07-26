/usr/java/jdk/bin/java -cp .:../../javaLib/LumaQQ.jar:../../javaLib/log4j.jar:../../javaLib/jiwaiMessage.jar QQMonitorRobot
RVALUE=$?
if [ $RVALUE -eq 0 ]; then
	echo "Success"
else
	echo "Failed";
fi

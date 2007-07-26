/usr/java/jdk/bin/java -cp .:../../javaLib/jiwaiMessage.jar:../../javaLib/smack.jar:../../javaLib/smackx.jar GTalkMonitorRobot
RVALUE=$?
if [ $RVALUE -eq 0 ]; then
	echo "Success"
else
	echo "Failed";
fi

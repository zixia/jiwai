all :   compile

compile : MobizJiWaiRobot.class

t1  :
	curl -v -H'SOAPAction: "http://intermobiz.com/SendSMS"' -H'Content-Type: text/xml; charset=utf-8' http://td.qykx.net/intermobizService.asmx -d "@t/t1" 

t2  :
	curl -v -H'Content-Type: application/soap+xml; charset=utf-8' http://td.qykx.net/intermobizService.asmx -d "@t/t2" 

t3  :
	curl -v -H'SOAPAction: "http://intermobiz.com/GetSMS"' -H'Content-Type: text/xml; charset=utf-8' http://td.qykx.net/intermobizService.asmx -d "@t/t3" 

run: MobizJiWaiRobot.class MD5Sum.class
	/usr/java/default/bin/java -cp .:../../javaLib/commons-logging.jar:../../javaLib/Mobiz.jar:../../javaLib/jiwaiMessage.jar:../../javaLib/intermobiz.jar:../../javaLib/xfire-all-1.2.6.jar:../../javaLib/wsdl4j-1.5.1.jar:../../javaLib/jdom.jar:../../javaLib/commons-codec-1.3.jar:../../javaLib/commons-httpclient-3.1.jar -Djiwai.robot.log.dir=/var/log/robot MobizJiWaiRobot

MobizJiWaiRobot.class: MobizJiWaiRobot.java
	/usr/java/default/bin/javac -Xlint:unchecked -cp .:../../javaLib/commons-logging.jar:../../javaLib/Mobiz.jar:../../javaLib/jiwaiMessage.jar:../../javaLib/intermobiz.jar MobizJiWaiRobot.java

MD5Sum.class: MD5Sum.java
	/usr/java/default/bin/javac -Xlint:unchecked -cp .:../../javaLib/commons-logging.jar:../../javaLib/Mobiz.jar:../../javaLib/jiwaiMessage.jar:../../javaLib/intermobiz.jar MD5Sum.java

clean:
	rm *.class

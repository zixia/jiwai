JAVALIB=/opt/jiwai.de/javaLib
INDEXPATH=/opt/lucene/index

all: 
	@exit;

class: index increment Optimize.class SearchUser.class

index:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar Index.java

increment:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar Increment.java

user: index
	@rm -rf ${INDEXPATH}/user
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar Index ${INDEXPATH}/user user

status: index
	@rm -rf ${INDEXPATH}/status
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar Index ${INDEXPATH}/status status

tag: index
	@rm -rf ${INDEXPATH}/tag
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar Index ${INDEXPATH}/tag tag


search: SearchUser.class
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar SearchUser

SearchUser.class:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar SearchUser.java

Optimize.class :
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar Optimize.java

clean:
	rm *.class

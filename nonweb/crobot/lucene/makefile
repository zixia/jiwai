JAVALIB=/opt/jiwai.de/javaLib
INDEXPATH=/opt/lucene/index

all: 
	@exit;

class: IndexUser.class IndexStatus.class Optimize.class SearchUser.class

user: IndexUser.class
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexUser ${INDEXPATH}/user

status: IndexStatus.class
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexStatus ${INDEXPATH}/status

IndexUser.class:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexUser.java

IndexStatus.class:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexStatus.java

search: SearchUser.class
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar SearchUser

SearchUser.class:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar SearchUser.java

Optimize.class :
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar Optimize.java

clean:
	rm *.class

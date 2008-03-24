JAVALIB=/opt/jiwai.de/javaLib
INDEXPATH=/opt/lucene/index

all: 
	@exit;

class: IndexUser.class IndexStatus.class IndexTag.class Optimize.class SearchUser.class

user: IndexUser.class
	@rm -rf ${INDEXPATH}/user
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexUser ${INDEXPATH}/user true

status: IndexStatus.class
	@rm -rf ${INDEXPATH}/status
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexStatus ${INDEXPATH}/status true

tag: IndexTag.class
	@rm -rf ${INDEXPATH}/tag
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexTag ${INDEXPATH}/tag true

IndexUser.class:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexUser.java

IndexStatus.class:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexStatus.java

IndexTag.class:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar IndexTag.java

search: SearchUser.class
	java -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar SearchUser

SearchUser.class:
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar SearchUser.java

Optimize.class :
	javac -encoding UTF-8 -cp .:${JAVALIB}/jiwai-library-1.2.jar:${JAVALIB}/lucene-core-2.0.0.jar:${JAVALIB}/mysql-connector-java.jar Optimize.java

clean:
	rm *.class

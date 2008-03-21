import de.jiwai.lucene.*;
import org.apache.lucene.search.*;

class SearchUser{
	public static void main(String[] argv)
	{
		String q = "叽歪";
		LuceneSearch searcher = new LuceneSearch("/opt/lucene/index/users");
		Query query = searcher.parseQuery(q, "bio");

		KeyResult result = searcher.searchKey(query, 1, 10, "id", null, true );

		System.out.println( result.getResultCount()) ;
	}
}

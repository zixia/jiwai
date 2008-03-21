import de.jiwai.lucene.*;
import de.jiwai.dao.*;

public class Optimize
{
	public static void main(String[] argv){

		if ( argv.length < 1 )
		{
			System.err.println("must offer index_path");
		}

		LuceneIndex indexer = new LuceneIndex( argv[0] );
        indexer.optimize();
        indexer.close();
	}
}

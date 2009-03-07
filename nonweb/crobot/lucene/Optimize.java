import de.jiwai.lucene.*;
import de.jiwai.dao.*;

import java.io.File;   

import org.apache.lucene.analysis.standard.StandardAnalyzer; 
import org.apache.lucene.index.IndexWriter;   
import org.apache.lucene.store.FSDirectory;   

public class Optimize
{
	public static void main(String[] argv){

		if ( argv.length < 2 ) {
			System.err.println("must offer index_path");
		}

		if ( "optimize".equals(argv[0]) ) {
			LuceneIndex indexer = new LuceneIndex( argv[1] );
			indexer.optimize();
			indexer.close();
		} else if ( "merge".equals(argv[0]) ) {
			merge(argv[1], argv[2]);
		}
	}

	public static void merge(String fromDir, String toDir) {
		IndexWriter indexWriter = null;   
		File from = new File(fromDir);
		File to = new File(toDir);
		StandardAnalyzer sa = new StandardAnalyzer();
		try {   
			indexWriter = new IndexWriter(to, sa, false);   
			indexWriter.setMergeFactor(100000); 
			indexWriter.setMaxFieldLength(Integer.MAX_VALUE);   
			indexWriter.setMaxBufferedDocs(Integer.MAX_VALUE);   
			indexWriter.setMaxMergeDocs(Integer.MAX_VALUE);   
			FSDirectory[] fs = { FSDirectory.getDirectory(from, false) };   
			indexWriter.addIndexes(fs);   
			indexWriter.close();   
		} catch (Exception e) {   
			e.printStackTrace();   
		} finally {   
			try {   
				if (indexWriter != null)   
					indexWriter.close();   
			} catch (Exception e) {   

			}   
		}   
	}
}

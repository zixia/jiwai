import de.jiwai.lucene.*;
import de.jiwai.dao.*;

public class IndexStatus
{
	public static void main(String[] argv){
		//Table t = Execute.getOnePK("User", 89);
		//System.out.println( t.get("nameScreen")) ;

		int count = Execute.getCount("Status", "1");
		int stepLen = 10000;
		int step = ( count + stepLen - 1) / stepLen;
		if ( argv.length < 1 )
		{
			System.err.println("must offer index_path");
		}

		LuceneIndex indexer = new LuceneIndex( argv[0] );

		Table[] a = null;
		String clause = null;
		for( int i=0; i<step; i++ )
		{
			clause = "id < " + ((i+1)*1000);
			a = Execute.getArray("SELECT id,status From Status ORDER BY id ASC", i*stepLen, stepLen);


			for( int j=0; j<a.length; j++)
			{
				String keyField = "id";
				String keyValue = a[j].get("id");

				String[] otherField = {"status" };
				String[] otherValue = {
						a[j].get("status")
				};

				boolean token[] = { true };

				indexer.create(keyField, keyValue, otherField, otherValue, token);
			}
			
			System.out.println( "Step: " + ( stepLen * (i+1) ) );
		}

		indexer.flush();
        indexer.close();

	}
}

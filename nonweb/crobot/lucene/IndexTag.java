import de.jiwai.lucene.*;
import de.jiwai.dao.*;

public class IndexTag
{
	public static void main(String[] argv){
		//Table t = Execute.getOnePK("User", 89);
		//System.out.println( t.get("nameScreen")) ;

		int count = Execute.getCount("Tag", "1");
		int stepLen = 10000;
		int step = ( count + stepLen - 1) / stepLen;
		if ( argv.length < 1 )
		{
			System.err.println("must offer index_path");
		}

		boolean force_create = false;

		if ( argv.length > 1 && "true".equals( argv[1] ) )
			force_create = true;

		LuceneIndex indexer = new LuceneIndex( argv[0], force_create );

		Table[] a = null;
		for( int i=0; i<step; i++ )
		{
			a = Execute.getArray("SELECT id,name,description From Tag ORDER BY id ASC", i*stepLen, stepLen);

			for( int j=0; j<a.length; j++)
			{
				String keyField = "id";
				String keyValue = a[j].get("id");

				String[] otherField = { "name", "description" };
				String[] otherValue = {
					a[j].get("name")
						, a[j].get("description")
				};

				boolean token[] = { true, true };

				indexer.create(keyField, keyValue, otherField, otherValue, token);
			}

			System.out.println( "Step: " + (stepLen*(i+1)) );
		}

		indexer.flush();
		indexer.close();

	}
}

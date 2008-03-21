import de.jiwai.lucene.*;
import de.jiwai.dao.*;

public class IndexUser
{
	public static void main(String[] argv){
		//Table t = Execute.getOnePK("User", 89);
		//System.out.println( t.get("nameScreen")) ;

		int count = Execute.getCount("User", "1");
		int stepLen = 10000;
		int step = ( count + stepLen -1 ) / stepLen;
		if ( argv.length < 1 )
		{
			System.err.println("must offer index_path");
		}

		Table t = Execute.getOnePK("User", 89);
		System.out.println( t.get("nameScreen") );

		LuceneIndex indexer = new LuceneIndex( argv[0] );

		Table[] a = null;
		String clause = null;
		for( int i=0; i<step; i++ )
		{
			clause = "id < " + ((i+1)*stepLen);
			a = Execute.getArray("SELECT id,nameScreen,email,birthday,gender,bio FROM User ORDER BY ID ASC", i*1000, 1000);


			for( int j=0; j<a.length; j++)
			{
				String keyField = "id";
				String keyValue = a[j].get("id");

//		System.out.println( keyValue + ":" + a[j].get("bio") );


				String[] otherField = {"nameScreen", "email", "birthday", "gender", "bio" };
				String[] otherValue = {
					a[j].get("nameScreen")
						, new StringBuffer(a[j].get("email")).reverse().toString()
                        , a[j].get("birthday")
						, a[j].get("gender")
						, a[j].get("bio")
				};

				boolean token[] = { false, false, false, false, true };

				indexer.create(keyField, keyValue, otherField, otherValue, token);
			}
			
			System.out.println( "Step: " + (stepLen*(i+1) ) );

//			break;
		}

		indexer.flush();
        indexer.close();

	}
}

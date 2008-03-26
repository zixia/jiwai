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

		boolean force_create = false;

		if ( argv.length > 1 && "true".equals( argv[1] ) )
			force_create = true;

		LuceneIndex indexer = new LuceneIndex( argv[0] );

		Table[] a = null;
		for( int i=0; i<step; i++ )
		{
			a = Execute.getArray("SELECT s.id AS id,u.nameScreen as user,t.name AS tag,status,device,isSignature,isMms,unix_timestamp(s.timeCreate) AS time From Status s LEFT JOIN User u ON u.id=s.idUser LEFT JOIN Tag t ON t.id=s.idTag ORDER BY s.id ASC", i*stepLen, stepLen);

			for( int j=0; j<a.length; j++)
			{
				String keyField = "id";
				String keyValue = a[j].get("id");

				String[] otherField = { "device", "user", "signature", "mms", "tag", "time", "status" };
				String[] otherValue = {
					a[j].get("device")
						, a[j].get("user")
						, a[j].get("isSignature")
						, a[j].get("isMms")
						, a[j].get("tag")
						, a[j].get("time")
						, a[j].get("status")
				};

				boolean token[] = { false, false, false, false, false, false, true };

				indexer.create(keyField, keyValue, otherField, otherValue, token);
			}

			System.out.println( "Step: " + (stepLen*(i+1)) );
		}

		indexer.flush();
		indexer.close();

	}
}

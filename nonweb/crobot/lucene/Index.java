import de.jiwai.lucene.*;
import de.jiwai.dao.*;

public class Index
{
	public static void main(String[] argv){
		//Table t = Execute.getOnePK("User", 89);
		//System.out.println( t.get("nameScreen")) ;

		int count = Execute.getCount("Tag", "1");
		int stepLen = 10000;
		int step = ( count + stepLen - 1) / stepLen;
		if ( argv.length < 2 )
		{
			System.err.println("must offer index_path and index_name");
		}

		LuceneIndex indexer = new LuceneIndex( argv[0], true );

        if ( argv[1].equals("status") ) {
            indexStatus(indexer);
        }else if( argv[1].equals("user") ){
            indexUser(indexer);
        }else if( argv[1].equals("tag") ){
            indexTag(indexer);
        }

		indexer.flush();
		indexer.close();
    }

    static public void indexUser(LuceneIndex indexer) {

		int count = Execute.getCount("User", "1");
		int stepLen = 10000;
		int step = ( count + stepLen - 1) / stepLen;

		Table[] a = null;
		for( int i=0; i<step; i++ )
		{
			a = Execute.getArray("SELECT id,nameScreen,nameFull,email,birthday,gender,bio FROM User ORDER BY ID ASC", i*stepLen, stepLen);


			for( int j=0; j<a.length; j++)
			{
				String keyField = "id";
				String keyValue = a[j].get("id");

				Table[] devices = Execute.getArray("SELECT address FROM Device WHERE idUser=" + keyValue + " AND secret=''" );
				String devicesValue = new StringBuffer(a[j].get("email")).reverse().toString();
				for( int m=0; m<devices.length; m++ )
				{
					devicesValue += " " + devices[m].get("address");
				}

				String[] otherField = {"nameScreen","nameFull", "birthday", "gender", "devices", "bio" };
				String[] otherValue = {
					a[j].get("nameScreen")
						, a[j].get("nameFull")
						, a[j].get("birthday")
						, a[j].get("gender")
						, devicesValue
						, a[j].get("bio")
				};

				boolean token[] = { false, false, false, false, true, true };

				indexer.create(keyField, keyValue, otherField, otherValue, token);
			}

			System.out.println( "Step: " + (stepLen*(i+1)) );
		}

	}

    static public void indexTag(LuceneIndex indexer){

		int count = Execute.getCount("Tag", "1");
		int stepLen = 10000;
		int step = ( count + stepLen - 1) / stepLen;

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

	}

    static public void indexStatus(LuceneIndex indexer) {

		int count = Execute.getCount("Status", "1");
		int stepLen = 10000;
		int step = ( count + stepLen - 1) / stepLen;

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

	}
}

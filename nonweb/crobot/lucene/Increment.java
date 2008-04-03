import de.jiwai.lucene.*;
import de.jiwai.dao.*;
import java.io.*;
import java.util.*;
import java.text.*;

public class Increment
{

    static public DateFormat dateformat = DateFormat.getDateInstance();

    public static Vector<String> getIdList(String index){
            
        Vector<String> v = new Vector<String>(); 

        try {
            BufferedReader br = new BufferedReader(new InputStreamReader(new FileInputStream("/tmp/update_"+index+"_u")));

            String line = br.readLine();
            while( null != line ) {
                v.add( line );
                line = br.readLine();
            }
        }catch(Exception e){
        }
        return v;
    }

    public static void main(String[] argv){
        //Table t = Execute.getOnePK("User", 89);
        //System.out.println( t.get("nameScreen")) ;

        boolean force_create = false;

		LuceneIndex indexer = new LuceneIndex( argv[0], false );

        Vector<String> v = getIdList(argv[1]);

        if ( argv[1].equals("status") ){
            for( String id:v ) updateStatus(indexer, id);
        }else if ( argv[1].equals("tag") ){
            for( String id:v ) updateTag(indexer, id);
        }else if ( argv[1].equals("user") ){
            for( String id:v ) updateUser(indexer, id);
        }

        indexer.flush();
        indexer.close();

    }

    static public void updateTag(LuceneIndex indexer, String id){
        Table record = Execute.getOnePK( "User", id );
        if ( null == record ){
            indexer.delete( "id", id );
            return;
        }

        boolean[] token = { true, true };
        String[] other_field = {"name", "description"};
        String[] other_value = {
            record.get("name"),
            record.get("description"),
        };

        indexer.update( "id", id, other_field, other_value, token );
    }

    static public void updateUser(LuceneIndex indexer, String id){

        Table record = Execute.getOnePK( "User", id );
        if ( null == record ){
            indexer.delete( "id", id );
            return;
        }

        Table[] devices = Execute.getArray("SELECT address FROM Device WHERE idUser="+id+" AND secret=''");
        String devices_value = new StringBuffer(record.get("email")).reverse().toString();
        for( Table one: devices )
        {   
            devices_value += ' ' + one.get("address");
        }   

        boolean[] token = {false, false, false, false, true, true};
        String[] other_field = {"nameScreen", "nameFull", "birthday", "gender", "devices", "bio"};
        String[] other_value = {
            record.get("nameScreen"),
            record.get("nameFull"),
            record.get("birthday"),
            record.get("gender"),
            devices_value,
            record.get("bio")
        };

        indexer.update( "id", id, other_field, other_value, token );
    }

    static public void updateStatus(LuceneIndex indexer, String id) {

        Table record = Execute.getOnePK( "Status", id );
        if ( null == record ){
            indexer.delete( "id", id );
            return;
        }

        try{

            boolean[] token = {true, false, false, false, false, false, false};
            Table user = Execute.getOnePK("User", record.get("idUser"));
            Table tag = Execute.getOnePK("Tag", record.get("idTag"));
            String[] other_field = {"status", "user", "device", "mms", "tag", "time", "signature"};
            String[] other_value = { 
                record.get("status"),
                null==user ? "" : user.get("nameScreen"),
                record.get("device"),
                record.get("isMms"),
                null==tag ? "" : tag.get("name"),
                String.valueOf(dateformat.parse(record.get("timeCreate")).getTime()),
                record.get("isSignature")
            };

            indexer.update( "id", id, other_field, other_value, token );
        }catch(Exception e){}
    }
}
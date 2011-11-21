/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

package orientserializer;

import com.orientechnologies.orient.core.db.document.ODatabaseDocumentTx;
import com.orientechnologies.orient.core.db.record.ODatabaseFlat;
import com.orientechnologies.orient.core.id.ORID;
import com.orientechnologies.orient.core.id.ORecordId;
import com.orientechnologies.orient.core.record.impl.ODocument;
import com.orientechnologies.orient.core.sql.query.OSQLSynchQuery;
import java.util.List;

/**
 *
 * @author piotrus
 */
public class Main {

    /**
     * @param args the command line arguments
     */
    public static void main(String[] args) {
		//ODatabaseDocumentTx db =
		//	new ODatabaseDocumentTx("remote:localhost/asvis").open("admin", "admin");
		//ODatabaseFlat db =
		//	new ODatabaseFlat("remote:localhost/asvis").open("admin", "admin");

		ODatabaseFlat db =
			new ODatabaseFlat("local:/www/zpi/asvis/db/vendor/orientdb/databases/asvis").open("admin", "admin");

		//for (ODocument node : db.browseClass("ASNode")) {
		//	System.out.println(node.field("@rid"));
		//}

		//List<ODocument> resultset =
		//	db.query(new OSQLSynchQuery<ODocument>("SELECT FROM ASNode WHERE num = 3").setFetchPlan("*:5"));

		ODocument o = db.load(new ORecordId(5, 1), "*:5 ASNode.pools:0");
		System.out.println(o.isLazyLoad());

		db.close();
    }

}

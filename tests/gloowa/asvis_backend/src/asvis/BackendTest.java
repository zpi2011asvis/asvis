package asvis;

import java.util.LinkedList;

import com.orientechnologies.orient.core.db.document.ODatabaseDocumentTx;
import com.orientechnologies.orient.core.db.object.ODatabaseObjectTx;
import com.orientechnologies.orient.core.db.record.ODatabaseFlat;
import com.orientechnologies.orient.core.entity.OEntityManager;
import com.orientechnologies.orient.core.id.ORecordId;
import com.orientechnologies.orient.core.record.impl.ODocument;

import backend.ASNode;
import backend.OrientEngine;

public class BackendTest {

	public static void main(String [] args) {
//		ODatabaseFlat db = new ODatabaseFlat("remote:localhost/asvis").open("admin", "admin");
//		ODatabaseFlat db = new ODatabaseFlat("local:" + Config.ASVIS_DB_LOCATION).open("admin", "admin");
//		ODocument o = db.load(new ORecordId(5, 1), "*:5 ASNode.pools:0");
//		System.out.println(o.field("out"));		
		
		long time = System.currentTimeMillis();
		System.out.print("Opening OrientDB connection... ");
		ODatabaseObjectTx odb = new ODatabaseObjectTx("local:"+Config.ASVIS_DB_LOCATION);
//		ODatabaseDocumentTx odb = new ODatabaseDocumentTx("local:"+Config.ASVIS_DB_LOCATION);
		
		odb.open("admin", "admin");
		System.out.println("opened in " + (System.currentTimeMillis() - time)/1000.0 + "s");
		
		OEntityManager em = odb.getEntityManager();
		em.registerEntityClass(ASNode.class);
		
		OrientEngine engine = new OrientEngine(odb);
		LinkedList<ASNode> graph = engine.structureGraph(3, 2);
		
		System.out.println(graph.size());
		System.out.println(graph);
		
		odb.close();
	}
	
}

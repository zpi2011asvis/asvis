package backend;

import java.util.ArrayList;
import java.util.LinkedList;
import java.util.List;

import com.orientechnologies.orient.core.db.document.ODatabaseDocumentTx;
import com.orientechnologies.orient.core.db.object.ODatabaseObjectTx;
import com.orientechnologies.orient.core.db.object.OLazyObjectList;
import com.orientechnologies.orient.core.id.ORecordId;
import com.orientechnologies.orient.core.query.OQueryHelper;
import com.orientechnologies.orient.core.query.nativ.ONativeSynchQuery;
import com.orientechnologies.orient.core.query.nativ.OQueryContextNative;
import com.orientechnologies.orient.core.query.nativ.OQueryContextNativeSchema;
import com.orientechnologies.orient.core.sql.query.OSQLAsynchQuery;
import com.orientechnologies.orient.core.sql.query.OSQLSynchQuery;

public class OrientEngine {
	
	private ODatabaseObjectTx _orientDb;
	
	private LinkedList<ASNode> _nodes;
	
	public OrientEngine(ODatabaseObjectTx orientDb) {
		_orientDb = orientDb;
		_nodes = new LinkedList<ASNode>();
	}
	
	public LinkedList<ASNode> structureGraph(int nodeNum, int depth) {
				
//		OSQLSynchQuery<ASNode> query = new OSQLSynchQuery<ASNode>("SELECT FROM ASNode WHERE num = "+nodeNum);
//		query.setFetchPlan("*:"+depth);
		

		long time = System.currentTimeMillis();
		System.out.print("Querying db... ");
		
		OSQLSynchQuery<ASNode> query = new OSQLSynchQuery<ASNode>("SELECT FROM ASNode WHERE num = " + nodeNum);
		query.setFetchPlan("*:" + depth);
		
		ArrayList<ASNode> result = _orientDb.query(query);
		
//		ASNode result = (ASNode) _orientDb.load(new ORecordId("5:3"), "*:10");
		
		System.out.println("done in " + (System.currentTimeMillis() - time)/1000.0 + "s."); // ("+result.size()+" records)");
		ASNode root = result.get(0);

		time = System.currentTimeMillis();
		System.out.print("Mapping result... ");
		map(root, 1, depth+1);
		System.out.println("done in " + (System.currentTimeMillis() - time)/1000.0 + "s");

		time = System.currentTimeMillis();
		System.out.print("Removing out of range nodes from ins and outs... ");
		for(ASNode node : _nodes) {
			removeOutOfDepth(node);
		}
		System.out.println("done in " + (System.currentTimeMillis() - time)/1000.0 + "s");
		
		return _nodes;
	}
	
	private void map(ASNode node, int currentDepth, int depth) {		
		if (!_nodes.contains(node)) {
			node.distance = currentDepth - 1;
			_nodes.add(node);
			
			if(currentDepth < depth) {
				if(node.getOut() != null) {
					for (ASNode n : node.getOut()) {
						map(n, currentDepth+1, depth);
					}
				}
				if(node.getIn() != null) {
					for (ASNode n : node.getIn()) {
						map(n, currentDepth+1, depth);
					}
				}
			}
		}
	}
	
	private void removeOutOfDepth(ASNode node) {
		LinkedList<ASNode> toRemove = null;
		if(node.getOut() != null) {
			toRemove = new LinkedList<ASNode>();
			for(ASNode n : node.getOut()) {
				boolean exists = false;
				for(ASNode inGraph : _nodes) {
					if(inGraph.getNum() == n.getNum()) {
						exists = true;
						break;
					}
				}
				
				if(!exists) {
					toRemove.add(n);
				}
			}
			
			for(ASNode toRem : toRemove) {
				node.getOut().remove(toRem);
			}
		}
		
		if(node.getIn() != null) {
			toRemove = new LinkedList<ASNode>();
			for(ASNode n : node.getIn()) {
				boolean exists = false;
				for(ASNode inGraph : _nodes) {
					if(inGraph.getNum() == n.getNum()) {
						exists = true;
						break;
					}
				}
				
				if(!exists) {
					toRemove.add(n);
				}
			}
			
			for(ASNode toRem : toRemove) {
				node.getIn().remove(toRem);
			}
		}
	}
	
}

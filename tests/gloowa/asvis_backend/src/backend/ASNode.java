package backend;

import java.util.List;

import com.orientechnologies.orient.core.id.ORID;

public class ASNode {

	private String name;
	private int num;
	private String num_as_string;
	
	private List<ASNode> in;
	private List<ASNode> out;
	
	public int distance = -1;
	
	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	public int getNum() {
		return num;
	}
	public void setNum(int num) {
		this.num = num;
	}
	public String getNum_as_string() {
		return num_as_string;
	}
	public void setNum_as_string(String num_as_string) {
		this.num_as_string = num_as_string;
	}
	public List<ASNode> getIn() {
		return in;
	}
	public void setIn(List<ASNode> in) {
		this.in = in;
	}
	public List<ASNode> getOut() {
		return out;
	}
	public void setOut(List<ASNode> out) {
		this.out = out;
	}
	
	public String toString() {
		int weight = 0;
		String outs = "[";
		if(out != null) {
			int outCount = out.size();
			for(int i=0; i<outCount; i++) {
				if(i < outCount-1) {
					outs += out.get(i).getNum_as_string() + ",";
				} else {
					outs += out.get(i).getNum_as_string();
				}
			}
			
			weight += outCount;
		}
		outs += "]";

		String ins = "[";

		if(in != null) {
			int inCount = in.size();
			for(int i=0; i<inCount; i++) {
				if(i < inCount-1) {
					ins += in.get(i).getNum_as_string() + ",";
				} else {
					ins += in.get(i).getNum_as_string();
				}
			}
			
			weight += inCount;
		}
		ins += "]";
		// {"6":{"out":[1239,3356,16128,18990],"in":[1239,3356,16128,18990],"distance":0,"weight":8}
		return String.format(
				"{\""+num_as_string + "\":{" +
						"\"out\":"+outs +
						",\"in\":"+ins+
						",\"distance\":"+distance+
						",\"weight\":"+weight+
					"}}\n");
	}
}

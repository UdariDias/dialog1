import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.sql.*;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.text.SimpleDateFormat;
import java.util.Date;

public class ReadFile {
	private static Connection conn;
	private static Statement stmt;
	private static Statement stmtTemp;

	public static ArrayList<String[]> data = new ArrayList<String[]>();
	private static String stpName;
	private static String fileTime;
	private static String fileDate;

	public static void main(String[] input) throws SQLException {

		String filename = "C:\\Users\\diasd\\Desktop\\1h chunck 2\\13.20.txt";
		String testWord = "LSN";
		String testWord2="  tstpc0dmt1 ";
		String stpNamePreviousLine ="Command Accepted - Processing"; //Find STP ID
		String loginLine="";
		boolean check = false;


		try {
			BufferedReader br = new BufferedReader(new FileReader(filename));
			String currentLine;
			String previousLine1 = "";
			String previousLine2 = "";

			int lineCount = 0;
			int i = 0;
			ArrayList<String> list1 = new ArrayList<String>();
			ArrayList<String> list2 = new ArrayList<String>();

			for (i = lineCount; (currentLine = br.readLine()) != null; i++) {
				if (!previousLine1.isEmpty()) {
					if (previousLine1.contains(stpNamePreviousLine)) {
						stpName = currentLine.replaceAll("^[ ]+([^ ]+) ([^ ]+) ([^ ]+).*$", "$1");//STP ID
						fileDate = currentLine.replaceAll("^[ ]+([^ ]+) ([^ ]+) ([^ ]+).*$", "$2");//Login date
						fileTime = currentLine.replaceAll("^[ ]+([^ ]+) ([^ ]+) ([^ ]+).*$", "$3");//Login time
					}
				}

				if (currentLine.contains(testWord2)) {
					loginLine = currentLine;
				}
				if (lineCount > 3) {
					if (check) {
						if (i % 2 == 0) {

							String[] temp = new String[12];
							temp = Pattern1Match(previousLine2, temp);
							temp = Pattern2Match(previousLine1, temp);
							data.add(temp);

						}
//end the file reading
						if (currentLine.matches(".*(-------*).*")) {
							check = false;
						}

					}
					if (previousLine2.contains(testWord)) {
						check = true;
//						System.out.println(previousLine1);
					}
				}
				previousLine2 = previousLine1;
				previousLine1 = currentLine;
				lineCount++;
			}
			System.out.println(stpName);
			String[] even = list1.toArray(new String[list1.size()]);
			String[] odd = list2.toArray(new String[list2.size()]);


			try {
				br.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
//connection to sql
			conn = DriverManager.getConnection("jdbc:mysql://localhost/dialog","root","1993");

			SimpleDateFormat formatter = new SimpleDateFormat("yy-mm-dd");
			SimpleDateFormat formatter2 = new SimpleDateFormat("yyyy-mm-dd");
			String dateInString = fileDate;
			Date date = null;
			String finalDate = null;
			try {
				date = formatter.parse(dateInString);
				finalDate = formatter2.format(date);
//			System.out.println(date);
//			System.out.println(formatter.format(date));

			} catch (ParseException e) {
				e.printStackTrace();
			}
			for (String[] t : data)
			{
				ConnectionToMysql(stpName,finalDate,fileTime, t[0],t[1],t[2],t[3],t[4],t[6],t[5],t[7],t[8],t[10],t[9]);
			}
		}
		catch(IOException ex)

		{
			System.out.println("Error reading file named '" + filename + "'");
			ex.printStackTrace();
		}
	}
	public static void ConnectionToMysql(
			String STP_LINK,String DATE, String TIME, String LSN, String CONFIG_RSVD, String CONFIG_MAX, String TXTPS,
			String TXPEAK, String TX_PEAK_TIME,String TX_PEAK_DATE, String RCVTPS,String RCVPEAK,String RCV_PEAK_TIME,String RCV_PEAK_DATE) {


//insert data sql
		try {
			PreparedStatement statement = conn.prepareStatement("INSERT INTO IPTPS (STP_LINK,DATE,TIME,LSN,CONFIG_RSVD,CONFIG_MAX,TXTPS,TXPEAK,TX_PEAK_TIME,TX_PEAK_DATE,RCVTPS,RCVPEAK,RCV_PEAK_TIME,RCV_PEAK_DATE)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
			statement.setString(1, STP_LINK);
			statement.setDate(2, java.sql.Date.valueOf(DATE));
			statement.setTime(3, Time.valueOf(TIME));
			statement.setString(4, LSN);
			statement.setString(5, CONFIG_RSVD);
			statement.setString(6, CONFIG_MAX);
			statement.setString(7, TXTPS);
			statement.setString(8, TXPEAK);
			statement.setString(9, TX_PEAK_TIME);
			statement.setString(10, TX_PEAK_DATE);
			statement.setString(11, RCVTPS);
			statement.setString(12, RCVPEAK);
			statement.setString(13, RCV_PEAK_TIME);
			statement.setString(14, RCV_PEAK_DATE);
			statement.executeUpdate();
			statement.close();

		} catch (SQLException e) {
			e.printStackTrace();
		}
	}

	public static void Close() throws SQLException {
		conn.close();
	}
      //Splitting TX line data
	private static String[] Pattern1Match(String line,String[] temp) {
		String line1 = line;
		String pattern1 = "^\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)$";
		Pattern r1 = Pattern.compile(pattern1);
		Matcher m1 = r1.matcher(line1);

     //added tx line data to array
		while(m1.find( )) {

			temp[0] = (m1.group(1));
			temp[1] = (m1.group(3));
			temp[2] = (m1.group(4));
			temp[3] = (m1.group(6));
			temp[4] = (m1.group(7));
			temp[5] = (m1.group(8));
			temp[6] = (m1.group(9));

			System.out.println( m1.group(1) + "\t");
			System.out.println( m1.group(3) + "\t");
			System.out.println( m1.group(4) + "\t");
			System.out.println( m1.group(6) + "\t");
			System.out.println( m1.group(7) + "\t");
			System.out.println( m1.group(8) + "\t");
			System.out.println( m1.group(9) + "\t");
			System.out.println();
		}

		return temp;
	}
     //Splitting RCV line data
	private static String[] Pattern2Match(String line,String[] temp) {
		String line2 = line;
		String pattern2 = "^\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)\\s*([^\\s]+)$";
		Pattern r2 = Pattern.compile(pattern2);
		Matcher m2 = r2.matcher(line2);

		while(m2.find( )) {
//added rcv line data to temp array
			temp[7]  = (m2.group(2));
			temp[8]  = (m2.group(3));
			temp[9]  = (m2.group(4));
			temp[10] = (m2.group(5));

			System.out.println( m2.group(2) + "\t");
			System.out.println( m2.group(3) + "\t");
			System.out.println( m2.group(4) + "\t");
			System.out.println( m2.group(5) + "\t");
			System.out.println();
		}

		System.out.println();
		return temp;

	}
}

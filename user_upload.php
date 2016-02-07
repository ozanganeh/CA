<?php

//reads the data from user.csv file and outputs the values. capitalized the first letter of name and surname and lower case email addresses
$row = 1;
$fname = "users.csv";
if (($handle = fopen($fname, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data); //variable num shows number of fields in each row.
    $row++;
	if ( $row > 2){ //the first row of the file is heading ; starts showing the values from the seconf row
    for ($c=0; $c < $num; $c++) {
		if ($c < 2){// email is in the 3rd field which needs to be validated. thus,, the if condition separates the first two cols. from the third
		$data[$c] = ucfirst(strtolower($data[$c]));//strtolower function convers all the characters to lower case and ucfirst function capitalizes the first letter.
		echo $data[$c]. "<br />\n";		
		}else{
			if(filter_var($data[2], FILTER_VALIDATE_EMAIL)){ //check email validaity
			$data[2] = strtolower($data[2]);
			echo $data[2]. "<br />\n";
			}else{
				echo "Invalid email address <br />\n";
				echo "this is the invalid email:" . $data[2]. " <br />\n";
				$data[2] = NULL;
			}
		}
	}
	}
    }
  fclose($handle);
}


//creating connection to mysql
$con = mysql_connect("localhost","root","");
if (!$con){
	die("cannot connect: ". msql_error);
}
//create a database named Catalyst, if it is not already created.
if(mysql_select_db('Catalyst', $con)){
    echo "databse exists";
}else{
mysql_query("CREATE DATABASE Catalyst");
}
//create a table named Users, if it is not already created.
$result = mysql_query("SHOW TABLES LIKE 'Users'");
 if (!(mysql_num_rows($result) > 0) ){
mysql_select_db("Catalyst", $con);
$sql = "CREATE TABLE Users (
ID int UNSIGNED NOT NULL AUTO_INCREMENT,
Name varchar(20), 
Surname varchar(20),
Email varchar(254) ,
PRIMARY KEY (ID),
Unique (Email)
)";
//execute the query, and creat a table with four columns. ID (as primary key), name, surname, and email.
//email is set to be Unique Index. if there is no unique index, the assumtion is made that , it is not a valid user and his/her details are not be inserted into the table
mysql_query($sql,$con);
 }

mysql_close($con);


?>
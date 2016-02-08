<?php

//Using Console_CommandLine::addOption() method to add options to the parser.
require_once 'Console/CommandLine.php';
$parser = new Console_CommandLine();
$parser->description = 'A fantastic command line program that does nothing.';

//--file [fileName]. this will take csv file name from user.
$parser->addOption('FileName', array('long_name'=>'--file', 'description' => 'Use this option to specify users csv finename. How to use: user_upload.php --file FILENAME.csv','action'=>'StoreString'));

//--create_table. this will build the MySQL user table.
$parser->addOption('CreateTable', array('long_name'=>'--create_table', 'description' => 'Use this option to create User Table in DB. How to use: user_upload.php --create_table','action'=>'StoreTrue'));

//--dry_run. this is used to run the script but no insert into the DB. All parts are executed without altering the database.
$parser->addOption('DryRun', array('long_name'=>'--dry_run', 'description' => 'Use this option to run the script but no insert into the DB. How to use: user_upload.php --dry_run','action'=>'StoreTrue'));

//--insert_data. this is used to insert data into user table. it also creates the table if it is not already created.
$parser->addOption('InsertData', array('long_name'=>'--insert_data', 'description' => 'Use this option to insert data into the users table. How to use: user_upload.php --insert_data','action'=>'StoreTrue'));

//-u. to get MySQL username from command line.
$parser->addOption('MySQLUsername', array('short_name'=>'-u', 'description' => 'Use this option to input DB username. How to use: user_upload.php -u USERNAME - default username is "root"','action'=>'StoreString'));

//-p. to get MySQL password from command line.
$parser->addOption('MySQLPassword', array('short_name'=>'-p', 'description' => 'Use this option to input DB password. How to use: user_upload.php -p PASSWORD - there is no password by default.','action'=>'StoreString'));

//-h. to get MySQL host address from command line.
$parser->addOption('MySQLHost', array('short_name'=>'-h', 'description' => 'Use this option to input DB host address. How to use: user_upload.php -h HOSTADDRESS','action'=>'StoreString'));

$result = $parser->parse();
//print_r($result->options);
///////////////////

//this function is used to connect to the database.
function connectToDB($DBuname, $DBpass, $DBhost ){
	//creating connection to mysql
	$con = mysql_connect($DBhost,$DBuname,$DBpass);
	if (!$con){
		die("cannot connect: ". msql_error);
	}
	//create a database named Catalyst, if it is not already created.
	if(mysql_select_db('Catalyst', $con)){
		echo "Databse exists. \n";
	}else{
		if (mysql_query("CREATE DATABASE Catalyst")){
			echo "Database was created successfully. \n";
		}
	else
		echo "Error: " . mysql_error();
	}	
	return $con;
}
//this function is used to create User table
function creatUserTBL($con){
	//create a table named Users, if it is not already created.
	$TBexist = mysql_query("SHOW TABLES LIKE 'Users'");

	// Select 1 from table_name will return false if the table does not exist.
	$TBexist = mysql_query('select 1 from `Users` LIMIT 1');
	if($TBexist == FALSE){
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
		//$check = mysql_query($sql,$con);
		if (mysql_query($sql,$con)){
			echo "The User table is created.\n";
		}else
			echo "Error: " . mysql_error();
	 }		
}
//This function is used to Insert the data (name , surname and email) of each user into the user table.
function InsertDataToUserTBL($name, $surname, $email, $con){
	//the next lines are used to insert the data of a row into user table.
		mysql_select_db("Catalyst", $con);
		
		$sql = "INSERT INTO Users (Name, Surname, Email) VALUES ('$name', '$surname', '$email')";

		$check = mysql_query($sql,$con);
		if (isset ($check))
			echo "The user ". $name . "'s info is added to the Users Table \n";
		else
			echo "No new data added to the users table. The User table is up-to-date. \n";	
	
}
//this function retuens the data in a row of csv file
function ReadDataRows ($rowdata, $num){
	for ($c=0; $c < $num; $c++) {
		if ($c < 2){// email is in the 3rd field which needs to be validated. thus,, the if condition separates the first two cols. from the third
			$rowdata[$c] = ucfirst(strtolower($rowdata[$c]));//strtolower function convers all the characters to lower case and ucfirst function capitalizes the first letter.
			//echo $data[$c]. "\n";		
		}else{
			if(filter_var($rowdata[$c], FILTER_VALIDATE_EMAIL)){ //check email validaity
				$rowdata[$c] = strtolower($rowdata[$c]);
				//echo ($data[2]. "\n");
			}else{
				//echo "Invalid email address:  ". $rowdata[2]. "\n";
				$rowdata[$c] = NULL;
			}
		}
	}
	return $rowdata;
}


/////////////////////
//default values of username, password and host address to connect to DB:
$uname = "root";
$passw = "";
$hostAdd = "localhost";
//getting username, password and host address from the command line:
if (isset ($result->options['MySQLUsername']))
	$uname = $result->options['MySQLUsername'];
if (isset ($result->options['MySQLPassword']))
	$passw = $result->options['MySQLPassword'];
if (isset ($result->options['MySQLHost']))
	$hostAdd = $result->options['MySQLHost'];
//csv file name:
$filename = "users.csv"; //default filename
if (isset ($result->options['FileName'])) { //if name of the csv file , provided as the command line
	$filename = $result->options['FileName'];
}
///////////////////


if ($result->options['CreateTable']) {//if user asks to create user table in the command line.
	$con = connectToDB($uname, $passw, $hostAdd);
	creatUserTBL($con);
}
elseif ($result->options['DryRun']){
	//reads the data from user.csv file. capitalized the first letter of name and surname and lower case email addresses. check email validity.
	$row = 1;
	echo "Users info from the csv file: \n";
	if (($handle = fopen($filename, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$num = count($data); //variable num shows number of fields in each row.
			$row++;
			if ( $row > 2){ //the first row of the file is heading ; starts showing the values from the second row
				//read the values in each row of the csv file. process it (check validity).
				$rowdata = ReadDataRows ($data, $num);	
				echo $rowdata[0] . ", " . $rowdata[1]. ", ";
				if (is_null($rowdata[2]) )
					echo "Invvalid user email! \n";
				else
					echo $rowdata[2] . "\n";
			}
		}
		fclose($handle);
	}
}elseif ($result->options['InsertData']){
	$con = connectToDB($uname, $passw, $hostAdd);
	creatUserTBL($con);
	$row = 1;
	if (($handle = fopen($filename, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$num = count($data); //variable num shows number of fields in each row.
			$row++;
			if ( $row > 2){ //the first row of the file is heading ; starts showing the values from the second row
				//read the values in each row of the csv file. process it (check validity).
				$rowdata = ReadDataRows ($data, $num);
				//Insert Data into User Table. if the email address is valid.
				if (!is_null($rowdata[2]))
					//to make sure text with special character "'" is inserted into the DB, the following replace is needed.
					$rowdata[0] = str_replace("'","\'",$rowdata[0]);
					$rowdata[1] = str_replace("'","\'",$rowdata[1]);
					$rowdata[2] = str_replace("'","\'",$rowdata[2]);	
					
					InsertDataToUserTBL($rowdata[0], $rowdata[1], $rowdata[2], $con);
			}
		}
	fclose($handle);
	mysql_close($con); //closing coonection to mysql if existed.
	}
}
?>
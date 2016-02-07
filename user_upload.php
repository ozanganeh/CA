<?php
////////////////////////
//Using Console_CommandLine::addOption() method to add options to the parser.
require_once 'Console/CommandLine.php';
$parser = new Console_CommandLine(array(
   'description' => 'A useful description for your program.',
   'version'     => '0.0.1', // the version of your program
));
//--file [fileName]. this will take csv file name from user.
$parser->addOption('FileName', array('long_name'=>'--file', 'action'=>'StoreString'));
//$result1 = $parser->parse();

//--create_table. this will build the MySQL user table.
$parser->addOption('CreTbl', array('long_name'=>'--create_table', 'action'=>'StoreTrue'));
//$result2 = $parser->parse();

//--dry_run. this is used with --file to run the script but no insert into the DB. All parts are executed without altering the database.
$parser->addOption('DryRun', array('long_name'=>'--dry_run', 'action'=>'StoreTrue'));
//$result3 = $parser->parse();

//-u. to get MySQL username from command line.
$parser->addOption('MSu', array('short_name'=>'-u', 'action'=>'StoreString'));
//$result4 = $parser->parse();

//-p. to get MySQL password from command line.
$parser->addOption('MSp', array('short_name'=>'-p', 'action'=>'StoreString'));
//$result5 = $parser->parse();


//-h. to get MySQL host address from command line.
$parser->addOption('MSh', array('short_name'=>'-h', 'action'=>'StoreString'));
//$result6 = $parser->parse();

//-help. outputs the list of directives with details.
$parser->addOption('HLP', array('long_name'=>'--help', 'action'=>'StoreTrue'));
//$result7 = $parser->parse();

$result = $parser->parse();

//print_r($result->options);


///////////////////



if ($result->options['CreTbl']) {//if user asks to create user table in the command line.

//default values of sername, password and host address:
$uname = "root";
$passw = "";
$hostAdd = "localhost";

//getting username, password and host address from the command line:
if (isset ($result->options['MSu']))
	$uname = $result->options['MSu'];
if (isset ($result->options['MSp']))
	$passw = $result->options['MSp'];
if (isset ($result->options['MSh']))
	$hostAdd = $result->options['MSh'];


//creating connection to mysql
$con = mysql_connect($hostAdd,$uname,$passw);
if (!$con){
	die("cannot connect: ". msql_error);
}
//create a database named Catalyst, if it is not already created.
if(mysql_select_db('Catalyst', $con)){
    echo "Databse exists.";
}else{
if (mysql_query("CREATE DATABASE Catalyst")){
	echo "Database was created successfully";
}
else
	echo "Error: " . mysql_error();
}
//create a table named Users, if it is not already created.
$TBexist = mysql_query("SHOW TABLES LIKE 'Users'");
 if (!(mysql_num_rows($TBexist) > 0) ){
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
	 echo "The User table is created.";
 }
 }
 
}


if (!$result->options['CreTbl']){ //no further action is taken if MySQL user table was built

//reads the data from user.csv file. capitalized the first letter of name and surname and lower case email addresses. check email validity.
$row = 1;
$filename = "users.csv"; //default filename
if (isset ($result->options['FileName'])) { //if name of the csv file , provided as the command line
$filename = $result->options['FileName'];
}
if (($handle = fopen($filename, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data); //variable num shows number of fields in each row.
    $row++;
	if ( $row > 2){ //the first row of the file is heading ; starts showing the values from the seconf row
    for ($c=0; $c < $num; $c++) {
		if ($c < 2){// email is in the 3rd field which needs to be validated. thus,, the if condition separates the first two cols. from the third
		$data[$c] = ucfirst(strtolower($data[$c]));//strtolower function convers all the characters to lower case and ucfirst function capitalizes the first letter.
		echo $data[$c]. "\n";		
		}else{
			if(filter_var($data[2], FILTER_VALIDATE_EMAIL)){ //check email validaity
			$data[2] = strtolower($data[2]);
			echo ($data[2]. "\n");
			}else{
				echo "Invalid email address \n";
				echo "this is the invalid email:" . $data[2]. "\n";
				$data[2] = NULL;
			}
		}
	}
	if ((!$result->options['DryRun']) && ($result->options['CreTbl']) ){
	//the next three lines are used to insert the data of a row into user table.
mysql_select_db("Catalyst", $con);
$sql = "INSERT INTO Users (Name, Surname, Email) VALUES ('$data[0]', '$data[1]', '$data[2]')";
$check = mysql_query($sql,$con);	
	}
	}
    }
  fclose($handle);
}
if (isset ($con)){
 mysql_close($con); //closing coonection to mysql if existed.
}
 if (isset ($check)){
	echo "New data inserted. The User table is up-to-date.";
 }else{
	 echo "The User table is up-to-date.";
 }
}

?>
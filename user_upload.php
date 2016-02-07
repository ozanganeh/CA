<?php

//reads the data from user.csv file and outputs the values. capitalized the first letter of name and surname and lower case email addresses
$row = 1;
if (($handle = fopen("users.csv", "r")) !== FALSE) {
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


?>
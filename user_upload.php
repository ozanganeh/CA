<?php

//reading data from user.csv file
$row = 1;
if (($handle = fopen("users.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
    echo "$num fields in line $row: <br />\n";
    $row++;
    for ($c=0; $c < $num; $c++) {
		echo $data[$c] . "<br />\n";
    }
  }
  fclose($handle);
}


?>
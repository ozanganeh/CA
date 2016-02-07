<?php

for ($i = 1; $i <= 100; $i++) {
    echo "$i ";
//this if checkes if the number is divisible to 3	
if ( $i % 3 == 0){
    echo "triple";
}
//this if checkes if the number is divisible to 5
if ( $i % 5 == 0){
    echo "fiver";
}
//in case the number is divisible by both 3 and 5, the words "triple" and "fiver"
//will be printed with no spabe between them, resulting in outputting triplefiver
echo "\n";
}

?>
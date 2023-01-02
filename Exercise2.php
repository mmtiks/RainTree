<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "raintreeDB";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT pn, patient.last, patient.first, iname, DATE_FORMAT(from_date,'%m-%d-%y') from_date, DATE_FORMAT(to_date,'%m-%d-%y') to_date FROM patient, insurance 
        WHERE patient._id = insurance.patient_id
        ORDER BY from_date, last ASC";
$result = $conn->query($sql);

$alphabet = "ABCDEFGHIJKLMNOPQRSTUVXYZ";
$my_array = array_fill(0, 26, 0);
$total_letters = 0;


if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo  sprintf('%08d', $row["pn"]). ", ". $row["last"]. ", ". $row["first"]. ", ". $row["iname"]. ", ". $row["from_date"]. ", ". $row["to_date"]. "\n";
  }
} else {
  echo "0 results";
}


$sql = "SELECT patient.last, patient.first FROM patient";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    foreach(str_split(strtoupper($row["first"])) as $letter){
        $my_array[stripos($alphabet, $letter)]++;
        $total_letters++;
    };
    foreach(str_split(strtoupper($row["last"])) as $letter){
        $my_array[stripos($alphabet, $letter)]++;
        $total_letters++;
    }
}

$index = 0;
foreach($my_array as $count){
    if($count > 0){
        echo $alphabet[$index]. "\t ". $count. "\t". (round(100*$count/$total_letters,2)). "%\n";
    };
    $index++;
}
$conn->close();
?>
<?php
interface PatientRecord {
    public function get_id();
    public function get_pn();
  }
  
class Patient implements PatientRecord{
    private $_id;
    private $pn;
    public $first;
    public $last;
    private $dob;
    public $insurance_records = array();

    function __construct($pn) {
        $query = $this->fetchPatient($pn);
        $this->_id   = $query["_id"];
        $this->pn    = $pn;
        $this->first = $query["first"];
        $this->last  = $query["last"];
        $this->dob   = new DateTime($query["dob"]);
    }

    function fetchPatient($val){
        $conn = new PDO("mysql:host=localhost;dbname=raintreeDB", 'root', '');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT _id, patient.first, patient.last, dob FROM patient WHERE pn = :pn");
        $stmt->execute(['pn' => $val]);
        $result = $stmt -> fetch();
        $this->fetchInsurances($conn, $result["_id"]);
        $conn = null;
        return $result;
    }

    function fetchInsurances($conn, $val){
        $stmt = $conn->prepare('SELECT * FROM insurance WHERE patient_id = :pid');
        $stmt->execute(['pid' => $val]);
        foreach($stmt as $insurance){
            array_push($this->insurance_records, new Insurance($insurance['_id']));
        }
    }

    public function get_id(){
        return $this->_id;
    }

    public function get_pn(){
        return $this->pn;
    }

    public function get_fullname(){
        return $this->first." ". $this->last;
    }

    public function get_insurances(){
        return $this->insurance_records;
    }

    public function date_function($date){
        foreach($this->insurance_records as $record){
            echo sprintf('%08d', $this->pn). ", ". $this->first." ". $this->last. ", ". ($record->get_name()).", ". ($record->effective($date)? "Yes" : "No"). "\n";
        }
    }
}

class Insurance implements PatientRecord{
    private $_id;
    private $patient_id;
    private $iname;
    private $from_date;
    private $to_date;
    private $pn;

    public function get_id(){
        return $this->_id;
    }

    public function get_pn(){
        return $this->pn;
    }

    public function get_name(){
        return $this->iname;
    }

    function __construct($pn) {
        $query = $this->fetchInsurance($pn);
        $this->_id         = $query["_id"];
        $this->patient_id  = $query["patient_id"];
        $this->iname       = $query["iname"];
        $this->from_date   = new DateTime($query["from_date"]);
        $this->to_date     = new DateTime($query["to_date"]);
    }

    function fetchInsurance($val){
        $conn = new PDO("mysql:host=localhost;dbname=raintreeDB", 'root', '');
        $stmt = $conn->prepare("SELECT _id, patient_id, iname, from_date, to_date FROM insurance WHERE _id = :id");
        $stmt->execute(['id' => $val]);
        $result = $stmt -> fetch();
        $this->fetchPn($conn, $result["patient_id"]);
        $conn = null;
        return $result;
    }

    function fetchPn($conn, $patient_id){
        $stmt = $conn->prepare("SELECT pn FROM patient WHERE _id = :id");
        $stmt->execute(['id' => $patient_id]);
        $result = $stmt -> fetch();
        $this->pn = $result["pn"];
    }

    function effective($date){
        if($this->from_date > $date){
            return false;
        }
        if($this->to_date == null) return true;
        if($this->to_date > $date) return true;
        return false;
    }
}
$conn = new mysqli('localhost', 'root', '', 'raintreeDB');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
$sql = "SELECT pn FROM patient";
$result = $conn->query($sql);


if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $patient = new Patient($row['pn']);
        $patient->date_function(new DateTime());
    }
}
?>
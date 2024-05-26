<?php
include("inputs.php");

class Form {

    public $inputs = array();
    public string $method = "POST";
    public string $sql_table;
    public string $submit_button_text = "Submit";
    public string $sql_id;
    public string $sql_id_type = "i";
    public string $sql_pk_name = "id";
    public $errors = array();


    public function __construct(Input ...$inputs){
        $this->inputs = $inputs;
    }
    public function validate() {
        check_csrf_or_error($_POST['csrf_token']);
        $is_valid = true;
        foreach ($this->inputs as $i => $input) {
            if(isset($_POST[$input->name])){
                $input->value = trim($_POST[$input->name]); 
            } else {
                $input->value = ""; 
            }
            $is_valid ? $is_valid = $input->validate() : $input->validate(); 
            
        }
        return $is_valid;
    }

    public function save() {
        global $db;
        if(isset($this->sql_id)) {
            if(isset($this->sql_table)){
                $sqlStatement = "UPDATE $this->sql_table SET";
                foreach ($this->inputs as $i => $input) {
                    $sqlStatement.=" $input->name=?,";
                }
                $sqlStatement = rtrim($sqlStatement, ",");
                $sqlStatement.= " WHERE {$this->sql_pk_name}=?";


                $stmt = $db->prepare($sqlStatement);
                $values = array();
                $datatypes = "";
                foreach ($this->inputs as $i => $input) {
                    if($input->type == "password") {
                        $password = password_hash($input->value,PASSWORD_DEFAULT);
                        array_push($values,$password);
                    } else {
                        array_push($values,$input->value);
                    }
                    $datatypes.=$input->mysqli_type;
                }
                array_push($values,$this->sql_id);
                $datatypes.=$this->sql_id_type;
                $stmt->bind_param($datatypes, ...$values);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("sql_table is not set on the form");
            }
        } else {
            if(isset($this->sql_table)){
                $sqlStatement = "INSERT INTO $this->sql_table (";
                foreach ($this->inputs as $i => $input) {
                    $sqlStatement.=" $input->name,";
                }
                $sqlStatement = rtrim($sqlStatement, ",");
                $sqlStatement.=" ) VALUES ( ";

                foreach ($this->inputs as $i => $input) {
                    $sqlStatement.=" ?,";
                }
                $sqlStatement = rtrim($sqlStatement, ",");
                $sqlStatement.=" )";

                $stmt = $db->prepare($sqlStatement);
                $values = array();
                $datatypes = "";
                foreach ($this->inputs as $i => $input) {
                    if($input->type == "password") {
                        $password = password_hash($input->value,PASSWORD_DEFAULT);
                        array_push($values,$password);
                    } else {
                        array_push($values,$input->value);
                    }
                    $datatypes.=$input->mysqli_type;
                }
                $stmt->bind_param($datatypes, ...$values);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("sql_table is not set on the form");
            }
        }
    }

    // propogate values to input feilds from the database based on input name
    public function fetch_values(){
        global $db;
        if(isset($this->sql_id)) {
            $stmt = $db->prepare("SELECT * FROM $this->sql_table WHERE {$this->sql_pk_name}=?");
            $stmt->bind_param($this->sql_id_type,$this->sql_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            foreach ($row as $attribute => $value) {
                foreach ($this->inputs as $i => $input) {
                    if($input->name == $attribute && $input->type != "password" && $input->type != "file"){
                        $this->inputs[$i]->value = $value;
                    }
                }
            }
        } 
    }

    public function render(){
        $this->fetch_values();
        global $csrf_token;
        echo "<form method=\"{$this->method}\">";
        foreach ($this->inputs as $i => $input) {
            $input->render();
        }
        echo "<input type=\"hidden\" name=\"csrf_token\" value=\"{$csrf_token}\" />";
        foreach ($this->errors as $i => $error) {
            echo "<p class=\"error\">{$error}</p>";
        }
        echo "<input type=\"submit\" value=\"{$this->submit_button_text}\" />";
        echo "</form>";
        echo "<br>";

    }
}

?>

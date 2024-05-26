<?php
class Input {
    public string $type = "text";
    public string $id;
    public string $name;
    public string $label;
    public string $placeholder;
    public string $value;
    public string $mysqli_type = "s";
    public string $mysqli_table;
    public string $mysqli_pk_name;
    public string $mysqli_select_attribute;
    public $selectOptions = array();
    public $errors = array();
    public $minLength = INF;
    public bool $blank = false;
    public bool $displayLabel = true;
    public bool $isPositiveInt = false;
    public bool $noStatus = false;
    public $maxLength = INF;

    public function __construct($name,$label,$maxLength=INF,$minLength=INF,$type="text",$mysqli_type="s"){
        $this->name = $name;
        $this->label = $label;
        $this->type = $type;
        $this->placeholder = &$this->label;
        $this->value = "";
        $this->mysqli_type = $mysqli_type;
        $this->maxLength = $maxLength;
        $this->minLength = $minLength;
    }

    public function validate() {
        if( !$this->blank &&
            isset($this->value) &&
            empty(trim($this->value))) {

            array_push($this->errors,"{$this->label} cannot be empty.");
        }
        if($this->minLength != INF &&
            strlen($this->value) < $this->minLength ) {

            array_push($this->errors,"{$this->label} cannot be less than {$this->minLength} characters.");
        }
        if($this->maxLength != INF &&
            strlen($this->value) > $this->maxLength ) {

            array_push($this->errors,"{$this->label} cannot exceed {$this->maxLength} characters.");
        }
        if($this->mysqli_type == "i" && !is_numeric($this->value)){
            array_push($this->errors,"The value should be a number.");
        }
        if($this->mysqli_type == "i" && 
            is_numeric($this->value) &&
            ((int) $this->value) < 1
        ){
            array_push($this->errors,"The value should be larger than 0");
        }
        if($this->type == "email" && !filter_var($this->value, FILTER_VALIDATE_EMAIL)){
            array_push($this->errors,"Not a valid email");
        }
        if($this->type == "file" &&  
            empty($_FILES[$this->name]['name'])){
            array_push($this->errors,"No file selected.");
        }
        if($this->type == "file" && !empty($_FILES[$this->name]['name']) ) {
            $file_name = $_FILES[$this->name]['name'];
            $file_size = $_FILES[$this->name]['size'];
            $file_tmp  = $_FILES[$this->name]['tmp_name'];
            $file_type = $_FILES[$this->name]['type'];
            $split_file_name = explode('.',$file_name);
            $file_ext  = strtolower(end($split_file_name));
            $extensions = array("jpeg","jpg");
            if(in_array($file_ext,$extensions)=== false){
               array_push($this->errors,"Extension not allowed, please choose a JPG/JPEG file.");
            }
            // 1 MB
            if($file_size > 5 * 1024 * 1024){
               array_push($this->errors,"File size cannot exceed 5MB");
            }
        }

        if(count($this->errors)) {
            return false;
        } else {
            return true;
        }

    }

    public function fetchSelectValues() {
        if(isset($this->mysqli_table) && 
           isset($this->mysqli_select_attribute) &&
           isset($this->mysqli_pk_name)
        ){
            global $db;
            $SQL = "SELECT {$this->mysqli_select_attribute},{$this->mysqli_pk_name} FROM {$this->mysqli_table} WHERE status=1 ORDER BY {$this->mysqli_select_attribute}";
            if($this->noStatus){
                $SQL = "SELECT {$this->mysqli_select_attribute},{$this->mysqli_pk_name} FROM {$this->mysqli_table} ORDER BY {$this->mysqli_select_attribute}";
            }
            $stmt = $db->prepare($SQL);
            $stmt->execute();
            $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $this->selectOptions = array();
            foreach ($results as $key => $row) {
                $this->selectOptions[$row[$this->mysqli_pk_name]] = $row[$this->mysqli_select_attribute];
            }
        }
    }

    public function render() {
        if($this->type != "hidden"){
            if($this->displayLabel){
                echo "<label for=\"{$this->name}\">{$this->label}</label>";
            }
        } else {
            echo "<br>";
            echo "<br>";
        }
        if ($this->type!="text" && $this->type == "select") {
            echo "<select name=\"{$this->name}\" >";
            foreach ($this->selectOptions as $value => $label) {
                $isSelected = $value == $this->value ? "selected=true" : null; 

                echo "<option value=\"{$value}\" {$isSelected} >{$label}</option>";
            }
            echo "</select><br><br>";
        } else if($this->type!="text" && $this->type == "textarea") {
            echo "<textarea name=\"{$this->name}\" placeholder=\"{$this->placeholder}\">{$this->value}</textarea>";
        } else { 
            echo "<input type=\"{$this->type}\" value=\"{$this->value}\" name=\"{$this->name}\" placeholder=\"{$this->placeholder}\"  />";
        }
        foreach ($this->errors as $i => $error) {
            echo "<p class=\"error\">{$error}</p>";
        }
    }

}

?>

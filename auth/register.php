<?php
$Title = 'Register | Hardware World'; 
include("../config/all_config.php"); 
include("../lib/all_lib.php");
include("../partials/header.php"); 
?>

<link rel="stylesheet" href="/static/css/auth.css"> 
<div class="form-main">
<h1> Register </h1>
<br>

<?php

// name, label, max, min , type
$fname_input      = new Input("customer_fname","First Name",15,2);
$lname_input      = new Input("customer_lname","Last Name",15,2);

$house_name_input = new Input("customer_house_name","House Name",20,5);
$street_input     = new Input("customer_street","Street/Area",20,5);
$city_input       = new Input("customer_city","City",20,5);
$state_input      = new Input("customer_state", "State");
$state_input->type = "select";
$state_input->selectOptions = INDIAN_STATES;
$pincode_input    = new Input("customer_pincode","Pincode",6,3);
$phone_input      = new Input("customer_phone","Phone",10,8);

$email_input      = new Input("email","Email",50,5,"email");
$password_input   = new Input("password","Password",INF,8,"password");
$confirm_input    = new Input("password2","Confirm Password",INF,8,"password");

$form= new Form(
    $lname_input,
    $fname_input,

    $house_name_input,
    $street_input,
    $city_input,
    $state_input,
    $pincode_input,

    $phone_input,
    $email_input,
    $password_input,
    $confirm_input
);

$form->submit_button_text = "Register";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()){
        // fetch user from database
        $stmt = $db->prepare("SELECT * FROM tbl_login WHERE email = ?");
        $stmt->bind_param("s", $email_input->value);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($user){

            array_push($email_input->errors,"There is already an account with this email.");

        } else if($password_input->value != $confirm_input->value){

            array_push($password_input->errors,"Passwords dont match!");
            array_push($confirm_input->errors,"Passwords dont match!");

        } else {

            $db->begin_transaction();
            try {
                // insert user to tbl_login
                $stmt = $db->prepare("INSERT INTO tbl_login (email,password,type,status) VALUES (?,?,?,?)");
                $user_type = "customer";
                $user_status = 1;
                $password = password_hash($password_input->value,PASSWORD_DEFAULT);
                $stmt->bind_param("sssi",$email_input->value,$password,$user_type,$user_status);
                $stmt->execute();
                $stmt->close();

                $CUSOMTER_INSERT_SQL = "
                    INSERT INTO tbl_customer (
                        email,
                        customer_fname,
                        customer_lname,
                        customer_house_name,
                        customer_street,
                        customer_city,
                        customer_state,
                        customer_pincode,
                        customer_phone 
                    ) VALUES (?,?,?,?,?,?,?,?,?)
                ";
                $stmt = $db->prepare($CUSOMTER_INSERT_SQL);
                $stmt->bind_param("sssssssss",
                    $email_input->value,
                    $fname_input->value,
                    $lname_input->value,
                    $house_name_input->value,
                    $street_input->value,
                    $city_input->value,
                    $state_input->value,
                    $pincode_input->value,
                    $phone_input->value,
                );
                $stmt->execute();
                $stmt->close();

                $db->commit();

            } catch (mysqli_sql_exception $exception) {
                $db->rollback();
            }

            $stmt = $db->prepare("SELECT * FROM tbl_login WHERE email = ?");
            $stmt->bind_param("s", $email_input->value);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $_SESSION['user'] = $user;

            redirect('/site/products/index.php');
        }
    }
}

echo "<form method=\"{$form->method}\">";

echo '<div class="form-row">';
    echo "<div>";
    $fname_input->render();
    echo "</div>";
    echo "<div>";
    $lname_input->render();
    echo "</div>";
echo "</div>";

echo '<div class="form-row">';
    echo "<div>";
    $email_input->render();
    echo "</div>";
    echo "<div>";
    $phone_input->render();
    echo "</div>";
echo "</div>";

echo '<div class="form-row">';
    echo "<div>";
    $password_input->render();
    echo "</div>";
    echo "<div>";
    $confirm_input->render();
    echo "</div>";
echo "</div>";

echo "<br><br><label> Adresss </label><br>";

echo '<div class="form-row">';
    echo "<div>";
    $house_name_input->render();
    echo "</div>";
    echo "<div>";
    $street_input->render();
    echo "</div>";
echo "</div>";


echo '<div class="form-row">';
    echo "<div>";
    $city_input->render();
    echo "</div>";
    echo "<div>";
    $state_input->render();
    echo "</div>";
echo "</div>";
$pincode_input->render();
global $csrf_token;
echo "<input type=\"hidden\" name=\"csrf_token\" value=\"{$csrf_token}\" />";
echo "<input type=\"submit\" value=\"{$form->submit_button_text}\" />";
echo "</form>";
echo "<br>";

?>

</div>


<div class="canvas-logo">
    <canvas id="canvas"></canvas>
    <h1>hardware <br> world </h1>
</div>


<script src="/static/js/canvas.js"></script>

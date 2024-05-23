<?php
$Title = 'Dashboard | Add Courier'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
?>

<h1> Add Courier </h1>
<br>


<?php

$name_input          = new Input("courier_name","Courier Name",30,5);
$building_name_input = new Input("courier_building_name","Builing Name",20,5);
$street_input        = new Input("courier_street","Street/Area",20,5);
$city_input          = new Input("courier_city","City",20,5);
$state_input         = new Input("courier_state", "State");
$state_input->type = "select";
$state_input->selectOptions = INDIAN_STATES;
$pincode_input    = new Input("courier_pincode","Pincode",6,3);
$phone_input      = new Input("courier_phone","Phone",10,8);

$email_input = new Input("email","Email",50,5,"email");
$password_input = new Input("password","Password",INF,8,"password");
$confirm_input = new Input("password2","Confirm Password",INF,8,"password");

$hidden_input      = new Input("hidden","hidden",INF,INF,"hidden");

$form= new Form(
    $name_input,

    $building_name_input,
    $street_input,
    $city_input,
    $state_input,
    $pincode_input,

    $phone_input,
    $email_input,
    $password_input,
    $confirm_input
);

$form->submit_button_text = "Add";

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

        } else if($_SESSION['user']['type'] == 'admin'){
            array_push($hidden_input->errors,"Admin users cannot add couriers");
        } else {

            $stmt = $db->prepare("SELECT * FROM tbl_staff WHERE email = ?");
            $stmt->bind_param("s", $_SESSION['user']['email']);
            $stmt->execute();
            $staff = $stmt->get_result()->fetch_assoc();
            $stmt->close();


            $db->begin_transaction();
            try {

                // insert user to tbl_login
                $stmt = $db->prepare("INSERT INTO tbl_login (email,password,type,status) VALUES (?,?,?,?)");
                $user_type = "courier";
                $user_status = 1;
                $password = password_hash($password_input->value,PASSWORD_DEFAULT);
                $stmt->bind_param("sssi",$email_input->value,$password,$user_type,$user_status);
                $stmt->execute();
                $stmt->close();

                $VENDOR_INSERT_SQL = "
                    INSERT INTO tbl_courier (
                        email,
                        courier_name,
                        courier_building_name,
                        courier_street,
                        courier_city,
                        courier_state,
                        courier_pincode,
                        courier_phone,
                        staff_id
                    ) VALUES (?,?,?,?,?,?,?,?,?)
                ";
                $stmt = $db->prepare($VENDOR_INSERT_SQL);
                $stmt->bind_param("ssssssssi",
                    $email_input->value,
                    $name_input->value,
                    $building_name_input->value,
                    $street_input->value,
                    $city_input->value,
                    $state_input->value,
                    $pincode_input->value,
                    $phone_input->value,
                    $staff['staff_id']
                );
                $stmt->execute();
                $stmt->close();

                $db->commit();

            } catch (mysqli_sql_exception $exception) {
                echo $exception;
                $db->rollback();
            }

            Messages::add("success","Courier was added successfully!");

            redirect('/admin/couriers/');
        }
    }
}

echo "<form method=\"{$form->method}\">";

echo '<div class="form-row">';
    echo "<div>";
    $name_input->render();
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
    $building_name_input->render();
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
$hidden_input->render();
echo "<input type=\"submit\" value=\"{$form->submit_button_text}\" />";
echo "</form>";
echo "<br>";

?>

</div>



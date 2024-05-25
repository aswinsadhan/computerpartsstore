<?php
$Title = 'Dashboard | Add Staff'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("admin");
?>

<h1> Add Staff </h1>
<br>

<?php

$fname_input      = new Input("staff_fname","First Name",15,2);
$lname_input      = new Input("staff_lname","Last Name",15,2);

$phone_input      = new Input("customer_phone","Phone",10,8);

$house_name_input = new Input("staff_house_name","House Name",20,5);
$street_input     = new Input("staff_street","Street/Area",20,5);
$city_input       = new Input("staff_city","City",20,5);
$state_input      = new Input("staff_state", "State");
$state_input->type = "select";
$state_input->selectOptions = INDIAN_STATES;
$pincode_input    = new Input("staff_pincode","Pincode",6,3);
$salary_input     = new Input("staff_salary","Salary",9,4,"text","i");

$email_input = new Input("email","Email",50,5,"email");

$password_input = new Input("password","Password",INF,8,"password");
$confirm_input = new Input("password2","Confirm Password",INF,8,"password");

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
    $confirm_input,
    $salary_input
);

$form->submit_button_text = "Add Staff";

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
                $user_type = "staff";
                $user_status = 1; 
                $password = password_hash($password_input->value,PASSWORD_DEFAULT);
                $stmt->bind_param("sssi",$email_input->value,$password,$user_type,$user_status);
                $stmt->execute();
                $stmt->close();

                $STAFF_INSERT_SQL = "
                    INSERT INTO tbl_staff (
                        email,
                        staff_fname,
                        staff_lname,
                        staff_house_name,
                        staff_street,
                        staff_city,
                        staff_state,
                        staff_pincode,
                        staff_phone,
                        staff_salary
                    ) VALUES (?,?,?,?,?,?,?,?,?,?)
                ";
                $stmt = $db->prepare($STAFF_INSERT_SQL);
                $stmt->bind_param("ssssssssss",
                    $email_input->value,
                    $fname_input->value,
                    $lname_input->value,
                    $house_name_input->value,
                    $street_input->value,
                    $city_input->value,
                    $state_input->value,
                    $pincode_input->value,
                    $phone_input->value,
                    $salary_input->value,
                );
                $stmt->execute();
                $stmt->close();
                $db->commit();
            } catch (mysqli_sql_exception $exception) {
                echo $exception; 
                $db->rollback();
            }
            Messages::add("success","Staff '{$fname_input->value}' was added successfully!");
            redirect('/admin/staff/');
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

$salary_input->render();

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



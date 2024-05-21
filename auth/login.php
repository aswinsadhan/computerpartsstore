<?php
$Title = 'Sign In | Hardware World'; 
include("../config/all_config.php"); 
include("../lib/all_lib.php");
include("../partials/header.php"); 
?>

<link rel="stylesheet" href="/static/css/auth.css"> 
<div class="form-main">
<h1> Log in  </h1>
<br>

<?php
// name, label, max, min , type
$email_input = new Input("email","Email",50,3,"email");
$password_input = new Input("password","Password",INF,8,"password");

$form = new Form($email_input,$password_input);
$form->submit_button_text = "Login";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()){
        // fetch user from database
        $stmt = $db->prepare("SELECT * FROM tbl_login WHERE email = ? AND status=1");
        $stmt->bind_param("s", $email_input->value);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if(!$user){
            array_push($email_input->errors,"No such account exists!");
        } else 
        if(!password_verify($password_input->value,$user['password'])){
            array_push($password_input->errors,"Wrong Password!");
        } else 
        if($user['status'] == 0){
            array_push($email_input->errors,"No such account exists!");
        } else {
            $_SESSION['user'] = $user;
            if($user['type'] == "admin") {
                redirect('/admin/');
            } else if($user['type'] == "staff") {
                redirect('/admin/');
            } else if($user['type'] == "courier") {
                redirect('/admin/delivery');
            } else {
                redirect('/site/products');
            }
        }
    }
}
$form->render();
?>
</div>

<div class="canvas-logo">
    <canvas id="canvas"></canvas>
    <h1>hardware <br> world </h1>
</div>


<script src="/static/js/canvas.js"></script>




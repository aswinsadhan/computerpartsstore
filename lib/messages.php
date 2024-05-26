<?php

// tag: info,error,success

class Messages {
    public static function add($tag,$message){
        if(!isset($_SESSION['messages'])){
            $_SESSION['messages'] = array($tag => $message);
        } else {
            $_SESSION['messages'][$tag] = $message;
        }
    }
    public static function show(){
        if(!isset($_SESSION['messages'])){
            return; 
        }
        $i = 0;
        foreach ($_SESSION['messages'] as $tag => $message) {
            echo "
                <div id=\"message-container{$i}\" class=\"message-container {$tag}\">
                    <p class=\"message-text {$tag}\">{$message}</p>
                    <i class=\"fa-sharp fa-solid fa-xmark\"></i>
                </div>
            ";
            $i++;
            unset($_SESSION['messages'][$tag]);
        }
    }
}

?>

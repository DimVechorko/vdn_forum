<?php
session_start();
require_once ('inc/classes.php');
if(isset ($_SESSION['id_user'])){
    $obj= new Profile();
    $arr_var=$obj->viewProfile($_SESSION['id_user']);
    $obj= new CreateForm();
    $tpl=$obj->getTPL('form_viewprofile');
    $arr_lab=$obj->arrayLabels();
    $obj= new ProcessTPL();
    $html=$obj->processTemplace($arr_lab,$arr_var,$tpl);
    echo($html);
}else{
    $home_url='http://'.$_SERVER['HTTP_HOST'].'/forum/tpl/form_login.html?';
    header('Location:'.$home_url);
    exit;
}
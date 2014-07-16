<?php
session_start();
require_once ('inc/classes.php');
$obj= new Profile();
$arr_var=$obj->viewProfile(2);
$obj= new CreateForm();
$tpl=$obj->getTPL('form_viewprofile');
$arr_lab=$obj->arrayLabels();
$obj= new ProcessTPL();
$html=$obj->processTemplace($arr_lab,$arr_var,$tpl);
echo($html);
<?php
class ConnectDB {
    protected  $host='localhost';
    protected  $dbname='vdn_forum';
    protected  $user='root';
    protected  $pass='root';
    protected  $DBH;

    function __construct(){
        try {
            $this->DBH = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
        }
        catch(PDOException $e) {
            echo $e->getMessage();
        }
    }
}

class SignUp extends ConnectDB {
    /**
     * Регистрация нового пользователя
     * @param $username
     * @param $password
     * @param $repassword
     * @return bool
     */
    public function writeData($username,$password,$repassword){
        session_start();
        if ($password==$repassword){
            $select = $this->DBH->prepare("SELECT * FROM vdn_users WHERE username = '$username'");
            $select->execute();
            $count=$select->rowCount();
            $row=$select->fetch();
            var_dump($count);
            if($count==0){
                $insert = $this->DBH->prepare("INSERT INTO vdn_users (username,password,date_registration,id_status)VALUE ('$username',SHA('$password'),NOW(),'2')");
                $id_user=$row['id_user'];
                $_SESSION['id_user']=$id_user;
                $username=$row['username'];
                $_SESSION['username']=$username;
                return $insert->execute();
            }else{$_SESSION['name_error']="that name already exists";}
        }else{$_SESSION['password_error']="passwords do not match";}
    }
}

class Authorization extends ConnectDB {
    /**
     * Вход зарегестрированного пользователя
     * @param $username
     * @param $password
     * @return bool
     */
    public function  LogIn($username,$password){
        session_start();
        $error_msg=" ";
        if(!isset($_SESSION['id_user'])){
                if(!empty($username) && !empty($password)){
                    $select = $this->DBH->prepare("SELECT * FROM vdn_users WHERE username='$username' AND password=SHA('$password')");
                    $select->execute();
                    $count=$select->rowCount();
                    if($count==1){
                        $row=$select->fetch();
                        $id_user=$row['id_user'];
                        $_SESSION['id_user']=$id_user;
                        setcookie('id_user',$row['id_user'],time()+ 60*60);
                        $username=$row['username'];
                        $_SESSION['username']=$username;
                        setcookie('username',$row['username'],time()+ 60*60);
                        return true;
                    }else{
                        $error_msg='To enter, you must enter a valid username and password';
                        $_SESSION['error_msg']=$error_msg;
                    }

                }else{
                    $error_msg='Name or password is not correct';
                    $_SESSION['error_msg']=$error_msg;
                }
        }
    }

    /**
     * Выход зарегестрированного пользователя
     */
    public function  LogOut(){
        session_start();
        if(isset($_SESSION['id_user'])){
            $_SESSION=array();
            if(isset($_COOKIE[session_name()])){
                setcookie(session_name(),'',time() - 3600);
            }
            session_destroy();
        }
        setcookie('username','$username',time() -3600);
        setcookie('id_user','$id_user',time() -3600);
        $home_url='http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/index.php';
        header('Location:'.$home_url);
    }
}

class Validation {
    /**
     * @param $username
     * @return bool|int
     */
    public function validName($username){
        if (!empty($username)) {
            return preg_match('/^([a-zA-Z0-9]{3,}\s?).*/',$username);

        }else {
            $_SESSION['name_error'] = '|name error|';
            return false;
        }
    }

    /**
     * @param $comment
     * @return bool
     */
    public function validComment($comment){
        if (!empty($comment)){
            if (strlen($comment)<50){
                $_SESSION['comment_error']='|ERROR:entered text contains less than 50 characters|';
                return false;
            }else{
                return true;}
        }else{
            $_SESSION['comment_error']='|enter your comment|';
            return false;
        }
    }

    /**
     * @param $captcha
     * @return bool
     */
    public  function validCaptcha($captcha){
        if ($_SESSION['pass_phrase']!==$captcha){
            $_SESSION['captcha_error']='|Enter the word verification|';
            return false;
        }else{
            return true;}
    }
}

class Profile extends ConnectDB {
    public function viewProfile($id_user){
        $select = $this->DBH->prepare(
           "SELECT * FROM vdn_forum.vdn_profiles
            join vdn_forum.vdn_status
            ON id_user=$id_user and vdn_profiles.id_status=vdn_status.id_status");
        $select->execute();
        $row=$select->fetch();
        $arr_var=array();
        $arr_var[]=$row['last_name'];
        $arr_var[]=$row['first_name'];
        $arr_var[]=$row['gender'];
        $arr_var[]=$row['date_birth'];
        //$arr[]=$row['photo'];
        //$arr_var[]=$row['status_name'];
        return $arr_var;

    }
    public  function editProfile(){

    }
}
//$obj= new Profile();
//var_dump($obj->viewProfile(2));

class CreateForm
{
    /**
     * @param $name
     * @return string
     */
    public function getTPL($name){
        $tpl = "";
        $fileName = 'tpl' . DIRECTORY_SEPARATOR . $name . '.html';
        if (file_exists($fileName)) {
            $tpl = file_get_contents($fileName);
        }
        return $tpl;
    }
    public  function arrayLabels(){
        $arr_lab=array();
        $arr_lab[]='{{LASTNAME}}';
        $arr_lab[]='{{FIRSTNAME}}';
        $arr_lab[]='{{GENDER}}';
        $arr_lab[]='{{DATE_BIRTH}}';

        return $arr_lab;
    }
}
//$obj= new CreateForm();
//var_dump($obj->arrayLabels());

class ProcessTPL{
      public function processTemplace(array $arr_lab,array $arr_var,$tpl){

        $tpl = str_replace("$arr_lab", "$arr_var", "$tpl");

        return $tpl;
    }
}
<?php
class Auth
{
    private $con;
    public function __construct()
    {
        require_once dirname(__FILE__) . "/includes/DbConnect.php";
        $db = new DbConnect();
        $this->con = $db->connect();
    }

    public function login($email, $pass)
    {
        $password = md5($pass);
        $stmt = $this->con->prepare("SELECT * FROM `users` WHERE `email`=? AND `password`=?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0; // true or false
    }


    // creates new user with email id and password
    public function createUser($name, $email, $pass, $role = 1)
    {
        if ($this->isUserExists($email)) {
            return -1;  //user already exixts
        } else {
            $password = md5($pass);
            $stmt = $this->con->prepare("INSERT INTO `users` (`name`,`email`, `password`, `role`) VALUES (?,?,?,?);");
            $stmt->bind_param("sssi", $name, $email, $password, $role);
            if ($stmt->execute()) {
                return 1;  //user created successfully
            } else {
                return 0; //something went wrong
            }
        }
    }
    public function createUserWithContactNo($contact_no,$is_active=1,$role=1){
        if ($this->isUserExistsWithContactNo($contact_no)) {
            return -1;  //user already exixts
        } else {
            // $password = md5($pass);
            $stmt = $this->con->prepare("INSERT INTO `users` ( `contact_no`,`is_active`, `role`) VALUES (?,?,?);");
            $stmt->bind_param("sii", $contact_no,$is_active,$role);
            if ($stmt->execute()) {
                return 1;  //user created successfully
            } else {
                return 0; //something went wrong
            }
        }
    }
    public function isUserExistsWithContactNo($contact_no)
    {
        $stmt = $this->con->prepare("SELECT * FROM `users` WHERE `contact_no`=? ");
        $stmt->bind_param("s", $contact_no);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
    public function createCompany($name, $domain, $logo, $id_proof, $user_id, $contact_no, $address, $city, $state, $zip, $linkedin_profile, $insta_profile, $website, $description)
    {
        $sql = "INSERT INTO `company` (`name`, `domain`, `logo`, `id_proof`, `user_id`, `contact_no`, `address`, `city`, `state`, `zip`, `linkedin_profile`, `insta_profile`, `website`, `description`) VALUES ('$name', '$domain', '$logo[name]', '$id_proof[name]', $user_id, '$contact_no', '$address', '$city' ,'$state', '$zip', '$linkedin_profile', '$insta_profile', '$website', '$description');
            ";
        $status = mysqli_query($this->con, $sql);
        if ($status) {
            if (mkdir("./uploaded/" . $user_id)) {
                move_uploaded_file($logo['tmp_name'], "./uploaded/" . $user_id . "/$logo[name]");
                move_uploaded_file($id_proof['tmp_name'], "./uploaded/" . $user_id . "/$id_proof[name]");
            }
        } else {
            // echo "not inserted";
        }
        return $status;
    }


    // helper methods....

    public function getUser($email)
    {
        // $co=mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
        $result = mysqli_query($this->con, "SELECT * FROM `candidate` WHERE `email`='$email'");
        // $num=mysqli_num_rows($result);
        $row = mysqli_fetch_array($result);
        return $row;
    }
    public function getUserDetails($email)
    {
        // $co=mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
        $result = mysqli_query($this->con, "SELECT * FROM `users` WHERE `email`='$email'");
        // $num=mysqli_num_rows($result);
        $row = mysqli_fetch_array($result);
        return $row;
    }
    public function getUserDetailsById($user_id)
    {
        // $co=mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
        $result = mysqli_query($this->con, "SELECT * FROM `users` WHERE `user_id`='$user_id'");
        // $num=mysqli_num_rows($result);
        $row = mysqli_fetch_array($result);
        return $row;
    }
    public function getUserDetailsByContact($contact_no)
    {
        // $co=mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
        $result = mysqli_query($this->con, "SELECT user_id FROM `users` WHERE `contact_no`='$contact_no'");
        // $num=mysqli_num_rows($result);
        if($result){
            $row = mysqli_fetch_array($result);
            return $row;
        }else{
            return false;
        }

    }
    public function isUserExists($email)
    {
        $stmt = $this->con->prepare("SELECT * FROM `users` WHERE `email`=? ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
    public function getNoOfAllUsers(){
        $sql="SELECT * FROM `users`";
        $result =mysqli_query($this->con,$sql);
        return $result->num_rows;
    }
    public function getNoOfRecruiters(){
        $sql="SELECT * FROM `users` WHERE `role`=2";
        $result =mysqli_query($this->con,$sql);
        return $result->num_rows;
    }
    public function getNoOfCandidates(){
        $sql="SELECT * FROM `users` WHERE `role`=1";
        $result =mysqli_query($this->con,$sql);
        return $result->num_rows;
    }
    public function changeState($user_id,$state){
        $sql="UPDATE `users` SET `is_active` = '$state' WHERE `users`.`user_id` = $user_id;";
        $status=mysqli_query($this->con,$sql);
        if($status>0){
            return true;
        }else{
            return false;
        }
    }
    public function getAllUsers(){
        $sql="SELECT * FROM `users`";
        $result=mysqli_query($this->con,$sql);
        if($result->num_rows>0){
            return $result;
        }else{
            return false;
        }
    }
    public function getAllUsersWithPagination($startLimit,$recordPerPage){
        $sql="SELECT * FROM `users` ORDER BY updated_at DESC LIMIT $startLimit,$recordPerPage";
        $result=mysqli_query($this->con,$sql);
        if($result->num_rows>0){
            return $result;
        }else{
            return false;
        }
    }
    public function deleteUser($user_id){
        $sql="DELETE FROM `users` WHERE `user_id`=$user_id";
        $status=mysqli_query($this->con,$sql);
        if($status>0)
            return true;
        else
            return false;
    }
    public function resetPassword($email,$password){
        $pass=md5($password);
        $sql="UPDATE  `users` SET `password`='$pass' WHERE `email`='$email'";
        $status=mysqli_query($this->con,$sql);
        if($status>0)
            return true;
        else
            return false;
    }
}

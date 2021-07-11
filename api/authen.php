<?php

session_start();


 header("Access-Control-Allow-Origin:*");
 header("Access-Control-Allow-Headers:*");
require_once ('../db/dbhelper.php');
require_once ('../utils/utility.php');

// $action = $_POST['action'];

$postData = json_decode(file_get_contents("php://input"), true);

// var_dump($postData);
$action = $postData['action'];

switch ($action) {
	case 'login':
		doLogin($postData);
		break;
	case 'logout':
		doLogout($postData);
		break;
	case 'register':
		doRegister($postData);
		break;
	case 'list':
		doUserList($postData);
		break;
    case 'edit':
        doEdit($postData);
         break;
    case 'update':
        doUpdate($postData);
         break;
    case 'delete':
         doDelete($postData);
          break;
}

function doLogout() {
	$token = getCOOKIE('token');
	if (empty($token)) {
		$res = [
			"status" => 1,
			"msg"    => "Logout success!!!"
		];
		echo json_encode($res);
		return;
	}

	// Xoa token khoi database
	$sql = "delete from login_tokens where token = '$token'";
	execute($sql);

	// Xoa token khoi cookie
	setcookie('token', '', time()-7*24*60*60, '/');

	$res = [
		"status" => 1,
		"msg"    => "Logout success!!!"
	];
	echo json_encode($res);

	session_destroy();
}

function doLogin($postData) {
	$email    = $postData['email'];
	$password = $postData['password'];

	$password = md5Security($password);

	$sql  = "select * from users where email = '$email' and password = '$password'";
	$user = executeResult($sql, true);
    if (!empty($user)){
     $role=$user['role'];
    $_SESSION['role']=$role;
    $_SESSION['id']=$user['id'];}
    
	if ($user != null) {
		$email = $user['email'];
		$id    = $user['id'];

		$token = md5Security($email.time().$id);

		// setcookie('status', 'login', time()+7*24*60*60, '/');
		setcookie('token', $token, time()+7*24*60*60, '/');

		// save database
		$sql = "insert into login_tokens (id_user, token) values ('$id', '$token')";
		execute($sql);

		$res = [
			"id"=>$id,
			"token"=>$token,
            "role"=>$_SESSION['role'],
			"status" => 1,
			"msg"    => "Login success!!!"
		];
	} else {
		$res = [
			"status" => -1,
			"msg"    => "Login failed!!!"
		];
	}
	echo json_encode($res);
}

function doRegister($postData) {
	$username = $postData['username'];
	$fullname = $postData['fullname'];
	$email    = $postData['email'];
	$password = $postData['password'];
	$address  = $postData['address'];

	$sql    = "select * from users where username = '$username' or email = '$email'";
	$result = executeResult($sql);
	if ($result == null || count($result) == 0) {
		$password = md5Security($password);

		$sql = "insert into users(fullname, username, email, password, address,role) values ('$fullname', '$username', '$email', '$password', '$address','employee')";
		execute($sql);

		$res = [
			"status" => 1,
			"msg"    => "Create new account success!!!"
		];
	} else {
		$res = [
			"status" => -1,
			"msg"    => "Email|Username existed!!!"
		];
	}
	echo json_encode($res);
}

function doUserList() {
	// $user = authenToken();
	// // var_dump($user);
	// // die();
	// if ($user == null) {
	// 	$res = [
	// 		"status"   => -1,
	// 		"msg"      => "Not login!!!",
	// 		"userList" => []
	// 	];
	// 	echo json_encode($res);
	// 	return;
	// }

	$sql    = "select * from users";
	$result = executeResult($sql);
	$res    = [
        //"session" =>$_SESSION['role'],
		"status"   => 1,
		"msg"      => "success!!!",
		"userList" => $result
	];
	echo json_encode($res);
}
function doEdit($postData){
   
    $id=$postData["id"];
    $sql="select * from users where id='$id'";
    $result=executeResult($sql,true);
	//var_dump($result);
    $res=[
		"result"=>$result,
        "status"=>1,
        "msg"=>"success",
    ];
     
    echo json_encode($res);
}
function doUpdate($postData) {
    $id=$postData["id"];
	$username = $postData['username'];
	$fullname = $postData['fullname'];
	$email    = $postData['email'];
	$password = $postData['password'];
	$address  = $postData['address'];

	$sql    = "select * from users where username = '$username' or email = '$email'";
	$result = executeResult($sql);
	if ($result == null || count($result) == 0 || $result['id']==$id) {
		$password = md5Security($password);

		$sql = "update users set fullname='$fullname',username='$username',email='$email',address='$address',password='$password' where id='$id'";
		execute($sql);

		$res = [
			"status" => 1,
			"msg"    => "Update account success!!!"
		];
	} else {
		$res = [
			"status" => -1,
			"msg"    => "Email|Username existed!!!"
		];
	}
	echo json_encode($res);
}
function doDelete(){
    $id=$_GET["id"];
    $sql="delete from users where id='$id'";
    execute($sql);
    $res=[
        "id"=>$id,
        "status"=>1,
        "msg"=>"delete acount success"
];

echo json_encode($res);
}

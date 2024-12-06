

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>


<style class="cp-pen-styles">

html { width: 100%; height:100%; overflow:hidden; }

body { 
	width: 100%;
	height:100%;
	font-family: 'Open Sans', sans-serif;
	background: #092756;
	background: -moz-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%),-moz-linear-gradient(top,  rgba(57,173,219,.25) 0%, rgba(42,60,87,.4) 100%), -moz-linear-gradient(-45deg,  #670d10 0%, #092756 100%);
	background: -webkit-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -webkit-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -webkit-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
	background: -o-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -o-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -o-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
	background: -ms-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -ms-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -ms-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
	background: -webkit-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), linear-gradient(to bottom,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), linear-gradient(135deg,  #670d10 0%,#092756 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#3E1D6D', endColorstr='#092756',GradientType=1 );
}
.login { 
	position: absolute;
	top: 50%;
	left: 50%;
	margin: -150px 0 0 -150px;
	width:300px;
	height:300px;
}
.login h1 { color: #fff; text-shadow: 0 0 10px rgba(0,0,0,0.3); letter-spacing:1px; text-align:center; }

input { 
	width: 100%; 
	margin-bottom: 10px; 
	background: rgba(0,0,0,0.3);
	border: none;
	outline: none;
	padding: 10px;
	font-size: 13px;
	color: #fff;
	text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
	border: 1px solid rgba(0,0,0,0.3);
	border-radius: 4px;
	box-shadow: inset 0 -5px 45px rgba(100,100,100,0.2), 0 1px 1px rgba(255,255,255,0.2);
	-webkit-transition: box-shadow .5s ease;
	-moz-transition: box-shadow .5s ease;
	-o-transition: box-shadow .5s ease;
	-ms-transition: box-shadow .5s ease;
	transition: box-shadow .5s ease;
}
input:focus { box-shadow: inset 0 -5px 45px rgba(100,100,100,0.4), 0 1px 1px rgba(255,255,255,0.2); }
</style>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<div class="login">
	<h1>Login</h1>
    <form method="POST" action="">
    	<input type="text" name="username" placeholder="Username" required="required" id="username"/>
        <input type="password" name="password" placeholder="Password" required="required" id="password"/>
        <button type="submit" name="login" class="btn btn-primary btn-block btn-large">Login</button>
    
    </form>
   Don't have an account? <a href="register.php">Sign up</a>
  
</div>


<?php
//session_start(); // بدء الجلسة

// تفعيل عرض الأخطاء للتطوير
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ملف قاعدة بيانات المستخدمين
$dbFile = 'users.db';

// إنشاء أو فتح قاعدة بيانات SQLite
$db = new SQLite3($dbFile);

// التحقق من طلب POST لتسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تنظيف المدخلات لتجنب الهجمات (مثل SQL Injection)
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    // التحقق من صحة اسم المستخدم وكلمة المرور
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();

    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // إذا كانت البيانات صحيحة، تخزين اسم المستخدم في الجلسة

        // إنشاء كود مميز (Token) وتخزينه في قاعدة البيانات
        $token = bin2hex(random_bytes(32)); // إنشاء كود عشوائي
        $stmt = $db->prepare("UPDATE users SET token = :token WHERE username = :username");
        $stmt->bindValue(':token', $token, SQLITE3_TEXT);
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->execute();


        echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'نجاح',
                        text: 'تم تسجيل الدخول بنجاح!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            localStorage.setItem('authToken', '$token');
                            window.location.href = 'users/$username/index.php';
                        }
                    });
                </script>";
        
        exit();
    } else {
        // إذا كانت بيانات الدخول غير صحيحة
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'اسم المستخدم أو كلمة المرور غير صحيحة.'
                });
            </script>";
    }
}
?>


</body>
</html>
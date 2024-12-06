<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // تمكين الإبلاغ عن الأخطاء للتطوير
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // إنشاء/فتح قاعدة بيانات SQLite
    $db = new SQLite3('users.db');

    // إنشاء جدول للمستخدمين إذا لم يكن موجودًا
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL,
        token TEXT NOT NULL
    )");

    // التحقق من وجود المدخلات المطلوبة
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // تنظيف المدخلات لحمايتها من الهجمات مثل XSS و SQL Injection
        $username = htmlspecialchars(trim($_POST['username']));
        $password = htmlspecialchars(trim($_POST['password']));

        // التحقق من أن المدخلات ليست فارغة
        if (empty($username) || empty($password)) {
            die("الرجاء إدخال اسم المستخدم وكلمة المرور.");
        }

        // التحقق مما إذا كان اسم المستخدم موجودًا مسبقًا في قاعدة البيانات
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($result->fetchArray()) {
            die("اسم المستخدم موجود بالفعل.");
        }

        // توليد كود مميز للمستخدم
        $userToken = bin2hex(random_bytes(16)); // توليد رمز فريد

        // إدخال المستخدم الجديد في قاعدة البيانات
        $stmt = $db->prepare("INSERT INTO users (username, password, token) VALUES (:username, :password, :token)");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT); // تشفير كلمة المرور
        $stmt->bindValue(':token', $userToken, SQLITE3_TEXT); // تخزين الكود المميز
        $stmt->execute();

        // إنشاء مجلد باسم المستخدم
        $userDir = 'users/' . $username;
        if (!file_exists($userDir)) {
            mkdir($userDir, 0777, true);
        }

        // فك ضغط ملف ZIP في المجلد الذي تم إنشاؤه
        $zipFile = 'إريا.zip'; // مسار ملف ZIP
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($userDir); // فك الضغط في المجلد
            $zip->close();
        } else {
            die("حدث خطأ أثناء فك الضغط.");
        }

        // تخزين الرمز المميز في المتصفح
        echo "<script>
            localStorage.setItem('userToken', '$userToken');
            window.location.href = '/$userDir/';
        </script>";
        
        exit();
    } else {
        echo "الرجاء إدخال اسم المستخدم وكلمة المرور.";
    }

    // إغلاق الاتصال بقاعدة البيانات
    $db->close();
}
?>

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
	<h1>Register Account</h1></h1>
    <form method="POST" action="">
    	<input type="text" name="username" placeholder="Username" required="required" id="username"/>
        <input type="password" name="password" placeholder="Password" required="required" id="password"/>
        <button type="submit" name="login" class="btn btn-primary btn-block btn-large">Sign up</button>
      I already have an account. <a href="index.php">login </a>
    </form>
</div>





</body>
</html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Admin interface</title>
</head>

<body>
<center>
<form name="f1" action="admin.php">
Логин : <input type="Text" name="login"><br>
Пароль : <input type="Password" name="password"><br>
<input type="Submit" name="go1"></form>
<?php
if(isset($go1)) {
$fp1 = "inf.txt";
$inf = file($fp1);
if($login != rtrim($inf[0]) || $password != rtrim($inf[1])) {print "<font color='#ff0000' size='5'>Неверные данные!</font>"; exit;}

print '<hr><form name="f2" action="admin.php">
<input type="Submit" name="go2" value="Добавить новость">
</form>
';
}

if(isset($go2)) {
print '<hr>
<form action="admin.php" name="f3">
Новость : <input type="text" name="new" size="90"><br>
<input type="Submit" value="Добавить" name="go3">
</form>
';
}
if(isset($go3)) {
if($new == "" || $new == " ") {print "Вы ничего не ввели."; exit;}
$fp2 = "news.txt";
$fp2 = fopen($fp2, "a");
$time = time();
$str = "$time|"."$new"."\r\n";
fputs($fp2, $str);
fclose($fp2);
print "Новость добавлена.";
print '<hr><form name="f2" action="admin.php">
<input type="Submit" name="go2" value="Добавить новость">
</form>
<a href="index.php">На сайт.</a>
';
}



?>
</center>
</body>
</html>

<?
error_reporting(0);
require "settings.php";
print "<center>
<br><br><form>
<br>L:<input type=text name=login>
<br>P:<input type=password name=password>
<br><input type=submit name=a>
</form>";
if ($login != "$alogin" || $password != "$apass") {
print "<br><br><h1>Access Denied!</h1>";
exit;}
if(isset($a)) {
print "<title>AdMiN</title>
<center><h1>Admin.Interface</h1><br><br>
<br>
<hr size=1>";
print "
<form action=admin.php method=post>
<input type=hidden name=login value=$login>
<input type=hidden name=password value=$password>
<center><b>Добавить новость:</b><br><br>
<textarea cols=40 rows=5 size=100 name=text></textarea><br>
<input type=submit  value='             Добавить новость             ' name=c>
</form>
";}
if(isset($c)) {
$dat = date("d.m.Y");
print "<h1>Новость размещена!";
$tteexxtt = "$dat|$text";
$fp=fopen("news.txt", "a");
fputs($fp, "$tteexxtt\r\n");
fclose($fp);}
?>
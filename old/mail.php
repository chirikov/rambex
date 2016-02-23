<?php
include ("head.php");
?>
<font color="#0080c0"><h2>&nbsp;Rambex >> Почта</h2></font><br>
<center>
<table align=center border=0>
<tr>
<form method="post" action="mail.php">
<td align=right bgcolor=#ddddee><b>Логин:</b> <input type="Text" name="login"></td><td bgcolor=#ddddee><b>@</b></td><td bgcolor=#ddddee><select name=server>
<option value=mail.ru>MAIL.RU
<option value=narod.ru>NAROD.RU
<option value=mailru.com>MAILRU.COM
</select></td>
<td align=right bgcolor=#ddddee><b>Пароль:</b> <input type="Text" name="password"></td></tr>
<td align=center bgcolor=#ddddee colspan="2"><input type="Submit" name=go value="Проверить"><input type="Reset" value=" Очистить &nbsp;"></td></tr></table>
</form></center>
<?php
error_reporting(0);
if(isset($go)) {
$box = imap_open("{$server}", "$login", "$password") or die("&nbsp;&nbsp;Не могу подключиться к $server : ".imap_last_error());
$check = imap_mailboxmsginfo($box);
if($check) {
print "Date: ".$check->Date."<br>\n";
} else {
print "Ошибка : ".imap_last_error(). "<br>\n";
}}
?>
</td></tr></table>
</body>
</html>

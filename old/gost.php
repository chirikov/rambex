<?php
error_reporting(0);
include("head.php");
print "<font color=#0080c0><h2>&nbsp;Rambex >> Гостевая книга</h2></font><h3>&nbsp;Записи в книге:</h3><blockquote>";
$fp1 = "gost.txt";
$fp = fopen($fp1, "r");
$data = file($fp1);
fclose($fp);
foreach($data as $d) {
$t = explode("|", $d);
print "<b><i>$t[0]</i><br>$t[1]<br>Запись:</b> $t[2]<br><br>";}
print "</blockquote><hr><center><h3>Добавьте свою запись:</h3><br><br>
<table  width=694 align=center border=0>
<tr><td align=right bgcolor=#ddddee width=50%>Ваше имя:</td>
<form method=post action=gost.php>
<td bgcolor=#ddddee width=50%><input type=Text name=name size=30></td></tr>
<td align=right bgcolor=#ddddee width=50%>Ваш E-mail:</td><td bgcolor=#ddddee width=50%><input type=Text name=email size=30> (не обязательно)</td></tr>
<td valign=top align=right bgcolor=#ddddee width=50%>Текст письма:</td><td bgcolor=#ddddee width=50%><textarea name=text cols=40 rows=7></textarea></td></tr>
<td align=center bgcolor=#ddddee colspan=2><input type=Submit name=add value=Отправить><input type=Reset value=&nbsp;Очистить&nbsp;&nbsp;></td></tr></table>
</form>
";
if(isset($add)) {
if(strlen($text)<2) {print "<b>Вы ввели слишком короткую запись. Пожалуйста, введите что-нибудь ещё.</b>"; break;}
if($email==true) {$name = "<a href='mailto:$email'>$name</a>";}
$dat = date("d.m.Y, H:i");
$text2 = "$dat|Добавил: <font color=#0000ff>$name</font>|$text\r\n";
$fp = "gost.txt";
$fp = fopen($fp, "a");
fputs($fp, $text2);
fclose($fp);
print "<h4>Ваша запись успешно добавлена. Вы увидите её при последующем обновлении страницы.</h4></center></td></tr></table></body></html>";
} //isset
?>
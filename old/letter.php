<?php
include ("head.php");
?>
<font color="#0080c0"><h2>&nbsp;Rambex >> ������ �������������</h2></font><br>
<center>
<table  width=560 align=center border=0>
<tr><td align=right bgcolor=#ddddee width=50%>���� ���:</td>
<form method="post" action="letter.php">
<td bgcolor=#ddddee width=50%><input type="Text" name="from" size="30"></td></tr>
<td align=right bgcolor=#ddddee width=50%>��� E-mail:</td><td bgcolor=#ddddee width=50%><input type="Text" name="email" size="30"></td></tr>
<td align=right bgcolor=#ddddee width=50%>���� ������:</td><td bgcolor=#ddddee width=50%><input type="Text" name="subject" size="30"></td></tr>
<td valign="top" align=right bgcolor=#ddddee width=50%>����� ������:</td><td bgcolor=#ddddee width=50%><textarea name="message0" cols="40" rows="7"></textarea></td></tr>
<td align=right bgcolor=#ddddee><input type="Submit" name=go value="���������"></td><td align=left bgcolor=#ddddee width=50%><input type="Reset" value=" �������� &nbsp;"></td></tr></table>
</form></center>
<?php
error_reporting(0);
if (isset($go)) {
$to = "sokrat1988@mail.ru";
$message = "
������ ��� ������������� ����� Rambex.\n
�� $from\n
E-mail: <a href=mailto:$email>$email</a>\n
����: $subject\n
����� ��������� : \n
$message0";
mail($to, $subject, $message ) or print "<center><h2>�� ���������� ��������� ������.</h2></center>";
}
?>
</td></tr></table>
</body>
</html>

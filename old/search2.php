<?php
error_reporting(0);
include ("head.php");
print "<br><br><blockquote><font face=Verdana>
<form action=search.php method=post><b>��� ������ : </b><input type=text name=word value=$word>
<input type=submit name=go value=������>
</form>";
if(isset($go)) {
$word = htmlspecialchars($word);
$nosearch = array("gif", "allnews", "news", "mail", "admin", "letter", "head", "search", "rambexwebs", "rambexsites", "settings");
$s = strlen($word);
if($s < 3) {
if($s == 0) {$x = " ������ �� �����.";}
if($s > 0) {$x = " ����� ������ $s.";}
print "�������� ����� ������ ��������� <i>�� ������ 3-� ��������</i>. ��$x<br>"; exit;}
print "
<ul>";
$base = "D:/server/www/rambex";
$bas = opendir($base);
$results = array();
while ($file= readdir($bas)) {
foreach($nosearch as $no) {
if(strstr(basename($file), $no)) {$file = readdir($bas);}}
$strings = @file($file);
foreach ($strings as $str) {
$str = strip_tags($str);
$str = htmlspecialchars($str);
$str2 = strtolower($str);
$word2 = strtolower($word);
if(eregi($word2, $str2)) {
$n++;
$word3 = ucfirst($word);
$wordb = "<font color=#0000cc><b>$word</b></font>";
$wordb2 = "<font color=#0000cc><b>$word3</b></font>";
$str = str_replace($word2, $wordb, $str);
$str = str_replace($word3, $wordb2, $str);
$str = $str."...";
$re =  "
<li><a href=$file>$str</a><br><br>";
$results[] = $re;
}//eregi
}//foreach
}//while
$n = count($results);
if ($n == 0) {
print "<H3>���������� ������ �� ����� Rambex.<br><br>�� ������ ���������� �� �������.</h3>\n";
exit;
} else {
$w = 0;
if ($n > 9 && $n < 100) {$w = 1;}
if ($n >99 && $n < 1000) {$w = 2;}
if (substr($n, $w, 1) == 1) { $e = "�:"; };
if (substr($n, $w, 1) > 1 && substr($n, $w, 1) < 5) { $e = "�:"; };
if (substr($n, $w, 1) > 4 || substr($n, $w, 1) == 0) { $e = "�:";};
if($n > 9 && $n < 21) {$e = "�:";}
}
print "<h3>���������� ������ �� ����� Rambex.<br><br></h3>����� ������� <font color=red><b>$n</b></font> ���������";
print "$e<br><br>";
$results = array_unique($results);
foreach($results as $r) {
print "$r";}
print "</ul>";
}//isset
?></font></blockquote>
</td></tr></table>
</body>
</html>
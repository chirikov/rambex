<?php
$nosearch = array();
#################################################################################################
#	������ : Rambex.SiteS ������ 1.1											#
#	����� : ����� ������� (sokrat1988@mail.ru)										#
# (�) ����� �������, 2003 �.	http://www.rambex.h10.ru									#
#															#
# �� ������ ����� ������� � ������ ����� ��������� � �������������� ��� ��� ������� ��� ������ 10 ����� �� �������.		#
# ���� � ��� ���� ������� ��� �����������, ����� ����������� � ������.							#
#															#
#################################################################################################
#  ������ ������� �� ����� ��� ������ ������ ������ �����, ����������							#
# �������� �����.													#
#															#
#	���������:													#
# 1. ���������� �����. ��������� ���� ���� � �����, ��� ������ ������������� �����.						#
# 2. �������� ����������� ��������� (�������� ����).									#
# 3. ���������� � ��������� �����!												#
# 4. ������� �� ������������ �� ����� www.rambex.h10.ru 									#
#															#
# � ������ � ���������� :													#
$yourname = "Rambex.h10"; # �������� ������ ����� (����� �������� ������).							#
$len = 3; # ����������� ����� ����� ��� ������ (����� �� 0 �� 20).								#
$slovo = "������"; # ������� �� ������ ������ ������.									#
$header = "head.php"; # ����� �������� (�������� : "header.html"). ����� �������� ������.						#
$footer = ""; # �������� �������� (�������� : "footer.html"). ����� �������� ������.						#
$color = "#355AC8"; # �������� �����, ������� ����� �������� � ����������� �����, �������� ��� ������.				#
$ncolor = "red"; # �������� �����, ������� ����� �������� ���������� �����������.						#
# � ������ �� ������ ������� �����, ������� ������ ����������� �� ������. �� ����� ���� ������� ������.			#
# ������ ���������� ����� ������ ����: "$nosearch[] = "������";", ��� ������ - ��� ��� ���������� �����.				#
# ��������, �� �� ������, ����� ����� ������������ � ����� admin.php. � ����� ������, �������� ������ : $nosearch[] = "admin"; .	#
# ���� ��� ���� ��� ������, ������� ��������� ����� � ������������ .gif, .jpg, .bmp.						#
$nosearch[] = ".gif";													#
$nosearch[] = ".jpg";													#
$nosearch[] = ".bmp";													#
# ����� ���������. ������. ������ �� ������!										#
#################################################################################################
$base = "./";
@include ("$header");
print "<br><br><blockquote><html><body><font face=Verdana>
<form action=search.php method=post>
<input type=text name='word' value='$word'>
<input type=submit name=go value=$slovo>
</form>";
if(isset($go)) {
$s = strlen($word);
if($s < $len) {
	if (substr($len, 0, 1) == 1) { $e = "-�� �������"; };
    if (substr($len, 0, 1) > 1 && substr($len, 0, 1) < 5) { $e = "-� ��������"; };
    if (substr($len, 0, 1) > 4) { $e = "-� ��������";};
	if($s == 0) {$x = " ������ �� �����.";}
	if($s > 0) {$x = " ����� ������ $s.";}
	print "�������� ����� ������ ��������� <i>�� ������ $len$e</i>. ��$x<br>";
	exit;
}
reset($nosearch);
$files = array();
print "<ul>";
$bas = opendir($base);
$results = array();
while ($file = readdir($bas)) {
	if($file != "." && $file != ".." && is_file($file)) {
		foreach($nosearch as $no){
			if(!strstr(basename($file), $no)){
				$files[] = basename($file);
			}
		}
	}
}
$files = array_unique($files);
foreach ($files as $file) {
	$strings = file($file);
	foreach ($strings as $str) {
		$str = strip_tags($str,"<b><i>");
		if(eregi($word, $str)) {
			$length = strlen($str);
			$str33 = strtolower($str);
			$word33 = strtolower($word);
			$pos = strpos($str33, $word33);
			$str1 = substr($str, 0, $pos);
			$str2 = substr($str, $pos, $s);
			$str3 = substr($str, $pos+$s, $length);
			$str2 = "<font color=$color><b>$str2</b></font>";
			$re =  "<li><a href=$file>".$str1.$str2.$str3."</a><br><br>";
			$results[] = $re;
		}
	}
}
$n = count($results);
if ($n == 0) {
	print "<H3>���������� ������ �� ����� $yourname.<br><br>�� ������ ���������� �� �������.</h3>\n";
	exit;
}
else {
	$w = 0;
	if ($n > 9 && $n < 100) {$w = 1;}
	if ($n >99 && $n < 1000) {$w = 2;}
	if (substr($n, $w, 1) == 1) { $e = "�:"; };
	if ($n > 10 && $n < 20) { $e = "�:";}
	else {
		if (substr($n, $w, 1) > 1 && substr($n, $w, 1) < 5) { $e = "�:"; }
		if (substr($n, $w, 1) > 4 || substr($n, $w, 1) == 0) { $e = "�:";}
	}
}
print "
<h3>���������� ������ �� ����� $yourname.</h3><br>����� ������� <font color=$ncolor><b>$n</b></font> ���������".$e."<br><br>";

foreach($results as $r) {print "$r";}
print "</ul>";
}//isset
@include ("$footer");
?></font></blockquote>
</td></tr></table>
</body>
</html>
<?php
include ("head.php");
?>
&nbsp;&nbsp;<b>бяе мнбнярх:</b><p>
<?
$n = "0";
$g = "0";
$fill="news.txt";
$test = file("$fill");
$size = sizeof($test);
$num = $size;
do {
$data = explode("|", $test[$num]);   
$data[1] = stripslashes($data[1]);
if ($data[0] != "") {
print "&nbsp;&nbsp;&nbsp;<img src=mes.gif><b><i>$data[0]</i></b> :: $data[1]<br>"; 
$data[3] = str_replace("\r\n", "", $data[3]);
print "<br>";
}
if ($g == "999") {
print "a"; $n=$size; }
$g++;
$num--;
$n++;
} while ($n-1 < "$size");
?>
</p>
</td>
</tr>
</table>
</body>
</html>
<?php
$fp = "news.txt";
$news = file($fp);
$n = count($news);
if($n < 1) print "<center><font color='#0080ff'><i>--Пока новостей нет--</i></font></center><br>";
else {
	rsort($news, SORT_NUMERIC);
	reset($news);
	print "<table width='100%'>";
	if($n < 5) $r = $n; else $r = 5;
	for($x=0; $x<$r; $x++) {
		$part = explode("|", $news[$x]);
		$date = date("d.m.Y", $part[0]);
		print "<tr><td valign='top'><font color='#0080ff'><i>$date</i> : </font></td><td>".$part[1]."</td></tr>";
	}
	if($n > 5) print "<tr><td align='right'><a href='showallnews.php'><font color='#0080ff'><i>Показать все новости</i></font></a></td></tr>";
	print "</table>";
}
?>
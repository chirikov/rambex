<?php
include ("head.php");
?>
<h2><font color="#0080C0">&nbsp;&nbsp;Поиск в Интернете:&nbsp;</font></h2>
<form method="get" action="rambexwebs.php">&nbsp;&nbsp;&nbsp;<b>Что искать:</b>&nbsp;<input type="Text" name="query" size="30">
&nbsp;<b>Через поисковую&nbsp;систему:</b>&nbsp;<SELECT NAME="site">
<OPTION VALUE="rambler">Rambler
<OPTION VALUE="yandex">Яndex
<OPTION VALUE="yahoo">Yahoo!    
<OPTION VALUE="excite">Excite
<OPTION VALUE="infoseek">Infoseek
<OPTION VALUE="lycos">Lycos
<OPTION VALUE="altavista">Alta Vista
<OPTION VALUE="webcrawler">Webcrawler 
</SELECT>
&nbsp;<Input Type="Image" Src="search.gif" alt="Начать поиск" Value="submit" BORDER=0  ALIGN=absmiddle></form>
<hr size="1">
&nbsp;&nbsp;&nbsp;<b>НОВОСТИ:</b><br><br><ul>
<?php include "news.php"; ?></ul>
<hr size="1">
&nbsp;<img src="./graphic_counter/index.php" width=88 height=31>
</td>
</tr>
</table>
</body>
</html>
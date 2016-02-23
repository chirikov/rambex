<?php
#################################################################################################
#	Скрипт : Rambex.Webs версия 1.0											#
#	Автор : Роман Чириков (sokrat1988@mail.ru)										#
# (с) Роман Чириков, 2003 г.	http://www.rambex.h10.ru									#
#															#
# Вы имеете право вносить в скрипт любые изменения и распространять его при условии что первые 10 строк не удалены.		#
# Если у Вас есть вопросы или предложения, смело обращайтесь к автору.							#
#															#
#################################################################################################
#  Скрипт загружает страницу одного из 8 сайтов с результатами поиска по введённому слову.					#
#															#
#	Установка:													#
# 1. Распакуйте архив. Поместите этот файл в папку на Вашем сервере, где выполняются						#
#    PHP скрипты.														#
# 2. В одну (или больше) Ваших страниц HTML-код фрмы ввода слова для поиска (между тегами <body> и </body>):			#
#	<form method="get" action="rambexwebs.php">&nbsp;&nbsp;<b>Что искать:</b>&nbsp;<input type="Text" name="query" size="30">	#
#	&nbsp;<b>Через поисковую систему:</b>&nbsp;<SELECT NAME="site">							#
#	<OPTION VALUE="rambler">Rambler											#
# 	<OPTION VALUE="yandex">Яndex											#
#	<OPTION VALUE="yahoo">Yahoo!    											#
#	<OPTION VALUE="excite">Excite											#
#	<OPTION VALUE="infoseek">Infoseek											#
#	<OPTION VALUE="lycos">Lycos											#
#	<OPTION VALUE="altavista">Alta Vista										#
#	<OPTION VALUE="webcrawler">Webcrawler 										#
#	</SELECT>													#
#	&nbsp;<Input Type="submit" value="Начать поиск"></form>								#
#															#
#     Если страница с кодом находится не в той же папке что и этот файл, то измените путь к этому файлу в строке "action="путь"".	#
# 3. Запускайте и радуйтесь жизни!												#
# 4. Следите за обновлениями на сайте www.rambex.h10.ru 									#
#	 Желаю удачного поиска!												#
#################################################################################################
if($site == "rambler") {
$l = "http://search.rambler.ru/cgi-bin/rambler_search?words=$query&where=1";
}
if ($site == "altavista") {
$l = "http://www.altavista.digital.com/cgi-bin/query\?pg=q\&what=web\&kl=XX\&q=$query";
}
if ($site == "excite") {
$l = "http://www.excite.com/search.gw?trace=a&search=$query";
}
if ($site == "infoseek") {
$l = "http://www2.infoseek.com/Titles?qt=$query&col=WW&sv=IS&lk=noframes&nh=10";
}
if ($site == "lycos") {
$l = "http://www.lycos.com/cgi-bin/pursuit?query=$query&matchmode=and&cat=lycos";
}
if ($site == "yahoo") {
$l = "http://search.yahoo.com/bin/search?p=$query";
}
if ($site == "webcrawler") {
$l = "http://webcrawler.com/cgi-bin/WebQuery?mode=compact&maxHits=25&searchText=$query";
}
if ($site == "yandex") {
$l = "http://www.yandex.ru/yandsearch?text=$query";
}
header ("Location: $l");
?>
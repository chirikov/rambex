<?php
#################################################################################################
#	������ : Rambex.Webs ������ 1.0											#
#	����� : ����� ������� (sokrat1988@mail.ru)										#
# (�) ����� �������, 2003 �.	http://www.rambex.h10.ru									#
#															#
# �� ������ ����� ������� � ������ ����� ��������� � �������������� ��� ��� ������� ��� ������ 10 ����� �� �������.		#
# ���� � ��� ���� ������� ��� �����������, ����� ����������� � ������.							#
#															#
#################################################################################################
#  ������ ��������� �������� ������ �� 8 ������ � ������������ ������ �� ��������� �����.					#
#															#
#	���������:													#
# 1. ���������� �����. ��������� ���� ���� � ����� �� ����� �������, ��� �����������						#
#    PHP �������.														#
# 2. � ���� (��� ������) ����� ������� HTML-��� ���� ����� ����� ��� ������ (����� ������ <body> � </body>):			#
#	<form method="get" action="rambexwebs.php">&nbsp;&nbsp;<b>��� ������:</b>&nbsp;<input type="Text" name="query" size="30">	#
#	&nbsp;<b>����� ��������� �������:</b>&nbsp;<SELECT NAME="site">							#
#	<OPTION VALUE="rambler">Rambler											#
# 	<OPTION VALUE="yandex">�ndex											#
#	<OPTION VALUE="yahoo">Yahoo!    											#
#	<OPTION VALUE="excite">Excite											#
#	<OPTION VALUE="infoseek">Infoseek											#
#	<OPTION VALUE="lycos">Lycos											#
#	<OPTION VALUE="altavista">Alta Vista										#
#	<OPTION VALUE="webcrawler">Webcrawler 										#
#	</SELECT>													#
#	&nbsp;<Input Type="submit" value="������ �����"></form>								#
#															#
#     ���� �������� � ����� ��������� �� � ��� �� ����� ��� � ���� ����, �� �������� ���� � ����� ����� � ������ "action="����"".	#
# 3. ���������� � ��������� �����!												#
# 4. ������� �� ������������ �� ����� www.rambex.h10.ru 									#
#	 ����� �������� ������!												#
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
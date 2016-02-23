<?php

#	.................................................................................
#
#		Скрипт:	Manlix Guestbook, версия: 1.4
#		Автор:	Manlix (http://manlix.ru)
#	.................................................................................

if(phpversion()<4.1) exit("<font face='verdana' size='1' color='#de0000'><b>Версия PHP интерпретатора должна быть 4.1.0 или выше, но никак не ниже (ваша версия интерпретатора: ".phpversion().")</b></font>");

function error($error,$file){exit('<font face="verdana" size="1" color="#de0000"><b>'.$error.'<br>['.htmlspecialchars($file).']</b></font>');}

function CheckConf($conf)
{
	while(list($section,$array)=each($conf))
		while(list($key,$value)=each($array))
			if(!strlen($value))
			error("В файле параметров скрипта, а именно в секции <font color=green>".$section."</font>, пуст ключ <font color=green>".$key."</font>",$conf['dir']['path']."/".$conf['dir']['inc']."/config.inc.dat");
}

if(!set_time_limit(0)) error("Откройте файл <font color=green>".__FILE__."</font> и удалите в нём <font color=green>".__LINE__."</font> строчку",date("Дата: d.m.Y. Время: H:i:s",time()));

if(isset($_GET))	while(list($key,$value)=each($_GET)) $$key=$value;

$manlix=null;

$manlix=parse_ini_file("./inc/config.inc.dat",1) or error("не могу загрузить основной файл конфигурации","./inc/config.inc.dat");

CheckConf($manlix);

if(!is_dir($manlix['dir']['path']."/".$manlix['dir']['inc']))							error("не найдена системная папка скрипта",$manlix['dir']['path']."/".$manlix['dir']['inc']);
if(!is_dir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']))				error("не найдена папка, в которой должны храниться все шаблоны скрипта",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']);
if(!is_dir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']))	error("не найдена папка, в которой должны находиться шаблонные файлы",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']);

if(!is_readable($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat"))	error("не могу загрузить специальный файл конфигурации для шаблона",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat");
else														$manlix=array_merge($manlix,parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat",1));

while(list(,$file)=each($manlix['templates']))
{
	if(file_exists($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/".$file))
	{
		if(!is_readable($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/".$file))
		error("не могу прочитать один из шаблонов /нет прав/",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/".$file);
	}

	else
	error("не найден один из шаблонов /файл не существует/",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/".$file);
}

if(!include($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['functions']))		error("не могу загрузить файл с функциями",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['functions']);
elseif(!include($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['interpreter']))	error("не могу загрузить файл с интерпретатором",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['interpreter']);

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if(!empty($manlix['closed']['closed']))
{
	if(!file_exists($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']))		error("не найден файл с базой сообщений",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);
	elseif(!is_readable($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']))		error("не могу прочитать базу с сообщениями, нет прав на чтение",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);
	else										$manlix['base']=array_reverse(manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']));

parse_template($manlix['templates']['top']);
parse_template($manlix['templates']['closed']);
	if(!empty($manlix['closed']['messages']))
	ShowMessages();
parse_template($manlix['templates']['bottom']);
exit;
}

if(!count($smiles=GetSmiles())) $manlix['other']['smiles']="смайликов нет";
else
{
$manlix['other']['smiles']=$ListSmiles=null;
$num=-1;
$manlix['other']['smiles'].="<table border=0>";

	while(list(,$array)=each($smiles))
	{
	$num++;
		if(!strstr(($num/$manlix['numeric']['show_smiles']),".")) $manlix['other']['smiles'].="</tr><tr>";
		$bgcolor=strstr(($num+1)/2,".")?$manlix['color']['uneven']:$manlix['color']['even'];
	$manlix['other']['smiles'].="<td bgcolor='".$bgcolor."' align=center style='cursor: hand' onclick='InsertSmile(\"$array[0]\")'>".$array[1]."</td>";
	}
$manlix['other']['smiles'].="</tr></table>";
}
	if(!$_POST)
	{
		if(isset($_COOKIE['manlix_guestbook_cookie_name']))
		$manlix['other']['cookie_name']=manlix_stripslashes($_COOKIE['manlix_guestbook_cookie_name']);
		else
		$manlix['other']['cookie_name']=null;

		if(isset($_COOKIE['manlix_guestbook_cookie_homepage']))
		$manlix['other']['cookie_homepage']=$_COOKIE['manlix_guestbook_cookie_homepage'];
		else
		$manlix['other']['cookie_homepage']=null;

		if(isset($_COOKIE['manlix_guestbook_cookie_mail']))
		$manlix['other']['cookie_mail']=$_COOKIE['manlix_guestbook_cookie_mail'];
		else
		$manlix['other']['cookie_mail']=null;

		if(isset($_COOKIE['manlix_guestbook_cookie_icq']))
		$manlix['other']['cookie_icq']=$_COOKIE['manlix_guestbook_cookie_icq'];
		else
		$manlix['other']['cookie_icq']=null;

		$manlix['other']['cookie_message']=null;
	}

	else
	{
	$manlix['other']['cookie_name']	=manlix_stripslashes($_POST['name']);
	$manlix['other']['cookie_homepage']	=manlix_stripslashes(eregi_replace("http://",null,$_POST['homepage']));
	$manlix['other']['cookie_mail']	=manlix_stripslashes($_POST['mail']);
	$manlix['other']['cookie_icq']		=manlix_stripslashes($_POST['icq']);
	$manlix['other']['cookie_message']	=manlix_stripslashes($_POST['message']);

		if(!empty($_COOKIE['manlix_guestbook_cookie_flood']))
		$_COOKIE['manlix_guestbook_cookie_flood']+=$manlix['numeric']['flood']*60;
		else
		$_COOKIE['manlix_guestbook_cookie_flood']=0;

		if(empty($_COOKIE['manlix_guestbook_cookie_message']))
		$_COOKIE['manlix_guestbook_cookie_message']=null;

		if($_COOKIE['manlix_guestbook_cookie_flood']>time())
		$manlix['other']['error']=sprintf($manlix['error']['flood'],$manlix['numeric']['flood']);

		elseif(!isset($_SERVER['REMOTE_ADDR']))
		$manlix['other']['error']=$manlix['error']['unknown_ip'];

		elseif(!$manlix['other']['cookie_name'])
		$manlix['other']['error']=$manlix['error']['empty_name'];

		elseif(strlen($manlix['other']['cookie_name'])<$manlix['numeric']['min_name'])
		$manlix['other']['error']=sprintf($manlix['error']['min_name'],$manlix['numeric']['min_name']);

		elseif(strlen($manlix['other']['cookie_name'])>$manlix['numeric']['max_name'])
		$manlix['other']['error']=sprintf($manlix['error']['max_name'],$manlix['numeric']['max_name']);

		elseif(!eregi("^((www)?)+(([a-z0-9_.-]+)?)[a-z0-9]+\.+[a-z]{2,4}$",$manlix['other']['cookie_homepage'])&&$manlix['other']['cookie_homepage'])
		$manlix['other']['error']=$manlix['error']['invalid_homepage'];

		elseif(!eregi("^[a-z0-9]+(([a-z0-9_.-]+)?)@[a-z0-9+](([a-z0-9_.-]+)?)+\.+[a-z]{2,4}$",$manlix['other']['cookie_mail'])&&$manlix['other']['cookie_mail'])
		$manlix['other']['error']=$manlix['error']['invalid_mail'];

		elseif(!is_numeric($manlix['other']['cookie_icq'])&&$manlix['other']['cookie_icq'])
		$manlix['other']['error']=$manlix['error']['invalid_icq'];

		elseif(strlen($manlix['other']['cookie_icq'])<$manlix['numeric']['min_icq']&&$manlix['other']['cookie_icq'])
		$manlix['other']['error']=sprintf($manlix['error']['min_icq'],$manlix['numeric']['min_icq']);

		elseif(!$manlix['other']['cookie_message'])
		$manlix['other']['error']=$manlix['error']['empty_message'];

		elseif(!CheckWords($manlix['other']['cookie_message']))
		$manlix['other']['error']=sprintf($manlix['error']['max_word'],$manlix['numeric']['max_word']);

		elseif($_COOKIE['manlix_guestbook_cookie_message']==$manlix['other']['cookie_message'])
		$manlix['other']['error']=$manlix['error']['try_flood'];

		elseif(strlen($manlix['other']['cookie_message'])>$manlix['numeric']['max_message'])
		$manlix['other']['error']=sprintf($manlix['error']['max_message'],$manlix['numeric']['max_message']);

		elseif(is_array($ban=Banlist($_SERVER['REMOTE_ADDR'])))
		$manlix['other']['error']=sprintf($manlix['error']['ban'],$ban[0],$ban[1]);

	$manlix['other']['cookie_name']	=htmlspecialchars($manlix['other']['cookie_name']);
	$manlix['other']['cookie_homepage']	=htmlspecialchars($manlix['other']['cookie_homepage']);
	$manlix['other']['cookie_mail']	=htmlspecialchars($manlix['other']['cookie_mail']);
	$manlix['other']['cookie_icq']		=htmlspecialchars($manlix['other']['cookie_icq']);
	$manlix['other']['cookie_message']	=htmlspecialchars($manlix['other']['cookie_message']);

		if(!isset($manlix['other']['error']))
		{
		setcookie("manlix_guestbook_cookie_name",		$manlix['other']['cookie_name'],	time()+60*60*24*365);
		setcookie("manlix_guestbook_cookie_homepage",	$manlix['other']['cookie_homepage'],	time()+60*60*24*365);
		setcookie("manlix_guestbook_cookie_mail",		$manlix['other']['cookie_mail'],	time()+60*60*24*365);
		setcookie("manlix_guestbook_cookie_icq",		$manlix['other']['cookie_icq'],	time()+60*60*24*365);
		setcookie("manlix_guestbook_cookie_message",		$manlix['other']['cookie_message'],	time()+60*60*24*365);
		setcookie("manlix_guestbook_cookie_flood",		time(),				time()+60*60*24*365);

		$AutochangeFile=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['autochange']);
		$array1=$array2=array();

			while(list(,$string)=each($AutochangeFile))
			{
			list($a,$b)=explode("::",$string);
			$array1[]=$a;
			$array2[]=$b;
			}

		$manlix['other']['cookie_message']=preg_replace($array1,$array2,$manlix['other']['cookie_message']);

		$open=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base'],"a");
		
		$manlix['other']['cookie_message'] = ereg_replace("ё","e",$manlix['other']['cookie_message']);
		
		fwrite($open,	time().".".manlix_char_generator("1234567890",32)."::".
				manlix_to_normal_string($manlix['other']['cookie_name'])."::".
				manlix_to_normal_string($manlix['other']['cookie_homepage'])."::".
				manlix_to_normal_string($manlix['other']['cookie_mail'])."::".
				$manlix['other']['cookie_icq']."::".
				manlix_to_normal_string($manlix['other']['cookie_message'])."::".
				$_SERVER['REMOTE_ADDR']."::::::::".
				chr(13).chr(10)
		);
		fclose($open);
		$manlix['other']['add']=1;
		}
	}


if(!file_exists($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']))		error("не найден файл с базой сообщений",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);
elseif(!is_readable($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']))		error("не могу прочитать базу с сообщениями, нет прав на чтение",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);
else										$manlix['base']=array_reverse(manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']));

parse_template($manlix['templates']['top']);

if(!isset($manlix['other']['add']))	parse_template($manlix['templates']['form']);
else				parse_template($manlix['templates']['okay']);

ShowMessages();

parse_template($manlix['templates']['bottom']);
?>
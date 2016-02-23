<?

#	.................................................................................
#
#		Скрипт:	Manlix Guestbook, версия: 1.4
#		Автор:	Manlix (http://manlix.ru)
#	.................................................................................

if(phpversion()<4.1) exit("<font face='verdana' size='1' color='#de0000'><b>Версия PHP интерпретатора должна быть 4.1.0 или выше, но никак не ниже (ваша версия интерпретатора: ".phpversion().")</b></font>");

function error($error,$file){exit('<font face="verdana" size="1" color="#de0000"><b>'.$error.'<br>['.htmlspecialchars($file).']</b></font>');}

if(!set_time_limit(0)) error("Откройте файл <font color=green>".__FILE__."</font> и удалите в нём <font color=green>".__LINE__."</font> строчку",date("Дата: d.m.Y. Время: H:i:s",time()));

if(isset($_GET))	while(list($key,$value)=each($_GET)) $$key=$value;

$manlix=null;

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

function CheckConf($conf)
{
	while(list($section,$array)=each($conf))
		while(list($key,$value)=each($array))
			if(!strlen($value))
			error("В файле параметров скрипта, а именно в секции <font color=green>".$section."</font>, пуст ключ <font color=green>".$key."</font>",$conf['dir']['path']."/".$conf['dir']['inc']."/config.inc.dat");
}

function read_dir($dir)
{
	$OpenDir=opendir($dir);

		while(($file=readdir($OpenDir))!==false)
		{
			if($file!="."&&$file!="..")
			{
				if(is_dir($dir."/".$file))
				{
					if(!is_readable($dir."/".$file))		error("нет прав для чтения текущий папки",$dir."/".$file);
					elseif(!is_writeable($dir."/".$file))	error("нет прав для записи в текущую папку",$dir."/".$file);
					else				read_dir($dir."/".$file);
				}

				else
				{
					if(!is_readable($dir."/".$file))		error("нет прав для чтения файла",$dir."/".$file);
					elseif(!is_writeable($dir."/".$file))	error("нет прав для записи в файл",$dir."/".$file);
				}
			}
		}
}

if(!is_readable("./inc"))		error("нет прав для чтения текущий папки","./inc");
elseif(!is_writeable("./inc"))		error("нет прав для записи в текущую папку","./inc");
else				read_dir("./inc");

$manlix=parse_ini_file("./inc/config.inc.dat",1);

CheckConf($manlix);

if(!include($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['functions']))	error("не могу загрузить файл с функциями",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['functions']);

$manlix['sections']=array(
		"сообщения"		=>	array("добавление, изменение, удаление ответов","редактирование сообщений","удаление сообщений"),
		"админ"			=>	array("добавление нового админа","изменение информации и прав доступа админа","удаление админа"),
		"дизайн"			=>	array("выбор шаблона","изменение шаблона и его параметров","создание шаблона"),
		"параметры"		=>	array("просмотр","изменение","просмотр собственных прав доступа","информация о скрипте"),
		"автозамена (антимат)"	=>	array("добавить","изменить","удалить"),
		"банлист"		=>	array("добавить","изменить","удалить")
		);

if(isset($_SERVER['QUERY_STRING'])&&$_SERVER['QUERY_STRING']=="exit")
{
$_COOKIE=null;
setcookie($manlix['script']['prefix']."password",null);
}

if(isset($section))
{
list($manlix['section']['select'][0],$manlix['section']['select'][1])=explode("_",$section);
$manlix['section']['current']=$section;
unset($section);
}

function admin($login,$password)
{
global $manlix;

	if(file_exists($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']))	$file=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);
	else									error("не найден файл с именами и паролями /файл не существует/",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);


	if(strlen($password)!=32)	$password=md5($password);

	while(list(,$string)=each($file))
	{
	list($admin_login,$admin_password,$admin_access)=explode("::",$string);

		if($admin_login==$login&&$admin_password==$password)
		{
		setcookie($manlix['script']['prefix']."login",$login);
		setcookie($manlix['script']['prefix']."password",$password);

		$array_admin_access=explode("|",$admin_access);

			for($i=0;$i<count($array_admin_access)-1;$i++)
			{
			$array_access_level=explode(",",$array_admin_access[$i]);

				for($q=0;$q<count($array_access_level);$q++)
				$manlix['access_level'][$i][]=$array_access_level[$q];
			}

			if(!isset($manlix['section']['current']))
			{
			$manlix['access_level']['status']=1;
			$manlix['status']="вход выполнен для пользователя <font color='blue'>".$login."</font>";
			}

			elseif(!empty($manlix['access_level'][$manlix['section']['select'][0]][$manlix['section']['select'][1]]))
			{
			$manlix['access_level']['status']=1;

			$temp_array=$manlix['sections'];
			$void=-1;
				while(list($name,$array)=each($temp_array))
				{
				$void++;
					if($manlix['section']['select'][0]==$void)
					{
					$manlix['status']="<a href='?' title='Вернуться в главное меню'><font color='#000000'>Главное меню</font></a> <font color='blue'>»</font> <font color='green'>".ucfirst($name)."</font>";
					$temp=-1;
						while(list(,$next)=each($array))
						{
						$temp++;
							if($manlix['section']['select'][1]==$temp)
							{
							$manlix['status'].=" <font color='blue'>»</font> <a href='?section=".$void."_".$temp."' title='Перезайти в раздел\"".ucfirst($next)."\"'><font color='#de0000'>".ucfirst($next)."</font></a>";
							break;
							}
						}
					}
				}
			$temp_array=null;
			}

			else
			{
			$manlix['status']="у вас нет прав для входа в этот раздел";
			$manlix['section']['current']="deny";
			}

		return 1;
		break;
		}
	}
}

if(isset($_POST['login'])&&isset($_POST['password']))
{
	if(isset($_POST))
	{
		if(strlen($_POST['password'])!=32)
		{
			if(isset($_POST['login']))
			$_COOKIE[$manlix['script']['prefix']."login"]=$_POST['login'];

			if(isset($_POST['password']))
			$_COOKIE[$manlix['script']['prefix']."password"]=$_POST['password'];
		}

		else $manlix['access']=0;
	}

	else
	{
	unset($_POST['login'],$_POST['password']);
	$manlix['status']="входить в управление гостевой книгой нужно, только через форму";
	}
}

if(isset($_COOKIE[$manlix['script']['prefix']."login"])&&isset($_COOKIE[$manlix['script']['prefix']."password"]))
{$manlix['access']=admin($_COOKIE[$manlix['script']['prefix']."login"],$_COOKIE[$manlix['script']['prefix']."password"])?1:0;}

if(empty($manlix['access']))
{
	if(isset($_POST['login'])||isset($_POST['password']))
	{
		$manlix['status']="ваши имя и пароль не опознаны";

		if(isset($_POST['password'])&&!$_POST['password'])	$manlix['status']="вы не ввели свой пароль, введите свои данные ещё раз";
		if(isset($_POST['login'])&&!$_POST['login'])		$manlix['status']="вы не ввели своё имя, введите свои данные ещё раз";
		if(isset($_POST['login'])&&!isset($_POST['password']))	$manlix['status']="вы не ввели имя и пароль";
	}

$manlix['section']['name']="Вход в управление";
$manlix['result']='<form method=post><br>
<table border=0 align=center cellspacing=0 cellpadding=1>
<tr><td align=right><font face=verdana size=1 color=maroon>Ваше имя:</td>	<td><input type=text name=login size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px"></td></tr>
<tr><td align=right><font face=verdana size=1 color=maroon>Пароль:</td>	<td><input type=password name=password size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px"></td></tr>
<tr><td height=10></td></tr>
<tr><td align=right colspan=2>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000>
				<tr><td><input type=submit value="Выполнить вход" class=submit style="width: 163px"></td></tr>
				</table>
</td></tr>
<tr><td height=20></td></tr>
</form>
</table>';
}

else
{
$manlix['section']['current']=(isset($manlix['section']['current']))?$manlix['section']['current']:null;
$manlix['result']=(isset($manlix['result']))?$manlix['result']:null;

	switch($manlix['section']['current'])
	{
	case "0_0":	$manlix['section']['name']="Выберите нужное сообщения";
			$manlix['base']=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);

			if(!count($manlix['base']))
			{
			$manlix['result']=<<<HTML
			<center><font face=verdana size=1 color=maroon><i><br>гостевая книга пуста<br><br><br><br></i><a href="?">...вернуться назад</a></font><br><br></center>
HTML;
			}

			elseif(empty($MessageId))
			{
			$manlix['other']['navigation']=manlix_array_navigation(
								(isset($manlix['base']))?array_reverse($manlix['base']):null,
								(isset($manlix['numeric']['show_messages']))?$manlix['numeric']['show_messages']:null,
								(isset($manlix['numeric']['show_pages']))?$manlix['numeric']['show_pages']:null,
								"?section=0_0&guestbook_page=",
								(isset($guestbook_page))?$guestbook_page:null,
								(isset($manlix['symbol']['left']))?$manlix['symbol']['left']:null,
								(isset($manlix['symbol']['right']))?$manlix['symbol']['right']:null,
								(isset($manlix['color']['not_active_symbol']))?$manlix['color']['not_active_symbol']:null,
								(isset($manlix['color']['not_current_page']))?$manlix['color']['not_current_page']:null,
								(isset($manlix['color']['current_page']))?$manlix['color']['current_page']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['symbol']['separator_between_pages']))?$manlix['symbol']['separator_between_pages']:null
								);

			$manlix['strings']=$manlix_array_navigation;

				while(list($number,$string)=each($manlix['strings']['result_strings']))
				{
				$manlix['other']['num']=$manlix['strings']['count_all_strings']-$manlix['strings']['start_string']-$number;
				list(	$manlix['other']['time'],
					$manlix['other']['name'],
					$manlix['other']['homepage'],
					$manlix['other']['mail'],
					$manlix['other']['icq'],
					$manlix['other']['message'],
					$manlix['other']['ip'],
					$manlix['other']['author'],
					$manlix['other']['answer'],
					$manlix['other']['answerTime'],
				)=explode("::",$string);

				if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['message'],2))==2)
				$manlix['other']['message']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['message']);

				if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['answer'],2))==2)
				$manlix['other']['answer']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['answer']);

			$manlix['result'].="<table border=0><tr><td></td></tr></table>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>·</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
			<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
			<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."<br>Сообщение № ".$manlix['other']['num']."</font></i></div>
			".$manlix['other']['message']."
			<div align=right>";

			if(!empty($manlix['other']['homepage']))
			$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='Домашняя страничка'></a>";

			if(!empty($manlix['other']['mail']))
			$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='Электронная почта'></a>";

			if(!empty($manlix['other']['icq']))
			$manlix['result'].="<img src='images/icq.gif' border=0 alt='Аська: ".$manlix['other']['icq']."'>";

			$manlix['result'].="</div>
			</font></td></tr>";

			if(!empty($manlix['other']['answer']))
			$manlix['result'].="<tr bgcolor=#faedc0><td><i><font face=verdana size=1><font color=#de0000><b>УЖЕ ЕСТЬ ОТВЕТ</b> (<a href='?section=0_0&MessageId=".$manlix['other']['time']."'>изменить ответ</a>) (<a href='?section=0_0&MessageId=".$manlix['other']['time']."&delete=1'>удалить ответ</a>)</font><br><b>·</b></i> <font color=maroon>".$manlix['other']['author']."</font>: <font color=#000000>".$manlix['other']['answer']."</font><i><div align=right>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</div></i></font></td></tr>";
			else
			$manlix['result'].="<tr bgcolor=#faedc0><td><i><font face=verdana size=1><font color=green><b>НЕТ ОТВЕТА</b></font> (<a href='?section=0_0&MessageId=".$manlix['other']['time']."'>добавить ответ</a>)</font></i></td></tr>";

			$manlix['result'].="</table>
			<table border=0><tr><td></td></tr></table>";
				}
			$manlix['result'].="
			<br>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr><td bgcolor=#faedc0 align=center><i><font face=verdana size=1>".$manlix['other']['navigation']."</font></i></td></tr>
			</table>
			<br>";
			}

			elseif(isset($_GET['delete']))
			{
				$OpenMessagesFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base'],"w");
				flock($OpenMessagesFile,1);
				flock($OpenMessagesFile,2);

					while(list(,$string)=each($manlix['base']))
					{
					list(	$manlix['other']['time'],
						$manlix['other']['name'],
						$manlix['other']['homepage'],
						$manlix['other']['mail'],
						$manlix['other']['icq'],
						$manlix['other']['message'],
						$manlix['other']['ip'],
						$manlix['other']['author'],
						$manlix['other']['answer'],
						$manlix['other']['answerTime'],
					)=explode("::",$string);

						if($_GET['MessageId']===$manlix['other']['time'])
						{
						fwrite($OpenMessagesFile,	$manlix['other']['time']."::".
									$manlix['other']['name']."::".
									$manlix['other']['homepage']."::".
									$manlix['other']['mail']."::".
									$manlix['other']['icq']."::".
									$manlix['other']['message']."::".
									$manlix['other']['ip']."::::::::".
									chr(13).chr(10)
						);

						$manlix['deleted']=$manlix['okay']=1;
						}

						else
						fwrite($OpenMessagesFile,$string);
					}

				fclose($OpenMessagesFile);

					if(!isset($manlix['deleted']))
					$manlix['result'].=<<<HTML
					<center><br><font face=verdana size=1>Выбранное Вами сообщение не существует<br><br><br><br><a href="?section=0_0">...вернуться на шаг назад</a><br></font><br></center>
HTML;

					else
					{
					$manlix['section']['name']="Ответ удалён";

					$manlix['result'].=<<<HTML
					<center><br><br><font face=verdana size=1 color=maroon>Ответ на сообщение успешно удалён<br><br><br>(<a href='?section=0_0'>показать сообщения</a>)</font><br><br><br></center>
HTML;
					}
			}

			else
			{
				if(!isset($_POST['NewAnswer']))
				{
					while(list(,$string)=each($manlix['base']))
					{
					list(	$manlix['other']['time'],
						$manlix['other']['name'],
						$manlix['other']['homepage'],
						$manlix['other']['mail'],
						$manlix['other']['icq'],
						$manlix['other']['message'],
						$manlix['other']['ip'],
						$manlix['other']['author'],
						$manlix['other']['answer'],
						$manlix['other']['answerTime'],
					)=explode("::",$string);

						if($_GET['MessageId']===$manlix['other']['time'])
						{
						$manlix['result'].=<<<HTML
<script language="javascript" type="text/javascript">
<!--
var ie=document.all?1:0;
var ns=document.getElementById&&!document.all?1:0;

function SmilesTable()
{
	if(ie)
	{
		if(document.all.SmilesTr.style.display=="none")
		{
		document.all.SmilesText.innerText="скрыть смайлики";
		document.all.SmilesTr.style.display='';
		}

		else
		{
		document.all.SmilesText.innerText="показать смайлики";
		document.all.SmilesTr.style.display='none';
		}
	}

	else if(ns)
	{

		if(document.getElementById("SmilesTr").style.display=="none")
		{
		document.getElementById("SmilesText").innerHTML="скрыть смайлики";
		document.getElementById("SmilesTr").style.display='';
		}

		else
		{
		document.getElementById("SmilesText").innerHTML="показать смайлики";
		document.getElementById("SmilesTr").style.display='none';
		}
	}

	else
	alert("Ваш браузер не поддерживается!");
}

function InsertSmile(SmileId)
{
	if(ie)
	{
	document.all.NewAnswer.focus();
	document.all.NewAnswer.value+=" :"+SmileId+": ";
	}


	else if(ns)
	{
	document.forms['guestbook'].elements['NewAnswer'].focus();
	document.forms['guestbook'].elements['NewAnswer'].value+=" :"+SmileId+": ";
	}

	else
	alert("Ваш браузер не поддерживается!");
}

function InsertName(NameId)
{
	if(ie)
	{
	document.all.NewAnswer.focus();
	document.all.NewAnswer.value+=" "+NameId+", ";
	}


	else if(ns)
	{
	document.forms['guestbook'].elements['NewAnswer'].focus();
	document.forms['guestbook'].elements['NewAnswer'].value+=" "+NameId+", ";
	}

	else
	alert("Ваш браузер не поддерживается!");
}

// -->
</script>
HTML;
						$manlix=array_merge($manlix,parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat",1));

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

							if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['message'],2))==2)
							$manlix['other']['message']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['message']);

							if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['answer'],2))==2)
							$manlix['other']['answer']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['answer']);

												

						$manlix['section']['name']="Ответ на сообщение";

						$manlix['result'].="<table border=0><tr><td></td></tr></table>
						<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
						<form method=post name=guestbook>
						<input type=hidden name=MessageId value='".$manlix['other']['time']."'>
						<tr bgcolor=#faedc0><td><i><font face=verdana size=1 onclick=InsertName(\"".addslashes(str_replace(" ","&nbsp;",$manlix['other']['name']))."\") style='cursor: hand'><b>·</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
						<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
						<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."</font></i></div>
						".$manlix['other']['message']."
						<div align=right>";

						if(!empty($manlix['other']['homepage']))
						$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='Домашняя страничка'></a>";

						if(!empty($manlix['other']['mail']))
						$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='Электронная почта'></a>";

						if(!empty($manlix['other']['icq']))
						$manlix['result'].="<img src='images/icq.gif' border=0 alt='Аська: ".$manlix['other']['icq']."'>";

						$manlix['result'].="</div>
						</font></td></tr>";

						if(!empty($manlix['other']['answer']))
						$manlix['result'].="<tr bgcolor=#faedc0><td><i><font face=verdana size=1 color=maroon>Существующий ответ:<br><table border=0 cellspacing=1 cellpadding=3 bgcolor=gray width=100%><tr><td bgcolor=#fef1d8><font face=verdana size=1><font color=maroon>".$manlix['other']['author']."</font>: ".$manlix['other']['answer']."<i><div align=right><font color=maroon>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</font></div></i></font></td></tr></table><div align=right>(<a href='?'>управление скриптом</a> / <a href='?section=0_0'>оставить ответ и перейти к списку сообщений</a>)</div><br><font color=#de0000>Обратите внимание!</font><ul type=square>".(($_COOKIE[$manlix['script']['prefix']."login"]!=$manlix['other']['author'])?"<li>Имя автора старого ответа (".$manlix['other']['author'].") изменится на Ваше имя (".$_COOKIE[$manlix['script']['prefix']."login"].").</li>":null)."<li>Старый ответ изменится на новый.</li></ul>Новый ответ:</font></i><center><textarea name=NewAnswer rows=5 cols=46 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 390px; height: 90px\"></textarea></center></td></tr>";
						else
						$manlix['result'].="<tr bgcolor=#faedc0><td><i><div align=right><font face=verdana size=1>(<a href='?section=0_0'>отменить действие и вернуться к списку сообщений</a>)</font></div><br><font face=verdana size=1 color=maroon>Новый ответ:</font></i><center><textarea name=NewAnswer rows=5 cols=46 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 390px; height: 90px\"></textarea></center></td></tr>";

						$manlix['result'].="
						<tr bgcolor=#faedc0><td align=center><font face=verdana size=1 color=#de0000 style='cursor: hand' onclick=SmilesTable() id=SmilesText>показать смайлики</font></td></tr>
						<tr bgcolor=#faedca style='display: none' id=SmilesTr><td align=center>".$manlix['other']['smiles']."</td></tr>

						</table>
						<table border=0><tr><td></td></tr></table>


						<br><table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
						<tr><td><input type=submit value='Применить' class=submit style='width: 163px'></td></tr>
						</form>
						</table><br>";

						$manlix['found']=1;
						break;
						}
					}

					if(!isset($manlix['found']))
					$manlix['result'].=<<<HTML
					<center><br><font face=verdana size=1>Выбранное Вами сообщение не существует<br><br><br><br><a href="?section=0_0">...вернуться на шаг назад</a><br></font><br></center>
HTML;
				}

				elseif(empty($_POST['NewAnswer']))
				{
				$manlix['section']['name']="Ошибка";
				$manlix['result'].="<center><br><font face=verdana size=1 color=maroon>Вы не ввели НОВЫЙ ОТВЕТ<br><br><br>";
					if(!empty($_POST['MessageId']))
					$manlix['result'].="<a href='?section=0_0&MessageId=".$_POST['MessageId']."'>...вернуться на шаг назад</a> | ";
				$manlix['result'].="<a href='?section=0_0'>вывести список сообщений</a></font><br><br></center>";
				}

				elseif(empty($_POST['MessageId']))
				{
				$manlix['section']['name']="Ошибка";
				$manlix['result'].="<center><br><font face=verdana size=1 color=maroon>Не указан id сообщения<br><br><br>";
				$manlix['result'].="<a href='?section=0_0'>вывести список сообщений</a></font><br><br></center>";
				}

				else
				{
				$OpenMessagesFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base'],"w");
				flock($OpenMessagesFile,1);
				flock($OpenMessagesFile,2);

					while(list(,$string)=each($manlix['base']))
					{
					list(	$manlix['other']['time'],
						$manlix['other']['name'],
						$manlix['other']['homepage'],
						$manlix['other']['mail'],
						$manlix['other']['icq'],
						$manlix['other']['message'],
						$manlix['other']['ip'],
						$manlix['other']['author'],
						$manlix['other']['answer'],
						$manlix['other']['answerTime'],
						)=explode("::",$string);

						if($_POST['MessageId']===$manlix['other']['time'])
						{
						fwrite($OpenMessagesFile,	$manlix['other']['time']."::".
									$manlix['other']['name']."::".
									$manlix['other']['homepage']."::".
									$manlix['other']['mail']."::".
									$manlix['other']['icq']."::".
									$manlix['other']['message']."::".
									$manlix['other']['ip']."::".
									$_COOKIE[$manlix['script']['prefix']."login"]."::".
									manlix_to_normal_string(manlix_stripslashes($_POST['NewAnswer']))."::".
									manlix_time()."::".
									chr(13).chr(10)
						);
						$manlix['changed']=$manlix['okay']=1;
						}

						else fwrite($OpenMessagesFile,$string);
					}

				fclose($OpenMessagesFile);

					if(!isset($manlix['changed']))
					$manlix['result'].=<<<HTML
					<center><br><font face=verdana size=1>Выбранное Вами сообщение не существует<br><br><br><br><a href="?section=0_0">...вернуться на шаг назад</a><br></font><br></center>
HTML;

					else
					{
					$manlix['section']['name']="Ответ добавлен";

					$manlix['result'].=<<<HTML
					<center><br><br><font face=verdana size=1 color=maroon>Ответ на сообщение успешно добавлен<br><br><br>(<a href='?section=0_0'>добавить ответ на другое сообщение</a>)</font><br><br><br></center>
HTML;
					}
				}
			}

			break;

	case "0_1":	$manlix['section']['name']="Выберите сообщение, которое будете редактировать";

			$manlix['base']=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);

			if(!count($manlix['base']))
			{
			$manlix['result']=<<<HTML
			<center><font face=verdana size=1 color=maroon><i><br>гостевая книга пуста<br><br><br><br></i><a href="?">...вернуться назад</a></font><br><br></center>
HTML;
			}

			elseif(empty($MessageId))
			{
			$manlix['other']['navigation']=manlix_array_navigation(
								(isset($manlix['base']))?array_reverse($manlix['base']):null,
								(isset($manlix['numeric']['show_messages']))?$manlix['numeric']['show_messages']:null,
								(isset($manlix['numeric']['show_pages']))?$manlix['numeric']['show_pages']:null,
								"?section=0_1&guestbook_page=",
								(isset($guestbook_page))?$guestbook_page:null,
								(isset($manlix['symbol']['left']))?$manlix['symbol']['left']:null,
								(isset($manlix['symbol']['right']))?$manlix['symbol']['right']:null,
								(isset($manlix['color']['not_active_symbol']))?$manlix['color']['not_active_symbol']:null,
								(isset($manlix['color']['not_current_page']))?$manlix['color']['not_current_page']:null,
								(isset($manlix['color']['current_page']))?$manlix['color']['current_page']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['symbol']['separator_between_pages']))?$manlix['symbol']['separator_between_pages']:null
								);

			$manlix['strings']=$manlix_array_navigation;

				while(list($number,$string)=each($manlix['strings']['result_strings']))
				{
				$manlix['other']['num']=$manlix['strings']['count_all_strings']-$manlix['strings']['start_string']-$number;
				list(	$manlix['other']['time'],
					$manlix['other']['name'],
					$manlix['other']['homepage'],
					$manlix['other']['mail'],
					$manlix['other']['icq'],
					$manlix['other']['message'],
					$manlix['other']['ip'],
					$manlix['other']['author'],
					$manlix['other']['answer'],
					$manlix['other']['answerTime'],
				)=explode("::",$string);

				if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['message'],2))==2)
				$manlix['other']['message']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['message']);

				if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['answer'],2))==2)
				$manlix['other']['answer']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['answer']);

			$manlix['result'].="<table border=0><tr><td></td></tr></table>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>·</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
			<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
			<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."<br>Сообщение № ".$manlix['other']['num']."<br><a href='?section=0_1&MessageId=".$manlix['other']['time']."'><font color=#de0000>Редактировать</font></a></font></i></div>
			".$manlix['other']['message']."
			<div align=right>";

			if(!empty($manlix['other']['homepage']))
			$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='Домашняя страничка'></a>";

			if(!empty($manlix['other']['mail']))
			$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='Электронная почта'></a>";

			if(!empty($manlix['other']['icq']))
			$manlix['result'].="<img src='images/icq.gif' border=0 alt='Аська: ".$manlix['other']['icq']."'>";

			$manlix['result'].="</div>
			</font></td></tr>";

			if(!empty($manlix['other']['answer']))
			$manlix['result'].="<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>·</b></i> <font color=maroon>".$manlix['other']['author']."</font>: <font color=#000000>".$manlix['other']['answer']."</font><i><div align=right>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</div></i></font></td></tr>";

			$manlix['result'].="</table>
			<table border=0><tr><td></td></tr></table>";
				}
			$manlix['result'].="
			<br>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr><td bgcolor=#faedc0 align=center><i><font face=verdana size=1>".$manlix['other']['navigation']."</font></i></td></tr>
			</table>
			<br>";
			}

			else
			{
				if(!isset($_POST['NewMessage']))
				{
					while(list(,$string)=each($manlix['base']))
					{
					list(	$manlix['other']['time'],
						$manlix['other']['name'],
						$manlix['other']['homepage'],
						$manlix['other']['mail'],
						$manlix['other']['icq'],
						$manlix['other']['message'],
						$manlix['other']['ip'],
						$manlix['other']['author'],
						$manlix['other']['answer'],
						$manlix['other']['answerTime'],
					)=explode("::",$string);

						if($_GET['MessageId']===$manlix['other']['time'])
						{
						$manlix['result'].=<<<HTML
<script language="javascript" type="text/javascript">
<!--
var ie=document.all?1:0;
var ns=document.getElementById&&!document.all?1:0;

function SmilesTable()
{
	if(ie)
	{
		if(document.all.SmilesTr.style.display=="none")
		{
		document.all.SmilesText.innerText="скрыть смайлики";
		document.all.SmilesTr.style.display='';
		}

		else
		{
		document.all.SmilesText.innerText="показать смайлики";
		document.all.SmilesTr.style.display='none';
		}
	}

	else if(ns)
	{

		if(document.getElementById("SmilesTr").style.display=="none")
		{
		document.getElementById("SmilesText").innerHTML="скрыть смайлики";
		document.getElementById("SmilesTr").style.display='';
		}

		else
		{
		document.getElementById("SmilesText").innerHTML="показать смайлики";
		document.getElementById("SmilesTr").style.display='none';
		}
	}

	else
	alert("Ваш браузер не поддерживается!");
}

function InsertSmile(SmileId)
{
	if(ie)
	{
	document.all.NewMessage.focus();
	document.all.NewMessage.value+=" :"+SmileId+": ";
	}


	else if(ns)
	{
	document.forms['guestbook'].elements['NewMessage'].focus();
	document.forms['guestbook'].elements['NewMessage'].value+=" :"+SmileId+": ";
	}

	else
	alert("Ваш браузер не поддерживается!");
}

function InsertName(NameId)
{
	if(ie)
	{
	document.all.NewMessage.focus();
	document.all.NewMessage.value+=" "+NameId+", ";
	}


	else if(ns)
	{
	document.forms['guestbook'].elements['NewMessage'].focus();
	document.forms['guestbook'].elements['NewMessage'].value+=" "+NameId+", ";
	}

	else
	alert("Ваш браузер не поддерживается!");
}

// -->
</script>
HTML;
						$manlix=array_merge($manlix,parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat",1));

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

							if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['answer'],2))==2)
							$manlix['other']['answer']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['answer']);

												

						$manlix['section']['name']="Ответ на сообщение";

						$manlix['result'].="<table border=0><tr><td></td></tr></table>
						<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
						<form method=post name=guestbook>
						<input type=hidden name=MessageId value='".$manlix['other']['time']."'>
						<tr bgcolor=#faedc0><td><i><font face=verdana size=1 onclick=InsertName(\"".addslashes(str_replace(" ","&nbsp;",$manlix['other']['name']))."\") style='cursor: hand'><b>·</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
						<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
						<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."</font></i></div>
						<textarea name=NewMessage rows=5 cols=46 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 390px; height: 90px\">".$manlix['other']['message']."</textarea>
						<div align=right>";

						if(!empty($manlix['other']['homepage']))
						$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='Домашняя страничка'></a>";

						if(!empty($manlix['other']['mail']))
						$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='Электронная почта'></a>";

						if(!empty($manlix['other']['icq']))
						$manlix['result'].="<img src='images/icq.gif' border=0 alt='Аська: ".$manlix['other']['icq']."'>";

						$manlix['result'].="</div>
						</font></td></tr>
						<tr bgcolor=#faedc0><td align=center><font face=verdana size=1 color=#de0000 style='cursor: hand' onclick=SmilesTable() id=SmilesText>показать смайлики</font></td></tr>
						<tr bgcolor=#faedca style='display: none' id=SmilesTr><td align=center>".$manlix['other']['smiles']."</td></tr>";

						if(!empty($manlix['other']['answer']))
						$manlix['result'].="<tr bgcolor=#faedc0><td><font face=verdana size=1><i><font color=maroon>".$manlix['other']['author']."</font></i>: ".$manlix['other']['answer']."<i><div align=right><font color=maroon>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</div></i></font></td></tr>";

						$manlix['result'].="	</table>
						<table border=0><tr><td></td></tr></table>


						<br><table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
						<tr><td><input type=submit value='Применить' class=submit style='width: 163px'></td></tr>
						</form>
						</table><br>";

						$manlix['found']=1;
						break;

						}
					}

					if(!isset($manlix['found']))
					$manlix['result'].=<<<HTML
					<center><br><font face=verdana size=1>Выбранное Вами сообщение не существует<br><br><br><br><a href="?section=0_1">...вернуться на шаг назад</a><br></font><br></center>
HTML;
				}

				elseif(empty($_POST['NewMessage']))
				{
				$manlix['section']['name']="Ошибка";
				$manlix['result'].="<center><br><font face=verdana size=1 color=maroon>Вы стёрли сообщение пользователя<br><br><br>";
					if(!empty($_POST['MessageId']))
					$manlix['result'].="<a href='?section=0_1&MessageId=".$_POST['MessageId']."'>...вернуться на шаг назад</a> | ";
				$manlix['result'].="<a href='?section=0_1'>вывести список сообщений</a></font><br><br></center>";
				}

				elseif(empty($_POST['MessageId']))
				{
				$manlix['section']['name']="Ошибка";
				$manlix['result'].="<center><br><font face=verdana size=1 color=maroon>Не указан идентификатор сообщения<br><br><br>";
				$manlix['result'].="<a href='?section=0_1'>вывести список сообщений</a></font><br><br></center>";
				}

				else
				{
				$OpenMessagesFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base'],"w");
				flock($OpenMessagesFile,1);
				flock($OpenMessagesFile,2);

					while(list(,$string)=each($manlix['base']))
					{
					list(	$manlix['other']['time'],
						$manlix['other']['name'],
						$manlix['other']['homepage'],
						$manlix['other']['mail'],
						$manlix['other']['icq'],
						$manlix['other']['message'],
						$manlix['other']['ip'],
						$manlix['other']['author'],
						$manlix['other']['answer'],
						$manlix['other']['answerTime'],
						)=explode("::",$string);

						if($_POST['MessageId']===$manlix['other']['time'])
						{
						fwrite($OpenMessagesFile,	$manlix['other']['time']."::".
									$manlix['other']['name']."::".
									$manlix['other']['homepage']."::".
									$manlix['other']['mail']."::".
									$manlix['other']['icq']."::".
									manlix_to_normal_string(manlix_stripslashes($_POST['NewMessage']))."::".
									$manlix['other']['ip']."::".
									$manlix['other']['author']."::".
									$manlix['other']['answer']."::".
									$manlix['other']['answerTime']."::".
									chr(13).chr(10)
						);
						$manlix['changed']=$manlix['okay']=1;
						}

						else fwrite($OpenMessagesFile,$string);
					}

				fclose($OpenMessagesFile);

					if(!isset($manlix['changed']))
					$manlix['result'].=<<<HTML
					<center><br><font face=verdana size=1>Выбранное Вами сообщение не существует<br><br><br><br><a href="?section=0_1">...вернуться на шаг назад</a><br></font><br></center>
HTML;

					else
					{
					$manlix['section']['name']="Сообщение изменено";

					$manlix['result'].=<<<HTML
					<center><br><br><font face=verdana size=1 color=maroon>Сообщение было успешно изменено<br><br><br>(<a href='?section=0_1'>изменить ещё одно сообщение</a>)</font><br><br><br></center>
HTML;
					}
				}
			}

			break;

	case "0_2":	$manlix['section']['name']="Выберите сообщение, которое хотите удалить";
			$manlix['base']=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);

			if(!count($manlix['base']))
			{
			$manlix['result']=<<<HTML
			<center><font face=verdana size=1 color=maroon><i><br>гостевая книга пуста<br><br><br><br></i><a href="?">...вернуться назад</a></font><br><br></center>
HTML;
			}

			elseif(empty($_GET['MessageId']))
			{
			$manlix['other']['navigation']=manlix_array_navigation(
								(isset($manlix['base']))?array_reverse($manlix['base']):null,
								(isset($manlix['numeric']['show_messages']))?$manlix['numeric']['show_messages']:null,
								(isset($manlix['numeric']['show_pages']))?$manlix['numeric']['show_pages']:null,
								"?section=0_2&guestbook_page=",
								(isset($guestbook_page))?$guestbook_page:null,
								(isset($manlix['symbol']['left']))?$manlix['symbol']['left']:null,
								(isset($manlix['symbol']['right']))?$manlix['symbol']['right']:null,
								(isset($manlix['color']['not_active_symbol']))?$manlix['color']['not_active_symbol']:null,
								(isset($manlix['color']['not_current_page']))?$manlix['color']['not_current_page']:null,
								(isset($manlix['color']['current_page']))?$manlix['color']['current_page']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['symbol']['separator_between_pages']))?$manlix['symbol']['separator_between_pages']:null
								);

			$manlix['strings']=$manlix_array_navigation;

				while(list($number,$string)=each($manlix['strings']['result_strings']))
				{
				$manlix['other']['num']=$manlix['strings']['count_all_strings']-$manlix['strings']['start_string']-$number;
				list(	$manlix['other']['time'],
					$manlix['other']['name'],
					$manlix['other']['homepage'],
					$manlix['other']['mail'],
					$manlix['other']['icq'],
					$manlix['other']['message'],
					$manlix['other']['ip'],
					$manlix['other']['author'],
					$manlix['other']['answer'],
					$manlix['other']['answerTime'],
				)=explode("::",$string);

				if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['message'],2))==2)
				$manlix['other']['message']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['message']);

				if(count(split("(&#58;[A-Za-z0-9_-]+&#58;)",$manlix['other']['answer'],2))==2)
				$manlix['other']['answer']=preg_replace_callback("/&#58;([A-Za-z0-9_-]+)&#58;/","AutochangeSmiles",$manlix['other']['answer']);

			$manlix['result'].="<table border=0><tr><td></td></tr></table>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>·</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
			<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
			<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."<br>Сообщение № ".$manlix['other']['num']."</font></i></div>
			".$manlix['other']['message']."
			<div align=right>";

			if(!empty($manlix['other']['homepage']))
			$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='Домашняя страничка'></a>";

			if(!empty($manlix['other']['mail']))
			$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='Электронная почта'></a>";

			if(!empty($manlix['other']['icq']))
			$manlix['result'].="<img src='images/icq.gif' border=0 alt='Аська: ".$manlix['other']['icq']."'>";

			$manlix['result'].="</div>
			</font></td></tr>";

			if(!empty($manlix['other']['answer']))
			$manlix['result'].=	"<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>·</b></i> <font color=maroon>".$manlix['other']['author']."</font>: <font color=#000000>".$manlix['other']['answer']."</font><i><div align=right>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</div></i></font></td></tr>";

			$manlix['result'].="<tr bgcolor=#faedc0 align=center><td><i><a href='?section=0_2&MessageId=".$manlix['other']['time']."'><font face=verdana size=1 color=#de0000><b>УДАЛИТЬ СООБЩЕНИЕ</b></font></a></i></td></tr>";

			$manlix['result'].="</table>
			<table border=0><tr><td></td></tr></table>";
				}
			$manlix['result'].="
			<br>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr><td bgcolor=#faedc0 align=center><i><font face=verdana size=1>".$manlix['other']['navigation']."</font></i></td></tr>
			</table>
			<br>";
			}

			else
			{
				$OpenMessagesFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base'],"w");
				flock($OpenMessagesFile,1);
				flock($OpenMessagesFile,2);

					while(list(,$string)=each($manlix['base']))
					{
					list($manlix['other']['time'])=explode("::",$string);

						if($_GET['MessageId']!==$manlix['other']['time'])
						{
						fwrite($OpenMessagesFile,$string);
						$manlix['deleted']=$manlix['okay']=1;
						}
					}

				fclose($OpenMessagesFile);

					if(count($manlix['base'])==1) $manlix['deleted']=$manlix['okay']=1;

					if(!isset($manlix['deleted']))
					$manlix['result'].=<<<HTML
					<center><br><font face=verdana size=1>Выбранное Вами сообщение не существует<br><br><br><br><a href="?section=0_2">...вернуться на шаг назад</a><br></font><br></center>
HTML;

					else
					{
					$manlix['section']['name']="Сообщение удалено";

					$manlix['result'].=<<<HTML
					<center><br><br><br><font face=verdana size=1 color=maroon>Сообщение удалено<br><br><br>(<a href='?section=0_2'>удалить ещё сообщение</a>)</font><br><br><br></center>
HTML;
					}
			}

			break;

	case "1_0":	$manlix['section']['name']="Установите права доступа для нового админа";

			if(!empty($_POST))
			{
			$manlix['temp']['login2']=(!empty($_POST['login2']))?$_POST['login2']:null;
			$manlix['temp']['password2']=(!empty($_POST['password2']))?$_POST['password2']:null;
			$manlix['temp']['a']=(!empty($_POST['a']))?$_POST['a']:null;
			add_admin();
			unset($_POST['login2'],$_POST['password'],$_POST['a']);
			}

			if(!isset($manlix['okay']))
			{
				$manlix['result'].=<<<HTML
				<table border=0 align=center cellspacing=0 cellpadding=1 width=470>
				<form method=post>
				<tr><td bgcolor=#faedca>
					<table align=center>
						<tr><td height=10></td></tr>
						<tr><td align=center><font face=verdana size=1 color=maroon><i>Имя нового админа:</i></td></tr>
						<tr><td><input type=text name=login2 size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 298px" value="
HTML;
						if(isset($manlix['temp']['login2']))$manlix['result'].=$manlix['temp']['login2'];
						$manlix['result'].=<<<HTML
"></td></tr>
						<tr><td align=center><font face=verdana size=1 color=maroon><i>Его пароль:</i></td></tr>
						<tr><td><input type=password name=password2 size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 298px" value="
HTML;
						if(isset($manlix['temp']['password2']))	$manlix['result'].=$manlix['temp']['password2'];
						$manlix['result'].=<<<HTML
"></td></tr>
						<tr><td height=10></td></tr>
					</table>
				<tr><td bgcolor=#faedca align=center>
HTML;
				$void=-1;
				while(list($section)=each($manlix['sections']))
				{
				$void++;
				$manlix['result'].='<table border=0 width=300 cellspacing=1 cellpadding=3 align=center bgcolor=#faad1e><caption><font face=verdana size=1 color=maroon>Права доступа для раздела <font color=#de0000>'.$section.'</font>:</font></caption>';
					while(list($number,$result)=each($manlix['sections'][$section]))
					{
					$manlix['result'].='<tr><td bgcolor=#f7f1e1><font face=verdana size=1>'.($number+1).'<font face=verdana size=1>.</font> '.ucfirst($result).'</font></td><td bgcolor=#faedca width=20 onmouseover="this.style.backgroundColor=\'#f7f1e1\'; this.style.color=\'#de0000\'" onmouseout="this.style.backgroundColor=\'#faedca\'; this.style.color=\'#000000\'"><input type=checkbox name="a['.$void.']['.$number.']"';
						if(empty($manlix['access_level'][$void][$number]))	$manlix['result'].=" disabled";
						else						$manlix['result'].=(!empty($manlix['temp']['admin'][$void][$number]))?$manlix['temp']['admin'][$void][$number]:null;
					$manlix['result'].=<<<HTML
></td></tr>
HTML;
					}
				$manlix['result'].="</table><br>";
				}

				$manlix['result'].=<<<HTML
				</td></tr>
				<tr><td bgcolor=#faedca align=center>
					<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000>
					<tr><td><input type=submit value="&gt; &gt; Всё верно, хочу добавить нового админа &lt; &lt;" class=submit style="width: 298px"></td></tr>
					</table><br>
				</td></tr>
				</table><br>
HTML;
			}
			break;

	case "1_1":	$manlix['section']['name']="Выберите имя админа";
			$_POST['a']=(isset($_POST['a']))?$_POST['a']:null;

			$manlix['result'].=<<<HTML
			<table border=0 align=center cellspacing=0 cellpadding=1 width=470>
			<tr><td bgcolor=#faedca>
HTML;

			if(empty($_POST['selected_admin']))
			{
			$admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);
			$manlix['result'].="<center><form method=post><br><select name=selected_admin class=name onfocus='id=className' onblur='id=\"\"' style='font: italic'>";
			$manlix['result'].="<option value=''>сделайте выбор...</option>";
				$void=0;
				while(list(,$string)=each($admins))
				{
				list($name,$password,$permission)=explode("::",$string);
				$void++;
				$manlix['result'].="<option value='".$name."'";

					if($name==stripslashes($_COOKIE[$manlix['script']['prefix']."login"]))
					$manlix['result'].=" style='color: green'";

				$manlix['result'].=">".str_replace(" ","&nbsp;",htmlspecialchars($name))."</option>";
				}
			$manlix['result'].="</select></center>";
			$manlix['result'].='<br><table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
			<tr><td><input type=submit value=Дальше... class=submit style="width: 100px"></td></tr>
			</form>
			</table>';
			}

			elseif(!admin_exists($_POST['selected_admin']))
			{
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>Выбранный Вами админ  не существует.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_1">Нажмите сюда, чтобы выбрать имя админа из списка существующих.</a><br><br></font>
HTML;
			}

			elseif(!$string=check_permission($_POST['a']))
			{
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>Неверный формат прав доступа.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_1">Нажмите сюда, чтобы ещё раз выбрать имя админа из списка существующих.</a><br><br></font>
HTML;
			}

			elseif(ereg("^".strtolower($_POST['selected_admin'])."$",strtolower($_COOKIE[$manlix['script']['prefix']."login"])))
			{
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>Изменять права доступа самому себе нельзя.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_1">Нажмите сюда, чтобы ещё раз выбрать имя админа из списка существующих.</a><br><br></font>
HTML;
			}

			elseif(!empty($_POST['change_info']))
			{
			$manlix['section']['name']="Результат";
			$admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);
				$open=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins'],"w");
				flock($open,1);
				flock($open,2);
				while(list(,$body)=each($admins))
				{
				list($name,$password,$permission)=explode("::",$body);
					if(strtolower($name)==strtolower($_POST['selected_admin']))
					{
						if(isset($_POST['new_password']))	$password=md5($_POST['new_password']);
					fwrite($open,$name."::".$password."::".$string."::".chr(13).chr(10));
					}

					else	fwrite($open,$body);
				}
				fclose($open);

			$manlix['okay']="<center><br><font face=verdana size=1><br>Информация о админе <i><font color=maroon>".$_POST['selected_admin']."</font></i>, успешно изменена.</font><br><br></center>";
			$manlix['result'].=$manlix['okay'];
			}

			else
			{
			$manlix['section']['name']="Кроме прав доступа, Вы так же можете изменить пароль этого админа";
			$admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);
			$manlix['result'].=<<<HTML
			<table border=0 align=center width=300 cellspacing=0 cellpadding=1>
			<form method=post>
			<tr><td><br></td></tr>
			<tr><td width=200 align=right><font face=verdana size=1>Имя админа: </font></td><td><input type=text class=name size=28 value="
HTML;
			$manlix['result'].=(isset($_POST['selected_admin']))?$_POST['selected_admin']:null;
			$manlix['result'].=<<<HTML
" disabled></td></tr>

			<tr><td><br></td></tr>
			<tr><td colspan=2 bgcolor=maroon></td></tr>
			<tr><td colspan=2 align=center><font face=verdana size=1 color=gray><i>Если Вы хотите изменить пароль, то заполните следующее поле, если же не хотите изменять текущий пароль для этого админа, то оставьте это поле пустым.</i></font></td></tr>
			<tr><td align=right><font face=verdana size=1 color=gray>Новый пароль: </font></td><td><input type=password name=new_password size=28 style="border: 1px; border-style: solid; height: 16px; border-color: gray; background-color: #f2f2f2; font-family: verdana; font-size: 10px; color: gray"></td></tr>
			<tr><td colspan=2 bgcolor=maroon></td></tr>
			<tr><td><br></td></tr>
			</table>
HTML;
			$manlix['result'].='<input type=hidden name=selected_admin value="'.$_POST['selected_admin'].'">';
					if(!isset($manlix['temp']['admin']))
					{
					list($name,$password,$access)=explode("::",$manlix['temp']['admin_info']);
					$manlix['temp']['access_level']=$manlix['access_level'];
					unset($manlix['access_level']);
					$array_admin_access=explode("|",$access);

						for($i=0;$i<count($array_admin_access)-1;$i++)
						{
							$array_access_level=explode(",",$array_admin_access[$i]);

							for($q=0;$q<count($array_access_level);$q++)
							{
							$manlix['access_level'][$i][]=$array_access_level[$q];
							}
						}
					}

				$void=-1;
				while(list($section)=each($manlix['sections']))
				{
				$void++;
				$manlix['result'].='<table border=0 width=300 cellspacing=1 cellpadding=3 align=center bgcolor=#faad1e><caption><font face=verdana size=1 color=maroon>Права доступа для раздела <font color=#de0000>'.$section.'</font>:</font></caption>';

					while(list($number,$result)=each($manlix['sections'][$section]))
					{
					$manlix['result'].='<tr><td bgcolor=#f7f1e1><font face=verdana size=1>'.($number+1).'<font face=verdana size=1>.</font> '.ucfirst($result).'</font></td><td bgcolor=#faedca width=20 onmouseover="this.style.backgroundColor=\'#f7f1e1\'; this.style.color=\'#de0000\'" onmouseout="this.style.backgroundColor=\'#faedca\'; this.style.color=\'#000000\'"><input type=checkbox name="a['.$void.']['.$number.']"';
					if(!empty($manlix['access_level'][$void][$number]))		$manlix['result'].=" checked ";
					if(empty($manlix['temp']['access_level'][$void][$number]))	$manlix['result'].=" disabled";
					$manlix['result'].=<<<HTML
></td></tr>
HTML;
					}
				$manlix['result'].="</table><br>";
				}
			$manlix['result'].=<<<HTML
			<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
			<tr><td><input type=submit name=change_info value=Применить class=submit style="width: 100px"></td></tr>
			</form>
			</table><br>
HTML;
			}

			$manlix['result'].=<<<HTML
			</table><br>
HTML;
			break;

	case "1_2":	$manlix['section']['name']="Выберите имя админа, которого нужно удалить";

			$manlix['result'].=<<<HTML
			<table border=0 align=center cellspacing=0 cellpadding=1 width=470>
			<tr><td bgcolor=#faedca>
HTML;

			if(!(count($admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']))-1))
			{
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>Некого удалять.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?">Нажмите сюда, чтобы вернуться в главное меню управления.</a><br></font>
HTML;
			}

			elseif(empty($_POST['selected_admin']))
			{
			$manlix['result'].="<center><form method=post><br><select name=selected_admin class=name onfocus='id=className' onblur='id=\"\"' style='font: italic'>";
			$manlix['result'].="<option value=''>сделайте выбор...</option>";
				$void=0;
				while(list(,$string)=each($admins))
				{
				list($name,$password,$permission)=explode("::",$string);
				$void++;
					$manlix['result'].="<option value='".$name."'";
						if($name==$_COOKIE[$manlix['script']['prefix']."login"])
						$manlix['result'].=" style='color: green'";
					$manlix['result'].=">".str_replace(" ","&nbsp;",$name)."</option>";
				}
			$manlix['result'].="</select></center>";
			$manlix['result'].='<br><table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
			<tr><td><input type=submit value="Удалить выбранного мною админа" class=submit style="width: 200px"></td></tr>
			</form>
			</table>';
			}

			elseif(!admin_exists($_POST['selected_admin']))
			{
			$manlix[result].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>Выбранный Вами админ  не существует.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_2">Нажмите сюда, чтобы выбрать имя админа из списка существующих.</a><br><br></font>
HTML;
			}

			elseif(ereg("^".strtolower($_POST['selected_admin'])."$",strtolower($_COOKIE[$manlix['script']['prefix']."login"])))
			{
			$manlix['section']['name']="Рузультат";
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>Сами себя Вы удалить не можете.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_2">Нажмите сюда, чтобы выбрать другого админа из списка.</a><br></font>
HTML;
			}

			else
			{
			$admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);
			$manlix['section']['name']="Результат";
				$open=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins'],"w");
				flock($open,1);
				flock($open,2);
				while(list(,$body)=each($admins))
				{
				list($name,$password,$permission)=explode("::",$body);
					if(strtolower($name)!=strtolower($_POST['selected_admin']))
					fwrite($open,$body);
				}
				fclose($open);

			$manlix['okay']="<center><font face=verdana size=1><br><br><br>Админ <i><font color=maroon>".$_POST['selected_admin']."</font></i>, успешно удалён.</font><br><br></center>";
			$manlix['result'].=$manlix['okay'];
			}

			$manlix['result'].=<<<HTML
			</td></tr>
			</table><br>
HTML;
			break;

	case "2_0":	if(empty($_POST['SelectedTemplate']))
			{
			$manlix['section']['name']="Выберите шаблон, который будет использовать гостевая книга";

			$manlix['result'].="<center><form method=post><br><select name=SelectedTemplate class=name onfocus='id=className' onblur='id=\"\"' style='font: italic'>";
			$manlix['result'].="<option value=''>сделайте выбор...</option>";
				$OpenTemplatesDir=opendir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']);
					while(($template=readdir($OpenTemplatesDir))!==false)
					{
						if($template!="."&&$template!="..")
						{
							if($manlix['template']['parse']==$template)
							$manlix['result'].="<option value='".$template."' style='color: green'>".$template."</option>";

							else
							$manlix['result'].="<option value='".$template."'>".$template."</option>";
						}
					}
			$manlix['result'].="</select></center>";
			$manlix['result'].='<br><table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
			<tr><td><input type=submit value=Применить class=submit style="width: 100px"></td></tr>
			</table>
			</form>';
			}

			elseif($_POST['SelectedTemplate']==$manlix['template']['parse'])
			{
			$manlix['section']['name']="Шаблон";

			$manlix['result'].=<<<HTML
			<center><br><font face=verdana size=1 color=maroon>Выбарнный Вами шаблон уже используется<br><br>(<a href='?section=2_0'>...вернуться на шаг назад</a> | <a href='?'>вернуться в главное меню</a>)</font><br><br></center>
HTML;
			}

			elseif(!is_dir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']))
			{
			$manlix['section']['name']="Шаблон";

			$manlix['result'].=<<<HTML
			<center><br><font face=verdana size=1 color=maroon>Выбарнный Вами шаблон не существует<br><br>(<a href='?section=2_0'>...вернуться на шаг назад</a> | <a href='?'>вернуться в главное меню</a>)</font><br><br></center>
HTML;
			}

			else
			{
			$manlix['section']['name']="Шаблон применён";
			$conf=parse_ini_file("./inc/config.inc.dat",1);

			$OpenConfigFile=fopen("./inc/config.inc.dat","w");

				while(list($IniKey,$array)=each($conf))
				{
				fwrite($OpenConfigFile,"[".$IniKey."]".chr(13).chr(10));

					if($IniKey!="template")
						while(list($key,$value)=each($array))
						fwrite($OpenConfigFile,$key."=\"".$value."\";".chr(13).chr(10));

					else
					fwrite($OpenConfigFile,"parse=\"".$_POST['SelectedTemplate']."\";".chr(13).chr(10));
				}

			fclose($OpenConfigFile);

			$manlix['okay']=1;
			$manlix['result'].=<<<HTML
			<center><br><font face=verdana size=1 color=maroon>Операция прошла успешно.<br><br>(<a href='?section=2_0'>...вернуться на шаг назад</a> | <a href='?'>вернуться в главное меню</a>)</font><br><br></center>
HTML;
			}

			break;

	case "2_1":	if(empty($_POST['SelectedTemplate']))
			{
			$manlix['section']['name']="Выберите шаблон, который будете изменять";

			$manlix['result'].="<center><form method=post><br><select name=SelectedTemplate class=name onfocus='id=className' onblur='id=\"\"' style='font: italic'>";
			$manlix['result'].="<option value=''>сделайте выбор...</option>";
				$OpenTemplatesDir=opendir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']);
					while(($template=readdir($OpenTemplatesDir))!==false)
					{
						if($template!="."&&$template!="..")
						{
							if($manlix['template']['parse']==$template)
							$manlix['result'].="<option value='".$template."' style='color: green'>".$template."</option>";

							else
							$manlix['result'].="<option value='".$template."'>".$template."</option>";
						}
					}
			$manlix['result'].="</select></center>";
			$manlix['result'].='<br><table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
			<tr><td><input type=submit value=Выбрать... class=submit style="width: 100px"></td></tr>
			</table>
			</form>';
			}

			elseif(!is_dir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']))
			{
			$manlix['section']['name']="Шаблон";

			$manlix['result'].=<<<HTML
			<center><br><font face=verdana size=1 color=maroon>Выбарнный Вами шаблон не существует<br><br>(<a href='?section=2_1'>...вернуться на шаг назад</a> | <a href='?'>вернуться в главное меню</a>)</font><br><br></center>
HTML;
			}

			elseif(count($_POST)<2)
			{
			$manlix['section']['name']="Заполните формы для шаблона";
			$manlix['result'].=	"<table border=0 align=center>".
					"<form method=post>".
					"<input type=hidden name=SelectedTemplate value='".$_POST['SelectedTemplate']."'>".
					"<tr><td><div align=right><font face=verdana size=2 color=green><br><b><i>ШАБЛОНЫ<br>(информацию по константам смотрите в файле <a href='info.html'>info.html</a>)</i></b></font></div></td></tr>";
				while(list($key,$FileName)=each($manlix['templates']))
				{
					switch($key)
					{
					case "top":		$TemplateName="Верхушка";
								break;

					case "form":		$TemplateName="Форма для добавления сообщения";
								break;

					case "no_messages":	$TemplateName="Гостевая книга пуста";
								break;

					case "message":		$TemplateName="Сообщение";
								break;

					case "bottom":		$TemplateName="Низ";
								break;

					case "okay":		$TemplateName="Сообщение добавлено";
								break;

					case "closed":		$TemplateName="Гостевая книга закрыта";
								break;

					default:			$TemplateName=$key;
					}

				$manlix['result'].=	"<tr><td><font face=verdana size=1><b><i>· <font color=maroon>".$TemplateName."</font></i></b></font></td></tr>".
						"<tr><td><textarea wrap=off rows=10 cols=56 name=templates__".$key." class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 450px; height: 150px\">".ReadTemplate($_POST['SelectedTemplate'],$FileName)."</textarea></td></tr>";
				}

			$manlix['result'].="<tr><td><div align=right><font face=verdana size=2 color=green><br><b><i>ДОПОЛНИТЕЛЬНЫЕ ПАРАМЕТРЫ ШАБЛОНА</i></b></font></div><br></td></tr>";

			$manlix['color']=parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']."/config.inc.dat");
			$manlix['result'].="<tr><td><table width=100% align=center>";
				while(list($key,$value)=each($manlix['color']))
				{
					switch($key)
					{
					case "current_page":	$KeyName="Цвет цифры/числа в навигации для текущей страницы";
								break;

					case "not_current_page":	$KeyName="Цвет цифры/числа в навигации для нетекущей страницы";
								break;

					case "even":		$KeyName="Цвет для чёта";
								break;

					case "uneven":		$KeyName="Цвет для нечета";
								break;

					case "active_symbol":	$KeyName="Цвет для активных символов в навигации по краям";
								break;

					case "not_active_symbol":	$KeyName="Цвет для неактивных символов в навигации по краям";
								break;

					default:			$KeyName=$key;
					}
				$manlix['result'].='<tr><td align=right><font face=verdana size=1>'.$KeyName.':</font></td><td><input type=text name=color__'.$key.' size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px" value="'.htmlspecialchars($value).'"></td></tr>';
				}
			$manlix['result'].="</table></td></tr>";
			$manlix['result'].=<<<HTML
			<tr><td><br>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=Применить class=submit style="width: 100px"></td></tr>
				</table>
				<br>
			</td></tr>
			</form>
			</table>
HTML;
			}

			else
			{
			$color=parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat",1);

				$OpenConfigTemplateFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']."/config.inc.dat","w");
				flock($OpenConfigTemplateFile,1);
				flock($OpenConfigTemplateFile,2);
				fwrite($OpenConfigTemplateFile,"[color]".chr(13).chr(10));
				fclose($OpenConfigTemplateFile);

				while(list($key,$value)=each($_POST))
				{
					if(strstr($key,"templates__"))
					{
					list($a,$b)=explode("__",$key);
						if(isset($manlix[$a][$b]))
						{
						$OpenTemplateFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']."/".$manlix['templates'][$b],"w");
						flock($OpenTemplateFile,1);
						flock($OpenTemplateFile,2);
						fwrite($OpenTemplateFile,manlix_stripslashes($value));
						fclose($OpenTemplateFile);
						}
					}

					if(strstr($key,"color__"))
					{
					list($a,$b)=explode("__",$key);
						if(isset($color[$a][$b]))
						{
						$OpenConfigTemplateFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']."/config.inc.dat","a");
						flock($OpenConfigTemplateFile,1);
						flock($OpenConfigTemplateFile,2);
						fwrite($OpenConfigTemplateFile,$b."=\"".str_replace('"','\'',manlix_stripslashes($value))."\";".chr(13).chr(10));
						fclose($OpenConfigTemplateFile);
						}
					}
				}

			$manlix['okay']=1;
			$manlix['section']['name']="Операция прошла успешно";
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>Шаблон успешно изменён<br><br>(<a href='?section=2_1'>выбрать другой шаблон для изменения</a> | <a href='?'>вернуться в главное меню</a>)<br><br></font></center>
HTML;
			}

			break;

	case "2_2":	if(!eregi("^[a-z0-9_-]+$",(!empty($_POST['TemplateName']))?$_POST['TemplateName']:null))
			{
			$manlix['section']['name']="Задайте имя для нового шаблона";
			$manlix['result'].='
			<center><br>
			<form method=post>
				<font face=verdana size=1>Используйте, только: русские,<br>латинские буквы, цифры, знак подчёркивания и чёрточку (минус).</font>
				<br><br>
				<input type=text name=TemplateName size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px" value="'.htmlspecialchars((!empty($_POST['TemplateName']))?$_POST['TemplateName']:null).'"><br><br>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=Дальше... class=submit style="width: 100px"></td></tr>
				</table>			
			</form>
			</center>';
			}

			elseif(count($_POST)<2)
			{
			$manlix['section']['name']="Заполните формы";

			$manlix['result'].=	"<table border=0 align=center>".
					"<form method=post>".
					"<input type=hidden name=TemplateName value='".$_POST['TemplateName']."'>".
					"<tr><td><div align=right><font face=verdana size=2 color=green><br><b><i>ШАБЛОНЫ<br>(информацию по константам смотрите в файле <a href='info.html'>info.html</a>)</i></b></font></div></td></tr>";
				while(list($key,$FileName)=each($manlix['templates']))
				{
					switch($key)
					{
					case "top":		$TemplateName="Верхушка";
								break;

					case "form":		$TemplateName="Форма для добавления сообщения";
								break;

					case "no_messages":	$TemplateName="Гостевая книга пуста";
								break;

					case "message":		$TemplateName="Сообщение";
								break;

					case "bottom":		$TemplateName="Низ";
								break;

					case "okay":		$TemplateName="Сообщение добавлено";
								break;

					default:			$TemplateName=$key;
					}

				$manlix['result'].=	"<tr><td><font face=verdana size=1><b><i>· <font color=maroon>".$TemplateName."</font></i></b></font></td></tr>".
						"<tr><td><textarea wrap=off rows=10 cols=56 name=templates__".$key." class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 450px; height: 150px\"></textarea></td></tr>";
				}

			$manlix['result'].="<tr><td><div align=right><font face=verdana size=2 color=green><br><b><i>ДОПОЛНИТЕЛЬНЫЕ ПАРАМЕТРЫ ШАБЛОНА</i></b></font></div></td></tr>";

			$manlix['color']=parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat");
			$manlix['result'].="<tr><td><table width=100% align=center>";
				while(list($key,$value)=each($manlix['color']))
				{
					switch($key)
					{
					case "current_page":	$KeyName="Цвет цифры/числа в навигации для текущей страницы";
								break;

					case "not_current_page":	$KeyName="Цвет цифры/числа в навигации для нетекущей страницы";
								break;

					case "even":		$KeyName="Цвет для чёта";
								break;

					case "uneven":		$KeyName="Цвет для нечета";
								break;

					case "active_symbol":	$KeyName="Цвет для активных символов в навигации по краям";
								break;

					case "not_active_symbol":	$KeyName="Цвет для неактивных символов в навигации по краям";
								break;

					default:			$KeyName=$key;
					}
				$manlix['result'].='<tr><td align=right><font face=verdana size=1>'.$KeyName.':</font></td><td><input type=text name=color__'.$key.' size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px"></td></tr>';
				}
			$manlix['result'].="</table></td></tr>";
			$manlix['result'].=<<<HTML
			<tr><td>	<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=Создать class=submit style="width: 100px"></td></tr>
				</table>
			</td></tr>
			</form>
			</table>
HTML;
			}

			else
			{
			mkdir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['TemplateName'],"0770");

			$color=parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat",1);

				$OpenConfigTemplateFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['TemplateName']."/config.inc.dat","w");
				flock($OpenConfigTemplateFile,1);
				flock($OpenConfigTemplateFile,2);
				fwrite($OpenConfigTemplateFile,"[color]".chr(13).chr(10));
				fclose($OpenConfigTemplateFile);

				while(list($key,$value)=each($_POST))
				{
					if(strstr($key,"templates__"))
					{
					list($a,$b)=explode("__",$key);
						if(isset($manlix[$a][$b]))
						{
						$OpenTemplateFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['TemplateName']."/".$manlix['templates'][$b],"w");
						flock($OpenTemplateFile,1);
						flock($OpenTemplateFile,2);
						fwrite($OpenTemplateFile,manlix_stripslashes($value));
						fclose($OpenTemplateFile);
						}
					}

					if(strstr($key,"color__"))
					{
					list($a,$b)=explode("__",$key);
						if(isset($color[$a][$b]))
						{
						$OpenConfigTemplateFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['TemplateName']."/config.inc.dat","a");
						flock($OpenConfigTemplateFile,1);
						flock($OpenConfigTemplateFile,2);
						fwrite($OpenConfigTemplateFile,$b."=\"".str_replace('"','\'',manlix_stripslashes($value))."\";".chr(13).chr(10));
						fclose($OpenConfigTemplateFile);
						}
					}
				}

/*
				if(is_array($_POST['templates']))
					while(list($key,$value)=each($_POST['templates']))
					{
					$OpenTemplateFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['TemplateName']."/".$manlix['templates'][$key],"w");
					flock($OpenTemplateFile,1);
					flock($OpenTemplateFile,2);
					fwrite($OpenTemplateFile,manlix_stripslashes($value));
					fclose($OpenTemplateFile);
					}

				if(is_array($_POST['colors']))
				{
					$OpenConfigTemplateFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['TemplateName']."/config.inc.dat","w");
					flock($OpenConfigTemplateFile,1);
					flock($OpenConfigTemplateFile,2);
					fwrite($OpenConfigTemplateFile,"[color]".chr(13).chr(10));
						while(list($key,$value)=each($_POST['colors']))
						fwrite($OpenConfigTemplateFile,$key."=\"".str_replace('"','\'',manlix_stripslashes($value))."\";".chr(13).chr(10));
					fclose($OpenConfigTemplateFile);
				}
*/
			$manlix['okay']=1;
			$manlix['section']['name']="Операция прошла успешно";
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>Шаблон успешно создан<br><br>(<a href='?section=2_2'>создать ещё шаблон</a> | <a href='?'>вернуться в главное меню</a>)<br><br></font></center>
HTML;
			}

			break;

	case "3_0":	$manlix['section']['name']="Просмотр параметров скрипта";
			$conf=parse_ini_file("./inc/config.inc.dat",1);
			$void=0;
			$manlix['result'].="<table border=0 width=440 align=center><tr><td>";
				while(list($section,$array)=each($conf))
				{
				$manlix['result'].="<table border=0 width=100% align=center cellspacing=0 cellpadding=1><tr><td colspan=2 bgcolor=#7e5757></td></tr><tr><td colspan=2 bgcolor=#f7f1e1 align=center><font face=verdana size=1><b>".((description($section,0))?description($section,0):$section)."</td></tr><tr><td colspan=2 bgcolor=#7e5757></td></tr>";
					while(list($key,$value)=each($array))
					{
					$bgcolor=(strstr(($void/2),"."))?"#f6f4dd":"#f6f4cd";
					$manlix['result'].="<tr bgcolor=".$bgcolor." valign=top><td width=200><font face=verdana size=1 color=maroon>&nbsp;<i>".($void+1)."</i>&nbsp;<b>·</b>&nbsp;".((description($section,$key))?description($section,$key):$key)."&nbsp;</td><td><font face=verdana size=1>".((!empty($value))?($value!=" ")?htmlspecialchars($value):"<b>пробел</b>":0)."</td></tr>";
					$void++;
					}
				$manlix['result'].=	"<tr><td colspan=2 bgcolor=#7e5757></td></tr>".
						"</table>".
						"<table border=0 height=10><tr><td></td></tr></table>";
				}
			$manlix['result'].="</td></tr></table>";
			break;

	case "3_1":	$manlix['section']['name']="Изменение параметров скрипта";
			if(!count($_POST))
			{
				$conf=parse_ini_file("./inc/config.inc.dat",1);
				$void=0;
				$manlix['result'].="<table border=0 width=440 align=center><form method=post><input type=hidden name=dir__inc value='".$conf['dir']['inc']."'><tr><td>";
					while(list($section,$array)=each($conf))
					{
					$manlix['result'].="<table border=0 width=100% align=center cellspacing=0 cellpadding=1><tr><td colspan=2 bgcolor=#7e5757></td></tr><tr><td colspan=2 bgcolor=#f7f1e1 align=center><font face=verdana size=1><b>".((description($section,0))?description($section,0):$section)."</td></tr><tr><td colspan=2 bgcolor=#7e5757></td></tr>";
						while(list($key,$value)=each($array))
						{
						$bgcolor=(strstr(($void/2),"."))?"#f6f4dd":"#f6f4cd";
						$manlix['result'].="<tr bgcolor=".$bgcolor." valign=top><td width=200><font face=verdana size=1 color=maroon>&nbsp;<i>".($void+1)."</i>&nbsp;<b>·</b>&nbsp;".((description($section,$key))?description($section,$key):$key)."&nbsp;</td><td><input type=text name=".$section."__".$key." size=42 class=name onfocus=\"id=className\" style=\"font: italic; width: 228px\" onblur=\"id=''\"\" value=\"".((!empty($value))?htmlspecialchars($value):0)."\" ".(($section=="dir"&&$key=="inc")?" disabled":null)."></td></tr>";
						$void++;
						}
					$manlix['result'].=	"<tr><td colspan=2 bgcolor=#7e5757></td></tr>".
							"</table>".
							"<table border=0 height=10><tr><td></td></tr></table>";
					}
				$manlix['result'].=	"</td></tr></table>".
						"<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
						<tr><td><input type=submit value=Применить class=submit style='width: 100px'></td></tr>
						</table></form>";
			}

			else
			{
			$OpenConfigFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/config.inc.dat","w");
			flock($OpenConfigFile,1);
			flock($OpenConfigFile,2);
				while(list($key,$value)=each($_POST))
				{
				list($a,$b)=explode("__",$key);
					if(!isset($manlix['temp'][$a]))
					{
					$manlix['temp'][$a]=1;
					fwrite($OpenConfigFile,"[".$a."]".chr(13).chr(10));
					}

					fwrite($OpenConfigFile,$b."=\"".stripslashes(stripslashes(addslashes(str_replace('"','\'',$value))))."\";".chr(13).chr(10));
				}
			fclose($OpenConfigFile);

			$manlix['okay']=1;
			$manlix['result'].=<<<HTML
			<center><br><br><font face=verdana size=1 color=maroon>Параметры скрипта успешно изменены</font><br><br><br></center>
HTML;
			}

			break;

	case "3_2":	$manlix['section']['name']="Ваши права доступа отмечены галочкой";
			$manlix['result'].=<<<HTML
			<table border=0 align=center cellspacing=0 cellpadding=1 width=470>
			<tr><td bgcolor="#faedca">
HTML;
				if(admin_exists($_COOKIE[$manlix['script']['prefix']."login"]))
				{
				list($name,$password,$access)=explode("::",$manlix['temp']['admin_info']);
				unset($manlix['access_level']);	
				$array_admin_access=explode("|",$access);

					for($i=0;$i<count($array_admin_access)-1;$i++)
					{
						$array_access_level=explode(",",$array_admin_access[$i]);

						for($q=0;$q<count($array_access_level);$q++)
						$manlix['access_level'][$i][]=$array_access_level[$q];
					}
				}

			$void=0;
			while(list($section)=each($manlix['sections']))
			{
			$manlix['result'].='<table border=0 width=300 cellspacing=1 cellpadding=2 align=center bgcolor=#faad1e><caption><font face=verdana size=1 color=maroon>Права доступа для раздела <font color=#de0000>'.$section.'</font>:</font></caption>';

				while(list($number,$result)=each($manlix['sections'][$section]))
				{
				$manlix['result'].='<tr><td bgcolor=#f7f1e1><font face=verdana size=1>'.($number+1).'. '.ucfirst($result).'</font></td><td bgcolor="';
				if(empty($manlix['access_level'][$void][$number]))	$manlix['result'].="#faedca";
				else						$manlix['result'].="#f7f1e1";
$manlix['result'].='" width=20><input type=checkbox ';
				if(!empty($manlix['access_level'][$void][$number]))	$manlix['result'].=" checked ";
				$manlix['result'].=<<<HTML
disabled></td></tr>
HTML;
				}
			$manlix['result'].="</table><br>";
			$void++;
			}
			$manlix['result'].="<center><a href='?'><font face=verdana size=1>...вернуться в главное меню управления</font></a></center><br>";
			$manlix['result'].=<<<HTML
			</td></tr>
			</table>
HTML;
			break;

	case "3_3":	$manlix['section']['name']="Информация о скрипте";
			$manlix['script']['info']=(!empty($manlix['script']['info']))?$manlix['script']['info']:"нет информации";

			$manlix['result'].=	"<table border=0 width=400 align=center>".
					"<tr><td width=80><font face=verdana size=1>Имя скрипта:</td><td><font face=verdana size=1 color=maroon>".$manlix['script']['name']."</td></tr>".
					"<tr><td><font face=verdana size=1>По-русски:</td><td><font face=verdana size=1 color=maroon>".$manlix['script']['russian']."</td></tr>".
					"<tr><td><font face=verdana size=1>Версия:</td><td><font face=verdana size=1 color=#de0000>".$manlix['script']['version']."</td></tr>".
					"<tr><td colspan=2><font face=verdana size=1>Дополнительно:</td></tr>".
					"<tr><td colspan=2><font face=verdana size=1><i>".$manlix['script']['info']."</td></tr>".
					"</table>";
			break;

	case "4_0":	$manlix['section']['name']="Добавление нового значения для замены";

			if(empty($_POST))
			{
			$manlix['result'].=<<<HTML
			<center><br><form method=post><font face=verdana size=1>
			<table border=0>
			<tr><td align=right><font face=verdana size=1>Искать:</td><td><input type=text name=autochange1 size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 165px"></td></tr>
			<tr><td align=right><font face=verdana size=1>Заменять на:</td><td><input type=text name=autochange2 size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 165px"></td></tr>
			<tr><td colspan=2 align=center><br>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=Добавить class=submit style='width: 100px'></td></tr>
				</table><br>
			</td></tr>
			</form>
			</table>
			</center>
HTML;
			}

			elseif(empty($_POST['autochange1']))
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br>Вы не указали, что нужно искать<br><br><a href="?section=4_0">...вернуться на шаг назад</a><br><br></font>
			</center>
HTML;

			elseif(empty($_POST['autochange2']))
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br>Вы не указали, на что хотите заменить<br><br><a href="?section=4_0">...вернуться на шаг назад</a><br><br></font>
			</center>
HTML;

			elseif(CheckAutochange($_POST['autochange1']=preg_quote(manlix_stripslashes($_POST['autochange1']))))
			{
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br>То, что Вы указали в качестве искомого - уже есть в базе<br><br><a href="?section=4_0">...вернуться на шаг назад</a><br><br></font>
			</center>
HTML;
			}

			else
			{
			$OpenAutochangeFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['autochange'],"a");
			flock($OpenAutochangeFile,1);
			flock($OpenAutochangeFile,2);
			fwrite($OpenAutochangeFile,"/".$_POST['autochange1']."/i::".manlix_stripslashes($_POST['autochange2'])."::".time().".".manlix_char_generator("1234567890",32)."::".chr(13).chr(10));
			fclose($OpenAutochangeFile);
			$manlix['okay']=1;
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br>Автозамена успешно добавлена в базу<br><br>(<a href="?section=4_0">добавить ещё одну автозамену</a>)<br><br></font>
			</center>
HTML;
			}

			break;

	case "4_1":	$manlix['section']['name']="Изменение автозамены";
			$AutochangeFile=$AutochangeFileRead=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['autochange']);

			if(!count($AutochangeFile))
			{
			$manlix['result'].='<center>
			<font face=verdana size=1><br>В базе нет ниодной автозамены<br><br><a href="?">...вернуться в главное меню</a><br><br></font>
			</center>';
			}

			elseif(empty($AutochangeId))
			{
			$manlix['other']['navigation']=manlix_array_navigation(
								(isset($AutochangeFile))?array_reverse($AutochangeFile):null,
								(isset($manlix['numeric']['show_messages']))?$manlix['numeric']['show_messages']:null,
								(isset($manlix['numeric']['show_pages']))?$manlix['numeric']['show_pages']:null,
								"?section=4_1&guestbook_page=",
								(isset($guestbook_page))?$guestbook_page:null,
								(isset($manlix['symbol']['left']))?$manlix['symbol']['left']:null,
								(isset($manlix['symbol']['right']))?$manlix['symbol']['right']:null,
								(isset($manlix['color']['not_active_symbol']))?$manlix['color']['not_active_symbol']:null,
								(isset($manlix['color']['not_current_page']))?$manlix['color']['not_current_page']:null,
								(isset($manlix['color']['current_page']))?$manlix['color']['current_page']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['symbol']['separator_between_pages']))?$manlix['symbol']['separator_between_pages']:null
								);

			$manlix['strings']=$manlix_array_navigation;

				$manlix['result'].=<<<HTML
				<table border=0 align=center width=400>
				<tr><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=180 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>искомое</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=180 bgcolor=#faad1e>
					<tr><td bgcolor=#f6f4cd align=center><font face=verdana size=1 color=maroon>заменяемое</td></tr>
					</table>
				</td></tr>
				</table>
HTML;

				while(list(,$string)=each($manlix['strings']['result_strings']))
				{
				list($a,$b,$c)=explode("::",$string);
				ereg("/(.*)/i",$a,$other);
				$manlix['result'].="
				<table border=0 align=center><tr><td>
				<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
				<tr><td bgcolor=#faedc0><font face=verdana size=1 color=#de0000> ".$other[1]."</td></tr>
				<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon>".$b."</td></tr>
				</table>
				</td></tr>
				<tr><td align=right><font face=verdana size=1>(<i><a href='?section=4_1&AutochangeId=".$c."'>изменить</a></i>)</td></tr>
				</table>";
				}

			$manlix['result'].="<br>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr><td bgcolor=#faedc0 align=center><i><font face=verdana size=1>".$manlix['other']['navigation']."</font></i></td></tr>
			</table>
			<br>";
			}

			else
			{
				while(list(,$string)=each($AutochangeFile))
				{
				list($a,$b,$c)=explode("::",$string);
					if($AutochangeId===$c)
					{
					$manlix['found']=1;
					break;
					}
				}

				if(!isset($manlix['found']))
				$manlix['result'].=<<<HTML
				<center>
				<font face=verdana size=1>
				<br>Указаная Вами автозамена не найдена в базе<br><br><a href="?section=4_1">...вернуться на шаг назад</a><br><br>
				</font>
				</center>
HTML;
				else
				{
					if(empty($_POST))
					{
					ereg("/(.*)/i",$a,$other);
					$manlix['result'].="
					<table border=0>
					<form method=post>
					<input type=hidden name=AutochangeId value='".$c."'>
					<tr><td></td></tr></table>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0><font face=verdana size=1 color=#de0000><input type=text name=autochange1 size=58 class=name onfocus=\"id=className\" onblur=\"id=''\"\" value='".htmlspecialchars($other[1])."'> -  искомое</td></tr>
					<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon><input type=text name=autochange2 size=58 class=name onfocus=\"id=className\" onblur=\"id=''\"\" value='".htmlspecialchars($b)."' style='color: maroon'> - заменяемое</td></tr>
					</table>
					<table border=0><tr><td></td></tr></table>
					<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
					<tr><td><input type=submit value=Изменить class=submit style='width: 100px'></td></tr>
					</table>
					<table border=0>
					</form>
					<tr><td></td></tr></table>";
					}

					elseif(empty($_POST['autochange1']))
					$manlix['result'].='<center>
					<font face=verdana size=1><br>Вы не указали, что нужно искать<br><br><a href="?section=4_1&AutochangeId='.(!empty($_POST['AutochangeId'])?$_POST['AutochangeId']:null).'">...вернуться на шаг назад</a><br><br></font>
					</center>';

					elseif(empty($_POST['autochange2']))
					$manlix['result'].='<center>
					<font face=verdana size=1><br>Вы не указали заменяемое<br><br><a href="?section=4_1&AutochangeId='.(!empty($_POST['AutochangeId'])?$_POST['AutochangeId']:null).'">...вернуться на шаг назад</a><br><br></font>
					</center>';

					elseif(CheckAutochange($_POST['autochange1']=preg_quote(manlix_stripslashes($_POST['autochange1']))))
					$manlix['result'].='<center>
					<font face=verdana size=1><br>То, что Вы указали в качестве искомого - уже есть в базе<br><br><a href="?section=4_1&AutochangeId='.(!empty($_POST['AutochangeId'])?$_POST['AutochangeId']:null).'">...вернуться на шаг назад</a><br><br></font>
					</center>';

					else
					{
					$OpenAutochangeFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['autochange'],"w");
					flock($OpenAutochangeFile,1);
					flock($OpenAutochangeFile,2);
						while(list(,$string)=each($AutochangeFileRead))
						{
						list(,,$c)=explode("::",$string);
							if($AutochangeId===$c)
							fwrite($OpenAutochangeFile,
							"/".$_POST['autochange1']."/i::".
							manlix_stripslashes($_POST['autochange2'])."::".
							$c."::".chr(13).chr(10)
							);

							else
							fwrite($OpenAutochangeFile,$string);
						}
					fclose($OpenAutochangeFile);
					$manlix['okay']=1;
					$manlix['result'].=<<<HTML
					<center>
					<font face=verdana size=1><br>Автозамена успешно изменена<br><br>(<a href="?section=4_1">изменить ещё одну автозамену</a>)<br><br></font>
					</center>
HTML;
					}
				}
			}

			break;

	case "4_2":	$manlix['section']['name']="Удаление автозамены";
			$AutochangeFile=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['autochange']);

			if(!count($AutochangeFile))
			{
			$manlix['result'].='<center>
			<font face=verdana size=1><br>В базе нет ниодной автозамены<br><br><a href="?">...вернуться в главное меню</a><br><br></font>
			</center>';
			}

			elseif(empty($AutochangeId))
			{
			$manlix['other']['navigation']=manlix_array_navigation(
								(isset($AutochangeFile))?array_reverse($AutochangeFile):null,
								(isset($manlix['numeric']['show_messages']))?$manlix['numeric']['show_messages']:null,
								(isset($manlix['numeric']['show_pages']))?$manlix['numeric']['show_pages']:null,
								"?section=4_2&guestbook_page=",
								(isset($guestbook_page))?$guestbook_page:null,
								(isset($manlix['symbol']['left']))?$manlix['symbol']['left']:null,
								(isset($manlix['symbol']['right']))?$manlix['symbol']['right']:null,
								(isset($manlix['color']['not_active_symbol']))?$manlix['color']['not_active_symbol']:null,
								(isset($manlix['color']['not_current_page']))?$manlix['color']['not_current_page']:null,
								(isset($manlix['color']['current_page']))?$manlix['color']['current_page']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['symbol']['separator_between_pages']))?$manlix['symbol']['separator_between_pages']:null
								);

			$manlix['strings']=$manlix_array_navigation;

				$manlix['result'].=<<<HTML
				<table border=0 align=center width=400>
				<tr><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=180 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>искомое</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=180 bgcolor=#faad1e>
					<tr><td bgcolor=#f6f4cd align=center><font face=verdana size=1 color=maroon>заменяемое</td></tr>
					</table>
				</td></tr>
				</table>
HTML;

				while(list(,$string)=each($manlix['strings']['result_strings']))
				{
				list($a,$b,$c)=explode("::",$string);
				ereg("/(.*)/i",$a,$other);
				$manlix['result'].="
				<table border=0 align=center><tr><td>
				<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
				<tr><td bgcolor=#faedc0><font face=verdana size=1 color=#de0000> ".$other[1]."</td></tr>
				<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon>".$b."</td></tr>
				</table>
				</td></tr>
				<tr><td align=right><font face=verdana size=1>(<i><a href='?section=4_2&AutochangeId=".$c."'>удалить</a></i>)</td></tr>
				</table>";
				}

			$manlix['result'].="<br>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr><td bgcolor=#faedc0 align=center><i><font face=verdana size=1>".$manlix['other']['navigation']."</font></i></td></tr>
			</table>
			<br>";
			}

			else
			{
			$OpenAutochangeFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['autochange'],"w");
			flock($OpenAutochangeFile,1);
			flock($OpenAutochangeFile,2);
				while(list(,$string)=each($AutochangeFile))
				{
				list(,,$c)=explode("::",$string);
					if($AutochangeId===$c)
					$manlix['found']=$manlix['okay']=1;

					else
					fwrite($OpenAutochangeFile,$string);
				}
			fclose($OpenAutochangeFile);

				if(!isset($manlix['found']))
				$manlix['result'].=<<<HTML
				<center>
				<font face=verdana size=1>
				<br>Указаная Вами автозамена не найдена в базе<br><br><a href="?section=4_2">...вернуться на шаг назад</a><br><br>
				</font>
				</center>
HTML;

				else
				$manlix['result'].=<<<HTML
				<center>
				<font face=verdana size=1><br>Автозамена успешно удалена<br><br>(<a href="?section=4_2">удалить ещё одну автозамену</a>)<br><br></font>
				</center>
HTML;
			}
			break;

	case "5_0":	$manlix['section']['name']="Добавление адреса в банлист";

			if(empty($_POST))
			{
			$manlix['result'].=<<<HTML
			<br><table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<form method=post>
			<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon><font color=#de0000><i><br>Обратите внимание!</i></font><ul type=square><li>Вы можете закрыть доступ некой подсети, для этого всего-лишь нужно ввести в поле <i>IP адрес</i>: <b>127.1.1.</b> Т.е. получится, что Вы закроете доступ к гостевой книге всем у кого ip начинается с <b>127.1.1.</b> (диапазон с <b>127.1.1.0</b> по <b>127.1.1.255</b>)</li><li>Если ввести <b>127.1.1</b> (без точки на конце), а не <b>127.1.1.</b> (с точкой на конце), то получится, что Вы закроете доступ к гостевой книге всем, у кого ip начинается с <b>127.1.1</b>, т.е. заблокируются адреса в диапазоне с <b>127.1.1.0</b> по <b>127.1.199.255</b></li><li>Чтобы закрыть доступ одному ip адресу, просто-так введите этот самый адрес, только уже полностью, например: <b>127.110.23.45</b></li></ul></td></tr>
			<tr><td bgcolor=#faedc0><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>IP адрес:</td><td><input type=text name=ip size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 165px"></td></tr></table></td></tr>
			<tr><td bgcolor=#f6f4cd><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>Причина:</td><td><input type=text name=reason size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 165px"></td></tr></table></td></tr>
			<tr><td bgcolor=#faedc0><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>На сколько закрыть доступ:</td>
				<td>
				<select name=time class=name onfocus="id=className" onblur="id=''"" style="font: italic">
				<option value='' selected>сделайте выбор</option>
				<option value=10>10 минут</option>
				<option value=30>30 минут</option>
				<option value=60>1 час</option>
				<option value=120>2 часа</option>
				<option value=240>4 часа</option>
				<option value=480>8 часов</option>
				<option value=960>16 часов</option>
				<option value=1440>1 день</option>
				<option value=2880>2 дня</option>
				<option value=5760>4 дня</option>
				<option value=10080>1 неделя</option>
				<option value=20160>2 недели</option>
				<option value=30240>3 недели</option>
				<option value=43200>1 месяц</option>
				<option value=86400>2 месяца</option>
				</select>
				</td></tr></table></td></tr>
			<tr><td colspan=2 bgcolor=#f6f4cd><br>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=Добавить class=submit style='width: 100px'></td></tr>
				</table><br>
			</td></tr>
			</form>
			</table><br>
HTML;
			}

			elseif(empty($_POST['ip']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>Вы не ввели ip адрес<br><br><a href="?section=5_0">...вернуться на шаг назад</a><br><br></font></center>
HTML;

			elseif(empty($_POST['reason']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>Вы не ввели причину<br><br><a href="?section=5_0">...вернуться на шаг назад</a><br><br></font></center>
HTML;

			elseif(empty($_POST['time']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>Вы не указали срок блокировки<br><br><a href="?section=5_0">...вернуться на шаг назад</a><br><br></font></center>
HTML;

			elseif(!is_numeric($_POST['time']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>Срок блокировки должен быть цифрой или числом<br><br><a href="?section=5_0">...вернуться на шаг назад</a><br><br></font></center>
HTML;
			elseif(CheckBanlist($_POST['ip']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>Указанный Вами ip адрес уже есть в банлисте<br><br><a href="?section=5_0">...вернуться на шаг назад</a><br><br></font></center>
HTML;
			else
			{
			$OpenBanlistFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['banlist'],"a");
			flock($OpenBanlistFile,1);
			flock($OpenBanlistFile,2);
			fwrite($OpenBanlistFile,	manlix_to_normal_string(manlix_stripslashes($_POST['ip']))."::".
						manlix_to_normal_string(manlix_stripslashes($_POST['reason']))."::".
						(time()+$_POST['time']*60)."::".
						time().".".manlix_char_generator("1234567890",32)."::".
						chr(13).chr(10)
			);
			fclose($OpenBanlistFile);
			$manlix['okay']=1;
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>IP адрес успешно добавлен в банлист<br><br>(<a href="?section=5_0">добавить ещё ip адрес</a>)<br><br></font></center>
HTML;
			}

			break;

	case "5_1":	$manlix['section']['name']="Изменение банлиста";

			$BanlistFile=$BanlistFileRead=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['banlist']);

			if(!count($BanlistFile))
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br><br>В банлисте никого нет<br><br><a href="?">...вернуться в главное меню</a><br><br></font>
			</center>
HTML;

			elseif(empty($BanId))
			{
			$manlix['other']['navigation']=manlix_array_navigation(
								(isset($BanlistFile))?array_reverse($BanlistFile):null,
								(isset($manlix['numeric']['show_messages']))?$manlix['numeric']['show_messages']:null,
								(isset($manlix['numeric']['show_pages']))?$manlix['numeric']['show_pages']:null,
								"?section=5_1&guestbook_page=",
								(isset($guestbook_page))?$guestbook_page:null,
								(isset($manlix['symbol']['left']))?$manlix['symbol']['left']:null,
								(isset($manlix['symbol']['right']))?$manlix['symbol']['right']:null,
								(isset($manlix['color']['not_active_symbol']))?$manlix['color']['not_active_symbol']:null,
								(isset($manlix['color']['not_current_page']))?$manlix['color']['not_current_page']:null,
								(isset($manlix['color']['current_page']))?$manlix['color']['current_page']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['symbol']['separator_between_pages']))?$manlix['symbol']['separator_between_pages']:null
								);

			$manlix['strings']=$manlix_array_navigation;

				$manlix['result'].=<<<HTML
				<table border=0 align=center width=400>
				<tr><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=100 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>ip адрес</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=100 bgcolor=#faad1e>
					<tr><td bgcolor=#f6f4cd align=center><font face=verdana size=1 color=maroon>причина</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=192 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>время разблокировки</td></tr>
					</table>
				</td></tr>
				</table>
HTML;

				while(list(,$string)=each($manlix['strings']['result_strings']))
				{
				list($ip,$reason,$time,$id)=explode("::",$string);
				$manlix['result'].="
				<table border=0 align=center><tr><td>
				<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
				<tr><td bgcolor=#faedc0><font face=verdana size=1 color=#de0000> ".$ip."</td></tr>
				<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon>".$reason."</td></tr>
				<tr><td bgcolor=#faedc0><font face=verdana size=1 color=#de0000> ".date('d.m.Y (H:i)',$time)."</td></tr>
				</table>
				</td></tr>
				<tr><td align=right><font face=verdana size=1>(<i><a href='?section=5_1&BanId=".$id."'>изменить</a></i>)</td></tr>
				</table>";
				}

			$manlix['result'].="<br>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr><td bgcolor=#faedc0 align=center><i><font face=verdana size=1>".$manlix['other']['navigation']."</font></i></td></tr>
			</table>
			<br>";
			}

			else
			{
				while(list(,$string)=each($BanlistFile))
				{
				list($ip,$reason,$time,$id)=explode("::",$string);
					if($BanId===$id)
					{
					$manlix['found']=1;
					break;
					}
				}

				if(!isset($manlix['found']))
				$manlix['result'].=<<<HTML
				<center>
				<font face=verdana size=1><br><br>Указанный Вами идентификатор не существует<br><br><a href="?section=5_1">...вернуться на шаг назад</a><br><br></font>
				</center>
HTML;

				elseif(empty($_POST['BanId']))
				{
				$manlix['result'].="<br>
				<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
				<form method=post>
				<input type=hidden name=BanId value='".$id."'>
				<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon><font color=#de0000><i><br>Обратите внимание!</i></font><ul type=square><li>Вы можете закрыть доступ некой подсети, для этого всего-лишь нужно ввести в поле <i>IP адрес</i>: <b>127.1.1.</b> Т.е. получится, что Вы закроете доступ к гостевой книге всем у кого ip начинается с <b>127.1.1.</b> (диапазон с <b>127.1.1.0</b> по <b>127.1.1.255</b>)</li><li>Если ввести <b>127.1.1</b> (без точки на конце), а не <b>127.1.1.</b> (с точкой на конце), то получится, что Вы закроете доступ к гостевой книге всем, у кого ip начинается с <b>127.1.1</b>, т.е. заблокируются адреса в диапазоне с <b>127.1.1.0</b> по <b>127.1.199.255</b></li><li>Чтобы закрыть доступ одному ip адресу, просто-так введите этот самый адрес, только уже полностью, например: <b>127.110.23.45</b></li></ul></td></tr>
				<tr><td bgcolor=#faedc0><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>IP адрес:</td><td><input type=text name=ip size=30 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"font: italic; width: 165px\" value='".htmlspecialchars($ip)."'></td></tr></table></td></tr>
				<tr><td bgcolor=#f6f4cd><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>Причина:</td><td><input type=text name=reason size=30 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"font: italic; width: 165px\" value='".htmlspecialchars($reason)."'></td></tr></table></td></tr>
				<tr><td bgcolor=#faedc0><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>Доступ закрыт до:</td><td><font face=verdana size=1 color=#de0000><i>".date('d.m.Y (H:i)',$time)."</i></font></td></tr></table></td></tr>
				<tr><td colspan=2 bgcolor=#f6f4cd><br>
					<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
					<tr><td><input type=submit value=Изменить class=submit style='width: 100px'></td></tr>
					</table><br>
				</td></tr>
				</form>
				</table><br>";
				}

				elseif(empty($_POST['ip']))
				$manlix['result'].="<center>
				<font face=verdana size=1><br><br>Вы не указали ip адрес<br><br><a href='?section=5_1&BanId=".$_POST['BanId']."'>...вернуться на шаг назад</a><br><br></font>
				</center>";

				elseif(empty($_POST['reason']))
				$manlix['result'].="<center>
				<font face=verdana size=1><br><br>Вы не указали причину блокировки<br><br><a href='?section=5_1&BanId=".$_POST['BanId']."'>...вернуться на шаг назад</a><br><br></font>
				</center>";

				else
				{
				$OpenBanlistFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['banlist'],"w");
				flock($OpenBanlistFile,1);
				flock($OpenBanlistFile,2);
					while(list(,$string)=each($BanlistFileRead))
					{
					list($ip,$reason,$time,$id)=explode("::",$string);
						if($_POST['BanId']===$id)
						{
						fwrite($OpenBanlistFile,	manlix_to_normal_string(manlix_stripslashes($_POST['ip']))."::".
									manlix_to_normal_string(manlix_stripslashes($_POST['reason']))."::".
									$time."::".
									$id."::".
									chr(13).chr(10)
									);
						$manlix['okay']=1;
						$manlix['result'].=<<<HTML
						<center>
						<font face=verdana size=1><br><br>Банлист успешно изменён<br><br>(<a href='?section=5_1'>изменить ещё</a>)<br><br></font>
						</center>
HTML;
						}

						else
						fwrite($OpenBanlistFile,$string);
					}
				fclose($OpenBanlistFile);
				}
			}

			break;

	case "5_2":	$manlix['section']['name']="Удаление из банлиста";

			$BanlistFile=$BanlistFile=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['banlist']);

			if(!count($BanlistFile))
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br><br>В банлисте никого нет<br><br><a href="?">...вернуться в главное меню</a><br><br></font>
			</center>
HTML;

			elseif(empty($_GET['BanId']))
			{
			$manlix['other']['navigation']=manlix_array_navigation(
								(isset($BanlistFile))?array_reverse($BanlistFile):null,
								(isset($manlix['numeric']['show_messages']))?$manlix['numeric']['show_messages']:null,
								(isset($manlix['numeric']['show_pages']))?$manlix['numeric']['show_pages']:null,
								"?section=5_2&guestbook_page=",
								(isset($guestbook_page))?$guestbook_page:null,
								(isset($manlix['symbol']['left']))?$manlix['symbol']['left']:null,
								(isset($manlix['symbol']['right']))?$manlix['symbol']['right']:null,
								(isset($manlix['color']['not_active_symbol']))?$manlix['color']['not_active_symbol']:null,
								(isset($manlix['color']['not_current_page']))?$manlix['color']['not_current_page']:null,
								(isset($manlix['color']['current_page']))?$manlix['color']['current_page']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['color']['active_symbol']))?$manlix['color']['active_symbol']:null,
								(isset($manlix['symbol']['separator_between_pages']))?$manlix['symbol']['separator_between_pages']:null
								);

			$manlix['strings']=$manlix_array_navigation;

				$manlix['result'].=<<<HTML
				<table border=0 align=center width=400>
				<tr><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=100 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>ip адрес</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=100 bgcolor=#faad1e>
					<tr><td bgcolor=#f6f4cd align=center><font face=verdana size=1 color=maroon>причина</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=192 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>время разблокировки</td></tr>
					</table>
				</td></tr>
				</table>
HTML;

				while(list(,$string)=each($manlix['strings']['result_strings']))
				{
				list($ip,$reason,$time,$id)=explode("::",$string);
				$manlix['result'].="
				<table border=0 align=center><tr><td>
				<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
				<tr><td bgcolor=#faedc0><font face=verdana size=1 color=#de0000> ".$ip."</td></tr>
				<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon>".$reason."</td></tr>
				<tr><td bgcolor=#faedc0><font face=verdana size=1 color=#de0000> ".date('d.m.Y (H:i)',$time)."</td></tr>
				</table>
				</td></tr>
				<tr><td align=right><font face=verdana size=1>(<i><a href='?section=5_2&BanId=".$id."'>удалить</a></i>)</td></tr>
				</table>";
				}

			$manlix['result'].="<br>
			<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<tr><td bgcolor=#faedc0 align=center><i><font face=verdana size=1>".$manlix['other']['navigation']."</font></i></td></tr>
			</table>
			<br>";
			}

			else
			{
			$OpenBanlistFile=fopen($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['banlist'],"w");
			flock($OpenBanlistFile,1);
			flock($OpenBanlistFile,2);
				while(list(,$string)=each($BanlistFile))
				{
				list(,,,$id)=explode("::",$string);

					if($_GET['BanId']===$id)
					$manlix['found']=1;

					else
					fwrite($OpenBanlistFile,$string);
				}
			fclose($OpenBanlistFile);

				if(!isset($manlix['found']))
				$manlix['result'].=<<<HTML
				<center>
				<font face=verdana size=1><font face=verdana size=1><br><br>Указанная Вами запись в банлисте - не существует<br><br><a href="?section=5_2">...вернуться на шаг назад</a><br><br></font></font>
				</center>
HTML;

				else
				{
				$manlix['result'].=<<<HTML
				<center>
				<font face=verdana size=1><font face=verdana size=1><br><br>Запись успешно удалена из банлиста<br><br>(<a href="?section=5_2">удалить ещё</a>)<br><br></font></font>
				</center>
HTML;
				$manlix['okay']=1;
				}
			}

			break;

	case "deny":	$manlix['section']['name']="Нет доступа";
			$manlix['result']=<<<HTML
			<table border="0" align="center" cellspacing="0" cellpadding="1" width="470">
			<tr><td><br></td></tr>
			<tr><td bgcolor="#faedca" align="center"><font face="verdana" size="1" color="maroon"><br>У Вас нет таких прав, которые позволяли бы Вам заходить в этот раздел.<br><br><hr color="maroon" size="1" width="415"><br></font></td></tr>
			<tr><td bgcolor="#faedca" align="center"><font face="verdana" size="1"><a href="?">Нажмите сюда, чтобы вернуться в главное меню управления</a><br><br></font></td></tr>
			<tr><td><br></td></tr>
			</table>
HTML;
			break;

	default: $manlix['section']['name']="Выберите один из разделов для выполнения каких-либо действий";

	$void=-1;
		while(list($section)=each($manlix['sections']))
		{
		$void++;
		$manlix['result'].='<table border=0 width=300 cellspacing=1 cellpadding=3 align=center bgcolor=#faad1e><caption><font face=verdana size=1 color=maroon>Раздел <font color=#de0000>'.$section.'</font>, выберите действие:</font></caption>';

			while(list($number,$result)=each($manlix['sections'][$section]))
			{
				if(!empty($manlix['access_level'][$void][$number]))
				$manlix['result'].='<tr onclick="location=\'?section='.$void.'_'.$number.'\'"><td bgcolor=#faedca onmouseover="this.style.backgroundColor=\'#f7f1e1\'; this.style.cursor=\'hand\'; this.style.color=\'#de0000\'" onmouseout="this.style.backgroundColor=\'#faedca\'; this.style.color=\'#000000\'"><font face=verdana size=1>'.($number+1).'<font face=verdana size=1>.</font> <a href="?section='.$void.'_'.$number.'">'.ucfirst($result).'</a></font></td></tr>';

				else
				$manlix['result'].='<tr><td bgcolor=#faedca><font face=verdana size=1 color=#808080>'.($number+1).'. '.ucfirst($result).'</font></td></tr>';
			}
		$manlix['result'].="</table><br>";
		}
	}
}

if(!isset($manlix['status']))	$manlix['status']="вход не выполнен";
?>
<html>
<head>
<title><?=$manlix['script']['name'],", версия: ",$manlix['script']['version']?> » Управление » <?=ereg_replace("<[^>]+>", "",ucfirst($manlix['status']))?></title>
<meta http-equiv="content-type" content="text/html; charset=windows-1251">
<meta http-equiv="pragma" content="no-cache">
<? if(isset($manlix['okay'])) echo '<meta http-equiv="refresh" content="3; url=?'.manlix_char_generator("qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890",32).'">'; ?>
<style type="text/css">
<!--
a:link	{color: #000000; text-decoration: none;}
a:active	{color: #000000; text-decoration: none;}
a:visited	{color: #000000; text-decoration: none;}
a:hover	{color: #de0000; text-decoration: none;}

.name	{border: 1px; border-style: solid; height: 16px; border-color: #000000; background-color: #ffe6b7; font-family: verdana; font-size: 10px; color: #de0000;}
#name	{border: 1px; border-style: solid; height: 16px; border-color: #000000; background-color: #fef1d8; font-family: verdana; font-size: 10px; color: #de0000;}
.submit	{border: 0px; height: 14px; background-color: #ffe6b7; font-family: verdana; font-size: 10px; color: #000000;}
-->
</style>
</script>
</head>
<body bgcolor=#ffffff background="images/background.gif" style="cursor: default" topmargin=3>
<table border=0 align=center cellspacing=0 cellpadding=1>
<tr><td align=right><font face=verdana size=1 style="background-color: #ffffff" color=#de0000><?=$manlix['status']?></font></td></tr>
<tr><td>
	<table width=500 align=center cellspacing=1 cellpadding=1 bgcolor=#faad1e>
	<tr align=center bgcolor=#faedca height=44><td><font face=verdana size=6 color=#fad27d><b><?=$manlix['script']['name']?></i></b></font></td></tr>
	<tr><td align=cetner bgcolor=#faedc0>
					<table border=0 align=center cellspacing=0 cellpadding=1 width=470>
					<tr><td height=10></td></tr>
					<tr><td bgcolor=maroon colspan=2></td></tr>
					<tr><td align=center bgcolor=#faedca colspan=2><font face=verdana color=maroon size=1><?=(isset($manlix['section']['name']))?$manlix['section']['name']:''?></font></td></tr>
					<tr><td bgcolor=maroon colspan=2></td></tr>
					<tr><td height=10></td></tr>
					<tr><td bgcolor=maroon colspan=2></td></tr>
					<tr><td colspan=2 bgcolor=#faedca><?=(isset($manlix['result']))?$manlix['result']:''?></td></tr>
					<tr><td bgcolor=maroon colspan=2></td></tr>
					<tr><td height=10></td></tr>
					</table>
	</td></tr>
	<tr align=center bgcolor=#faedca><td align=center><font face=verdana size=1><a href="http://manlix.ru" target="_blank">Разработка скрипта: Manlix</a></font></td></tr>
	</table>
</td></tr>
<?
if(!empty($manlix['access']))
{
echo "<tr><td align=right><font face=verdana size=1>(<a href='?exit'>закрыть сессию</a>)</font></td></tr>";
}
?>
</table>
</body>
</html>
<?
function error($error,$file){exit('<font face="verdana" size="1" color="#de0000"><b>'.$error.'<br>['.htmlspecialchars($file).']</b></font>');}

if(isset($_GET))	while(list($key,$value)=each($_GET)) $$key=$value;

$manlix=null;

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

function read_dir($dir)
{
	if($OpenDir=opendir($dir))
	{
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

	else error("нет прав",$dir);
}

if(!is_readable("./inc"))		error("нет прав для чтения текущий папки","./inc");
elseif(!is_writeable("./inc"))		error("нет прав для записи в текущую папку","./inc");
else				read_dir("./inc");

$manlix=parse_ini_file("./inc/config.inc.dat",1);
include("./inc/functions.inc.php");

if(isset($_SERVER['QUERY_STRING'])&&$_SERVER['QUERY_STRING']=="exit")
{
$_COOKIE=null;
setcookie($manlix['script']['prefix']."password",null);
}

$manlix['sections']=array(
		10	=>	"загрузка основной картинки счётчика",
		20	=>	"загрузка картинки для определённого числа",
		30	=>	"изменение цвета цифр на счётчике",
		40	=>	"смена пароля"
		);

function CheckPostRequest()
{
global $manlix;

	if(!count($_POST))				return 0;
	elseif(!isset($_POST['password']))		return 0;
	elseif(strlen($_POST['password'])==32)	return 0;
	else
	{
	setcookie($manlix['script']['prefix']."password",md5($_POST['password']));
	$_COOKIE[$manlix['script']['prefix']."password"]=md5($_POST['password']);
	return 1;
	}
}

function CheckAdminPassword($password)
{
global $manlix;

$PasswordFile=manlix_read_file("./inc/password.inc.dat");
	if(!isset($password))															return 0;
	elseif(!isset($PasswordFile[0]))														return 0;
	elseif(strlen($password)==32&&isset($_COOKIE[$manlix['script']['prefix']."password"])&&$_COOKIE[$manlix['script']['prefix']."password"]==$PasswordFile[0])	return 1;
	elseif($password==$PasswordFile[0])														return 1;
	else																	return 0;
}

if(CheckPostRequest())				$manlix['access']=CheckAdminPassword($_COOKIE[$manlix['script']['prefix']."password"]);
else						$manlix['access']=CheckAdminPassword((!isset($_COOKIE[$manlix['script']['prefix']."password"]))?null:$_COOKIE[$manlix['script']['prefix']."password"]);


if(empty($manlix['access']))
{
	if(isset($_POST['password']))	$manlix['status']="пароль не опознан, повторите ввод";

$manlix['section']['name']="Вход в управление скриптом";
$manlix['result']='<br><table border="0" align="center" cellspacing="0" cellpadding="1">
<form method="post">
<tr><td align="right"><font face="verdana" size="1" color="maroon">Пароль:</td>	<td><input type="password" name="password" size="30" class="name" onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px" value=""></td></tr>
<tr><td height="10"></td></tr>
<tr><td align="right" colspan="2">
				<table border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
				<tr><td><input type="submit" value="Выполнить вход" class="submit" style="width: 163px"></td></tr>
				</table>
</td></tr>
<tr><td height="20"></td></tr>
</form>
</table>';
}

else
{
$manlix['status']="Вход выполнен";
$manlix['result']=(!isset($manlix['result']))?null:$manlix['result'];

$manlix['section']['name']="Выберите нужное Вам действие";

$manlix['result'].="<table border=0 align=center>";
	while(list($a,$b)=each($manlix['sections']))
	$manlix['result'].="<tr><td><a href='?section=".(($a+1)*2*3*4*5*6*7*8*90)."'><font face=verdana size=1>".ucfirst($b)."</a></td></tr>";
$manlix['result'].="</table>";

$manlix['result'].="</td></tr><tr><tr><td bgcolor=maroon colspan=2></td></tr><tr><td colspan=2 bgcolor=#faedcf>";

	if(empty($section)) $manlix['result'].="<center><br><font face=verdana size=1 color=green>сделайте выбор</font></br><br></center>";
	elseif(!isset($manlix['sections'][($section-1)/2/3/4/5/6/7/8/90])) $manlix['result'].="<br><center><font face=verdana size=1 color=#de0000>Выбраный Вами раздел не существует</font></cebter><br><br>";
	else
	{
	$manlix['status'].=" <font color=blue>»</font> <font color=green>".$manlix['section']['name']=ucfirst($manlix['sections'][$case=floor(($section-1)/2/3/4/5/6/7/8/90)])."</font>";
	$manlix['result'].="<table border=0 width=98% align=center><tr><td><font face=verdana size=1>";

		switch($case)
		{
		case "10":
				if(empty($_FILES))
				$manlix['result'].=<<<HTML
<br><i><font face=verdana color=#de0000>Обратите внимание! </font><br><i><ul type=square><li>Размер загружаемого графического файла по ширине должен быть 88 пикселей, а по высоте 31 пиксель. Формат PNG.</li><li>Загружённый Вами файл автоматически заменит основную картинку счётчика.</li></ul></i>
<br>
<form method=post enctype="multipart/form-data">
Графический файл: <input type=file name=counter size=52 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 346px">
<br><br>
	<center>
	<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000>
	<tr><td><input type=submit value=Загрузить class=submit style="width: 70px"></td></tr>
	</table>
	</center>
</form>
HTML;
				else
				{
					if(!isset($_FILES['counter']['size']))					$manlix['result'].="<br><center><font color=#de0000>Загружаемый Вами файл не должен быть пустым.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					elseif($_FILES['counter']['error']==1)					$manlix['result'].="<br><center><font color=#de0000>Загружаемый Вами файл превышает лимит, этот лимит узнайте у своего админа, а я рекомендую загрузить файл объёмом поменьше.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					elseif($_FILES['counter']['size']>102400)				$manlix['result'].="<br><center><font color=#de0000>Графический файл не должен превышать 100 килобайт.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					elseif($_FILES['counter']['error']==3)					$manlix['result'].="<br><center><font color=#de0000>Ваш файл загружен частично, настоятельно рекомендую повторить загрузку.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					elseif($_FILES['counter']['error']==4)					$manlix['result'].="<br><center><font color=#de0000>Вы не выбрали файл для загрузки.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					elseif(!eregi("png$",$_FILES['counter']['type']))				$manlix['result'].="<br><center><font color=#de0000>Графический файл должен быть в формате PNG.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					elseif(!is_array($sizes=getimagesize($_FILES['counter']['tmp_name'])))	$manlix['result'].="<br><center><font color=#de0000>Нет возможности прочитать размеры файла.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					elseif($sizes[0]!=88)						$manlix['result'].="<br><center><font color=#de0000>Ширина графического файла обязательно должна равняться 88 пикселям.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					elseif($sizes[1]!=31)						$manlix['result'].="<br><center><font color=#de0000>Высота графического файла обязательно должна равняться 31 пикселям.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					else
					{
						if(copy($_FILES['counter']['tmp_name'],"./images/counter.png"))
						{
						$manlix['okay']=1;
						$manlix['result'].="<br><center><font color=green>Новая картинка для счётчика успешно внесена в базу.</font><br><br></center>";
						}

						else $manlix['result'].="<br><center><font color=#de0000>Нет прав для замены основной картинки счётчика на новую, обратитесь к администратору.</font><br><br>...<a href='?section=39916800'>вернуться на шаг назад</a><br><br></center>";
					}
				}
			break;
		case "20":
				if(empty($_POST))
				$manlix['result'].=<<<HTML
<form method=post enctype="multipart/form-data">
<br><i><font face=verdana color=#de0000>Обратите внимание! </font><br><i><ul type=square><li>Размер загружаемого графического файла по ширине должен быть 88 пикселей, а по высоте 31 пиксель. Формат PNG.</li><li>Загружённый Вами файл автоматически будет заменять картинку счётчика при N количетво посетителей.</li></ul></i>
Если переменная: <ul type=square>
<li><input type=radio name=case value=1>посещений за всё время (верх счётчика)</li>
<li><input type=radio name=case value=2>посетителей сегодня (середина счётчика)</li>
<li><input type=radio name=case value=3>уникальный посетителей сегодня (низ счётчика)</li>
</ul>
равна: <input type=text name=amount size=3 class=name onfocus="id=className" onblur="id=''"">, то заменять картинку счётчика на...<br><br>
Графический файл: <input type=file name=counter size=52 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 346px">
<br><br>
	<center>
	<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000>
	<tr><td><input type=submit value=Загрузить class=submit style="width: 70px"></td></tr>
	</table>
	</center>
</form>
HTML;

			else
			{
				if(empty($_POST['case']))						$manlix['result'].="<br><center><font color=#de0000>Вы не выбрали название переменной.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif(empty($_POST['amount']))					$manlix['result'].="<br><center><font color=#de0000>Вы не ввели значение, при котором счётчик будет менять свою картинку.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif(!is_numeric($_POST['amount']))					$manlix['result'].="<br><center><font color=#de0000>Значение, при котором счётчик будет менять свою картинку, должно быть цифрой (например: 2) или числом (например: 10000).</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif(!isset($_FILES['counter']['size']))				$manlix['result'].="<br><center><font color=#de0000>Загружаемый Вами файл не должен быть пустым.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif($_FILES['counter']['error']==1)					$manlix['result'].="<br><center><font color=#de0000>Загружаемый Вами файл превышает лимит, этот лимит узнайте у своего админа, а я рекомендую загрузить файл объёмом поменьше.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif($_FILES['counter']['size']>102400)				$manlix['result'].="<br><center><font color=#de0000>Графический файл не должен превышать 100 килобайт.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif($_FILES['counter']['error']==3)					$manlix['result'].="<br><center><font color=#de0000>Ваш файл загружен частично, настоятельно рекомендую повторить загрузку.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif($_FILES['counter']['error']==4)					$manlix['result'].="<br><center><font color=#de0000>Вы не выбрали файл для загрузки.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif(!eregi("png$",$_FILES['counter']['type']))				$manlix['result'].="<br><center><font color=#de0000>Графический файл должен быть в формате PNG.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif(!is_array($sizes=getimagesize($_FILES['counter']['tmp_name'])))	$manlix['result'].="<br><center><font color=#de0000>Нет возможности прочитать размеры файла.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif($sizes[0]!=88)						$manlix['result'].="<br><center><font color=#de0000>Ширина графического файла обязательно должна равняться 88 пикселям.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				elseif($sizes[1]!=31)						$manlix['result'].="<br><center><font color=#de0000>Высота графического файла обязательно должна равняться 31 пикселям.</font><br><br>...<a href='?section=76204800'>вернуться на шаг назад</a><br><br></center>";
				else
				{
					switch($_POST['case'])
					{
					case "2":	$_POST['case']="AllToday"; break;
					case "3":	$_POST['case']="UniqueToday"; break;
					default:	$_POST['case']="All";
					}

					if(copy($_FILES['counter']['tmp_name'],"./images/".$_POST['case']."/".$_POST['amount'].".png"))
					{
					$manlix['okay']=1;
					$manlix['result'].="<br><center><font color=green>Новая картинка для счётчика успешно внесена в базу.</font><br><br></center>";
					}

					else $manlix['result'].="<br><center><font color=#de0000>Нет прав для копирования картинки счётчика, обратитесь к администратору.</font><br><br>...<a href='?section=6204800'>вернуться на шаг назад</a><br><br></center>";

				}
			}
			break;
		case "30":
				if(empty($_POST))
				$manlix['result'].=<<<HTML
<center><br><i><font face=verdana color=#de0000>Обратите внимание на таблицу цветов в RGB:</font></i><br><br></center>
<script language="javascript" type="text/javascript">
<!--
function ShowColor(color)
{
alert(color)
}
-->
</script>
<script language="javascript" type="text/javascript">
<!--
function ShowColor(color)
{
alert(color)
}
-->
</script>
<table border=0 align=center cellspacing=1 cellpadding=0 bgcolor=000000 title="Таблица цветов">
<td colspan=2 bgcolor=#000000 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 0\\nB: 0")'><font face=verdana size=1>R: 0<br>G: 0<br>B: 0</font></td>
<td bgcolor=#000080 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 0\\nB: 128")'><font face=verdana size=1>R: 0<br>G: 0<br>B: 128</font></td>
<td bgcolor=#00008b width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 0\\nB: 139")'><font face=verdana size=1>R: 0<br>G: 0<br>B: 139</font></td>
<td bgcolor=#0000cd width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 0\\nB: 205")'><font face=verdana size=1>R: 0<br>G: 0<br>B: 205</font></td>
<td bgcolor=#0000ff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 0\\nB: 255")'><font face=verdana size=1>R: 0<br>G: 0<br>B: 255</font></td>
<td bgcolor=#006400 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 100\\nB: 0")'><font face=verdana size=1>R: 0<br>G: 100<br>B: 0</font></td>
<td bgcolor=#008000 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 128\\nB: 0")'><font face=verdana size=1>R: 0<br>G: 128<br>B: 0</font></td>
<td bgcolor=#008080 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 128\\nB: 128")'><font face=verdana size=1>R: 0<br>G: 128<br>B: 128</font></td>
<td bgcolor=#008b8b width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 139\\nB: 139")'><font face=verdana size=1>R: 0<br>G: 139<br>B: 139</font></td>
</tr>
<tr>
<td bgcolor=#00bfff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 191\\nB: 255")'><font face=verdana size=1>R: 0<br>G: 191<br>B: 255</font></td>
<td bgcolor=#00ced1 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 206\\nB: 209")'><font face=verdana size=1>R: 0<br>G: 206<br>B: 209</font></td>
<td bgcolor=#00fa9a width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 250\\nB: 154")'><font face=verdana size=1>R: 0<br>G: 250<br>B: 154</font></td>
<td bgcolor=#00ff00 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 255\\nB: 0")'><font face=verdana size=1>R: 0<br>G: 255<br>B: 0</font></td>
<td bgcolor=#00ff7f width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 255\\nB: 127")'><font face=verdana size=1>R: 0<br>G: 255<br>B: 127</font></td>
<td bgcolor=#00ffff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 255\\nB: 255")'><font face=verdana size=1>R: 0<br>G: 255<br>B: 255</font></td>
<td bgcolor=#00ffff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 0\\nG: 255\\nB: 255")'><font face=verdana size=1>R: 0<br>G: 255<br>B: 255</font></td>
<td bgcolor=#191970 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 25\\nG: 25\\nB: 112")'><font face=verdana size=1>R: 25<br>G: 25<br>B: 112</font></td>
<td bgcolor=#1e90ff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 30\\nG: 144\\nB: 255")'><font face=verdana size=1>R: 30<br>G: 144<br>B: 255</font></td>
<td bgcolor=#20b2aa width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 32\\nG: 178\\nB: 170")'><font face=verdana size=1>R: 32<br>G: 178<br>B: 170</font></td>
</tr>
<tr>
<td bgcolor=#228b22 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 34\\nG: 139\\nB: 34")'><font face=verdana size=1>R: 34<br>G: 139<br>B: 34</font></td>
<td bgcolor=#2e8b57 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 46\\nG: 139\\nB: 87")'><font face=verdana size=1>R: 46<br>G: 139<br>B: 87</font></td>
<td bgcolor=#2f4f4f width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 47\\nG: 79\\nB: 79")'><font face=verdana size=1>R: 47<br>G: 79<br>B: 79</font></td>
<td bgcolor=#32cd32 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 50\\nG: 205\\nB: 50")'><font face=verdana size=1>R: 50<br>G: 205<br>B: 50</font></td>
<td bgcolor=#3cb371 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 60\\nG: 179\\nB: 113")'><font face=verdana size=1>R: 60<br>G: 179<br>B: 113</font></td>
<td bgcolor=#40e0d0 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 64\\nG: 224\\nB: 208")'><font face=verdana size=1>R: 64<br>G: 224<br>B: 208</font></td>
<td bgcolor=#4169e1 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 65\\nG: 105\\nB: 225")'><font face=verdana size=1>R: 65<br>G: 105<br>B: 225</font></td>
<td bgcolor=#4682b4 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 70\\nG: 130\\nB: 180")'><font face=verdana size=1>R: 70<br>G: 130<br>B: 180</font></td>
<td bgcolor=#483d8b width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 72\\nG: 61\\nB: 139")'><font face=verdana size=1>R: 72<br>G: 61<br>B: 139</font></td>
<td bgcolor=#48d1cc width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 72\\nG: 209\\nB: 204")'><font face=verdana size=1>R: 72<br>G: 209<br>B: 204</font></td>
</tr>
<tr>
<td bgcolor=#4b0082 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 75\\nG: 0\\nB: 130")'><font face=verdana size=1>R: 75<br>G: 0<br>B: 130</font></td>
<td bgcolor=#556b2f width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 85\\nG: 107\\nB: 47")'><font face=verdana size=1>R: 85<br>G: 107<br>B: 47</font></td>
<td bgcolor=#5f9ea0 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 95\\nG: 158\\nB: 160")'><font face=verdana size=1>R: 95<br>G: 158<br>B: 160</font></td>
<td bgcolor=#6495ed width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 100\\nG: 149\\nB: 237")'><font face=verdana size=1>R: 100<br>G: 149<br>B: 237</font></td>
<td bgcolor=#66cdaa width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 102\\nG: 205\\nB: 170")'><font face=verdana size=1>R: 102<br>G: 205<br>B: 170</font></td>
<td bgcolor=#696969 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 105\\nG: 105\\nB: 105")'><font face=verdana size=1>R: 105<br>G: 105<br>B: 105</font></td>
<td bgcolor=#6a5acd width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 106\\nG: 90\\nB: 205")'><font face=verdana size=1>R: 106<br>G: 90<br>B: 205</font></td>
<td bgcolor=#6b8e23 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 107\\nG: 142\\nB: 35")'><font face=verdana size=1>R: 107<br>G: 142<br>B: 35</font></td>
<td bgcolor=#708090 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 112\\nG: 128\\nB: 144")'><font face=verdana size=1>R: 112<br>G: 128<br>B: 144</font></td>
<td bgcolor=#778899 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 119\\nG: 136\\nB: 153")'><font face=verdana size=1>R: 119<br>G: 136<br>B: 153</font></td>
</tr>
<tr>
<td bgcolor=#7b68ee width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 123\\nG: 104\\nB: 238")'><font face=verdana size=1>R: 123<br>G: 104<br>B: 238</font></td>
<td bgcolor=#7cfc00 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 124\\nG: 252\\nB: 0")'><font face=verdana size=1>R: 124<br>G: 252<br>B: 0</font></td>
<td bgcolor=#7fff00 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 127\\nG: 255\\nB: 0")'><font face=verdana size=1>R: 127<br>G: 255<br>B: 0</font></td>
<td bgcolor=#7fffd4 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 127\\nG: 255\\nB: 212")'><font face=verdana size=1>R: 127<br>G: 255<br>B: 212</font></td>
<td bgcolor=#800000 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 128\\nG: 0\\nB: 0")'><font face=verdana size=1>R: 128<br>G: 0<br>B: 0</font></td>
<td bgcolor=#800080 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 128\\nG: 0\\nB: 128")'><font face=verdana size=1>R: 128<br>G: 0<br>B: 128</font></td>
<td bgcolor=#808000 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 128\\nG: 128\\nB: 0")'><font face=verdana size=1>R: 128<br>G: 128<br>B: 0</font></td>
<td bgcolor=#808080 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 128\\nG: 128\\nB: 128")'><font face=verdana size=1>R: 128<br>G: 128<br>B: 128</font></td>
<td bgcolor=#87ceeb width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 135\\nG: 206\\nB: 235")'><font face=verdana size=1>R: 135<br>G: 206<br>B: 235</font></td>
<td bgcolor=#87cefa width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 135\\nG: 206\\nB: 250")'><font face=verdana size=1>R: 135<br>G: 206<br>B: 250</font></td>
</tr>
<tr>
<td bgcolor=#8a2be2 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 138\\nG: 43\\nB: 226")'><font face=verdana size=1>R: 138<br>G: 43<br>B: 226</font></td>
<td bgcolor=#8b0000 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 139\\nG: 0\\nB: 0")'><font face=verdana size=1>R: 139<br>G: 0<br>B: 0</font></td>
<td bgcolor=#8b008b width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 139\\nG: 0\\nB: 139")'><font face=verdana size=1>R: 139<br>G: 0<br>B: 139</font></td>
<td bgcolor=#8b4513 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 139\\nG: 69\\nB: 19")'><font face=verdana size=1>R: 139<br>G: 69<br>B: 19</font></td>
<td bgcolor=#8fbc8f width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 143\\nG: 188\\nB: 143")'><font face=verdana size=1>R: 143<br>G: 188<br>B: 143</font></td>
<td bgcolor=#90ee90 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 144\\nG: 238\\nB: 144")'><font face=verdana size=1>R: 144<br>G: 238<br>B: 144</font></td>
<td bgcolor=#9370db width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 147\\nG: 112\\nB: 219")'><font face=verdana size=1>R: 147<br>G: 112<br>B: 219</font></td>
<td bgcolor=#9400d3 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 148\\nG: 0\\nB: 211")'><font face=verdana size=1>R: 148<br>G: 0<br>B: 211</font></td>
<td bgcolor=#98fb98 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 152\\nG: 251\\nB: 152")'><font face=verdana size=1>R: 152<br>G: 251<br>B: 152</font></td>
<td bgcolor=#9932cc width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 153\\nG: 50\\nB: 204")'><font face=verdana size=1>R: 153<br>G: 50<br>B: 204</font></td>
</tr>
<tr>
<td bgcolor=#a0522d width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 160\\nG: 82\\nB: 45")'><font face=verdana size=1>R: 160<br>G: 82<br>B: 45</font></td>
<td bgcolor=#a52a2a width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 165\\nG: 42\\nB: 42")'><font face=verdana size=1>R: 165<br>G: 42<br>B: 42</font></td>
<td bgcolor=#a9a9a9 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 169\\nG: 169\\nB: 169")'><font face=verdana size=1>R: 169<br>G: 169<br>B: 169</font></td>
<td bgcolor=#add8e6 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 173\\nG: 216\\nB: 230")'><font face=verdana size=1>R: 173<br>G: 216<br>B: 230</font></td>
<td bgcolor=#adff2f width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 173\\nG: 255\\nB: 47")'><font face=verdana size=1>R: 173<br>G: 255<br>B: 47</font></td>
<td bgcolor=#afeeee width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 175\\nG: 238\\nB: 238")'><font face=verdana size=1>R: 175<br>G: 238<br>B: 238</font></td>
<td bgcolor=#b0c4de width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 176\\nG: 196\\nB: 222")'><font face=verdana size=1>R: 176<br>G: 196<br>B: 222</font></td>
<td bgcolor=#b0e0e6 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 176\\nG: 224\\nB: 230")'><font face=verdana size=1>R: 176<br>G: 224<br>B: 230</font></td>
<td bgcolor=#b22222 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 178\\nG: 34\\nB: 34")'><font face=verdana size=1>R: 178<br>G: 34<br>B: 34</font></td>
<td bgcolor=#b8860b width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 184\\nG: 134\\nB: 11")'><font face=verdana size=1>R: 184<br>G: 134<br>B: 11</font></td>
</tr>
<tr>
<td bgcolor=#ba55d3 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 186\\nG: 85\\nB: 211")'><font face=verdana size=1>R: 186<br>G: 85<br>B: 211</font></td>
<td bgcolor=#bc8f8f width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 188\\nG: 143\\nB: 143")'><font face=verdana size=1>R: 188<br>G: 143<br>B: 143</font></td>
<td bgcolor=#bdb76b width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 189\\nG: 183\\nB: 107")'><font face=verdana size=1>R: 189<br>G: 183<br>B: 107</font></td>
<td bgcolor=#c0c0c0 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 192\\nG: 192\\nB: 192")'><font face=verdana size=1>R: 192<br>G: 192<br>B: 192</font></td>
<td bgcolor=#c71585 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 199\\nG: 21\\nB: 133")'><font face=verdana size=1>R: 199<br>G: 21<br>B: 133</font></td>
<td bgcolor=#cd5c5c width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 205\\nG: 92\\nB: 92")'><font face=verdana size=1>R: 205<br>G: 92<br>B: 92</font></td>
<td bgcolor=#cd853f width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 205\\nG: 133\\nB: 63")'><font face=verdana size=1>R: 205<br>G: 133<br>B: 63</font></td>
<td bgcolor=#d2691e width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 210\\nG: 105\\nB: 30")'><font face=verdana size=1>R: 210<br>G: 105<br>B: 30</font></td>
<td bgcolor=#d2b48c width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 210\\nG: 180\\nB: 140")'><font face=verdana size=1>R: 210<br>G: 180<br>B: 140</font></td>
<td bgcolor=#d3d3d3 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 211\\nG: 211\\nB: 211")'><font face=verdana size=1>R: 211<br>G: 211<br>B: 211</font></td>
</tr>
<tr>
<td bgcolor=#d8bfd8 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 216\\nG: 191\\nB: 216")'><font face=verdana size=1>R: 216<br>G: 191<br>B: 216</font></td>
<td bgcolor=#da70d6 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 218\\nG: 112\\nB: 214")'><font face=verdana size=1>R: 218<br>G: 112<br>B: 214</font></td>
<td bgcolor=#daa520 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 218\\nG: 165\\nB: 32")'><font face=verdana size=1>R: 218<br>G: 165<br>B: 32</font></td>
<td bgcolor=#db7093 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 219\\nG: 112\\nB: 147")'><font face=verdana size=1>R: 219<br>G: 112<br>B: 147</font></td>
<td bgcolor=#dc143c width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 220\\nG: 20\\nB: 60")'><font face=verdana size=1>R: 220<br>G: 20<br>B: 60</font></td>
<td bgcolor=#dcdcdc width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 220\\nG: 220\\nB: 220")'><font face=verdana size=1>R: 220<br>G: 220<br>B: 220</font></td>
<td bgcolor=#dda0dd width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 221\\nG: 160\\nB: 221")'><font face=verdana size=1>R: 221<br>G: 160<br>B: 221</font></td>
<td bgcolor=#deb887 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 222\\nG: 184\\nB: 135")'><font face=verdana size=1>R: 222<br>G: 184<br>B: 135</font></td>
<td bgcolor=#e0ffff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 224\\nG: 255\\nB: 255")'><font face=verdana size=1>R: 224<br>G: 255<br>B: 255</font></td>
<td bgcolor=#e6e6fa width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 230\\nG: 230\\nB: 250")'><font face=verdana size=1>R: 230<br>G: 230<br>B: 250</font></td>
</tr>
<tr>
<td bgcolor=#e9967a width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 233\\nG: 150\\nB: 122")'><font face=verdana size=1>R: 233<br>G: 150<br>B: 122</font></td>
<td bgcolor=#ee82ee width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 238\\nG: 130\\nB: 238")'><font face=verdana size=1>R: 238<br>G: 130<br>B: 238</font></td>
<td bgcolor=#eee8aa width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 238\\nG: 232\\nB: 170")'><font face=verdana size=1>R: 238<br>G: 232<br>B: 170</font></td>
<td bgcolor=#f08080 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 240\\nG: 128\\nB: 128")'><font face=verdana size=1>R: 240<br>G: 128<br>B: 128</font></td>
<td bgcolor=#f0e68c width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 240\\nG: 230\\nB: 140")'><font face=verdana size=1>R: 240<br>G: 230<br>B: 140</font></td>
<td bgcolor=#f0f8ff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 240\\nG: 248\\nB: 255")'><font face=verdana size=1>R: 240<br>G: 248<br>B: 255</font></td>
<td bgcolor=#f0fff0 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 240\\nG: 255\\nB: 240")'><font face=verdana size=1>R: 240<br>G: 255<br>B: 240</font></td>
<td bgcolor=#f0ffff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 240\\nG: 255\\nB: 255")'><font face=verdana size=1>R: 240<br>G: 255<br>B: 255</font></td>
<td bgcolor=#f4a460 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 244\\nG: 164\\nB: 96")'><font face=verdana size=1>R: 244<br>G: 164<br>B: 96</font></td>
<td bgcolor=#f5deb3 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 245\\nG: 222\\nB: 179")'><font face=verdana size=1>R: 245<br>G: 222<br>B: 179</font></td>
</tr>
<tr>
<td bgcolor=#f5f5dc width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 245\\nG: 245\\nB: 220")'><font face=verdana size=1>R: 245<br>G: 245<br>B: 220</font></td>
<td bgcolor=#f5f5f5 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 245\\nG: 245\\nB: 245")'><font face=verdana size=1>R: 245<br>G: 245<br>B: 245</font></td>
<td bgcolor=#f5fffa width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 245\\nG: 255\\nB: 250")'><font face=verdana size=1>R: 245<br>G: 255<br>B: 250</font></td>
<td bgcolor=#f8f8ff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 248\\nG: 248\\nB: 255")'><font face=verdana size=1>R: 248<br>G: 248<br>B: 255</font></td>
<td bgcolor=#fa8072 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 250\\nG: 128\\nB: 114")'><font face=verdana size=1>R: 250<br>G: 128<br>B: 114</font></td>
<td bgcolor=#faebd7 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 250\\nG: 235\\nB: 215")'><font face=verdana size=1>R: 250<br>G: 235<br>B: 215</font></td>
<td bgcolor=#faf0e6 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 250\\nG: 240\\nB: 230")'><font face=verdana size=1>R: 250<br>G: 240<br>B: 230</font></td>
<td bgcolor=#fafad2 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 250\\nG: 250\\nB: 210")'><font face=verdana size=1>R: 250<br>G: 250<br>B: 210</font></td>
<td bgcolor=#fdf5e6 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 253\\nG: 245\\nB: 230")'><font face=verdana size=1>R: 253<br>G: 245<br>B: 230</font></td>
<td bgcolor=#ff0000 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 0\\nB: 0")'><font face=verdana size=1>R: 255<br>G: 0<br>B: 0</font></td>
</tr>
<tr>
<td bgcolor=#ff00ff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 0\\nB: 255")'><font face=verdana size=1>R: 255<br>G: 0<br>B: 255</font></td>
<td bgcolor=#ff00ff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 0\\nB: 255")'><font face=verdana size=1>R: 255<br>G: 0<br>B: 255</font></td>
<td bgcolor=#ff1493 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 20\\nB: 147")'><font face=verdana size=1>R: 255<br>G: 20<br>B: 147</font></td>
<td bgcolor=#ff4500 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 69\\nB: 0")'><font face=verdana size=1>R: 255<br>G: 69<br>B: 0</font></td>
<td bgcolor=#ff6347 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 99\\nB: 71")'><font face=verdana size=1>R: 255<br>G: 99<br>B: 71</font></td>
<td bgcolor=#ff69b4 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 105\\nB: 180")'><font face=verdana size=1>R: 255<br>G: 105<br>B: 180</font></td>
<td bgcolor=#ff7f50 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 127\\nB: 80")'><font face=verdana size=1>R: 255<br>G: 127<br>B: 80</font></td>
<td bgcolor=#ff8c00 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 140\\nB: 0")'><font face=verdana size=1>R: 255<br>G: 140<br>B: 0</font></td>
<td bgcolor=#ffa07a width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 160\\nB: 122")'><font face=verdana size=1>R: 255<br>G: 160<br>B: 122</font></td>
<td bgcolor=#ffa500 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 165\\nB: 0")'><font face=verdana size=1>R: 255<br>G: 165<br>B: 0</font></td>
</tr>
<tr>
<td bgcolor=#ffb6c1 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 182\\nB: 193")'><font face=verdana size=1>R: 255<br>G: 182<br>B: 193</font></td>
<td bgcolor=#ffc0cb width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 192\\nB: 203")'><font face=verdana size=1>R: 255<br>G: 192<br>B: 203</font></td>
<td bgcolor=#ffd700 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 215\\nB: 0")'><font face=verdana size=1>R: 255<br>G: 215<br>B: 0</font></td>
<td bgcolor=#ffdab9 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 218\\nB: 185")'><font face=verdana size=1>R: 255<br>G: 218<br>B: 185</font></td>
<td bgcolor=#ffdead width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 222\\nB: 173")'><font face=verdana size=1>R: 255<br>G: 222<br>B: 173</font></td>
<td bgcolor=#ffe4b5 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 228\\nB: 181")'><font face=verdana size=1>R: 255<br>G: 228<br>B: 181</font></td>
<td bgcolor=#ffe4c4 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 228\\nB: 196")'><font face=verdana size=1>R: 255<br>G: 228<br>B: 196</font></td>
<td bgcolor=#ffe4e1 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 228\\nB: 225")'><font face=verdana size=1>R: 255<br>G: 228<br>B: 225</font></td>
<td bgcolor=#ffebcd width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 235\\nB: 205")'><font face=verdana size=1>R: 255<br>G: 235<br>B: 205</font></td>
<td bgcolor=#ffefd5 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 239\\nB: 213")'><font face=verdana size=1>R: 255<br>G: 239<br>B: 213</font></td>
</tr>
<tr>
<td bgcolor=#fff0f5 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 240\\nB: 245")'><font face=verdana size=1>R: 255<br>G: 240<br>B: 245</font></td>
<td bgcolor=#fff5ee width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 245\\nB: 238")'><font face=verdana size=1>R: 255<br>G: 245<br>B: 238</font></td>
<td bgcolor=#fff8dc width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 248\\nB: 220")'><font face=verdana size=1>R: 255<br>G: 248<br>B: 220</font></td>
<td bgcolor=#fffacd width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 250\\nB: 205")'><font face=verdana size=1>R: 255<br>G: 250<br>B: 205</font></td>
<td bgcolor=#fffaf0 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 250\\nB: 240")'><font face=verdana size=1>R: 255<br>G: 250<br>B: 240</font></td>
<td bgcolor=#fffafa width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 250\\nB: 250")'><font face=verdana size=1>R: 255<br>G: 250<br>B: 250</font></td>
<td bgcolor=#ffff00 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 255\\nB: 0")'><font face=verdana size=1>R: 255<br>G: 255<br>B: 0</font></td>
<td bgcolor=#ffffe0 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 255\\nB: 224")'><font face=verdana size=1>R: 255<br>G: 255<br>B: 224</font></td>
<td bgcolor=#fffff0 width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 255\\nB: 240")'><font face=verdana size=1>R: 255<br>G: 255<br>B: 240</font></td>
<td bgcolor=#ffffff width=40 style='cursor: hand' onclick='javascript:ShowColor("R: 255\\nG: 255\\nB: 255")'><font face=verdana size=1>R: 255<br>G: 255<br>B: 255</font></td>
</table>
<br>
<form method=post>
<table border=0 align=center cellspacing=0 cellpadding=0>
<tr><td>
	<table border=0>
	<caption><font face=verdana size=1>Верхние цифры</font></caption>
	<tr><td><font face=verdana size=1 color=red>R</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=1 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td>
	<td><font face=verdana size=1 color=green>G</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=11 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td>
	<td><font face=verdana size=1 color=blue>B</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=111 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td></tr>
	</table><br>
</td></tr>
<tr><td>
	<table border=0>
	<caption><font face=verdana size=1>Центральные цифры</font></caption>
	<tr><td><font face=verdana size=1 color=red>R</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=2 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td>
	<td><font face=verdana size=1 color=green>G</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=22 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td>
	<td><font face=verdana size=1 color=blue>B</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=222 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td></tr>
	</table><br>
</td></tr>
<tr><td>
	<table border=0>
	<caption><font face=verdana size=1>Нижние цифры</font></caption>
	<tr><td><font face=verdana size=1 color=red>R</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=3 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td>
	<td><font face=verdana size=1 color=green>G</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=33 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td>
	<td><font face=verdana size=1 color=blue>B</font><font face=verdana size=1 color=maroon>:</td><td><input type=text name=333 size=3 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 22px" maxlength=3></td></tr>
	</table>
</td></tr>
</table>

<br><br>
	<center>
	<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000>
	<tr><td><input type=submit value="Установить заданные цвета" class=submit style="width: 166px"></td></tr>
	</table>
	</center>
</form>
HTML;
				else
				{
					while(list($key,$value)=each($_POST))
					{
					$colors[$key]=$value;
						if(!isset($value)||!is_numeric($value)||strlen($value)>3||strlen($value)<0||$value>255||$value<0)	$error=1;
					}

					if(!isset($error))
					{
					$manlix['okay']=1;

						$OpenConfigFile=fopen("./inc/config.inc.dat","w");
						flock($OpenConfigFile,1);
						flock($OpenConfigFile,2);
						fwrite($OpenConfigFile,"[colors]".chr(13).chr(10));
							while(list($key,$value)=each($colors))
							fwrite($OpenConfigFile,$key."=".$value.chr(13).chr(10));

							fwrite($OpenConfigFile,	chr(13).chr(10)."[script]".chr(13).chr(10).
										"name=\"".$manlix['script']['name']."\";".chr(13).chr(10).
										"prefix=\"".$manlix['script']['prefix']."\";".chr(13).chr(10).
										"russian=\"".$manlix['script']['russian']."\";".chr(13).chr(10).
										"version=\"".$manlix['script']['version']."\";");
						fclose($OpenConfigFile);

					$manlix['result'].="<br><center><font color=green>Изменения успешно внесены в базу.</font><br><br></center>";
					}

					else $manlix['result'].="<br><center><font color=#de0000>Не верно введены значения для RGB,<br>каждый оттенок должен быть от 0 до  255.</font><br><br>...<a href='?section=112492800'>вернуться на шаг назад</a><br><br></center>";
				}
			break;

		case "40":
				if(empty($_POST))
				$manlix['result'].=<<<HTML
<br><i><font face=verdana color=#de0000>Будьте внимательны!</font><br><i><ul type=square><li>После изменения пароля старый действовать больше не будет.</li><li>Для того чтобы не забыть новый пароль, запишите его где-нибудь.</li><li>В пароль могут входить: русские, латинский буквы и цифры.</li><li>Учитывается регистр.</li></ul></i>
<br>
<form method=post>
<center>Новый пароль: <input type=password name=NewPassword size=52 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 346px"></center>
<br><br>
	<center>
	<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000>
	<tr><td><input type=submit value=Применить class=submit style="width: 70px"></td></tr>
	</table>
	</center>
</form>
HTML;

				else
				{
					if(empty($_POST['NewPassword']))			$manlix['result'].="<br><center><font color=#de0000>Вы не ввели новый пароль.</font><br><br>...<a href='?section=148780800'>вернуться на шаг назад</a><br><br></center>";
					elseif(!eregi("^[a-zа-яё0-9]+$",$_POST['NewPassword']))	$manlix['result'].="<br><center><font color=#de0000>Пароль должен состоять, только из русских, латинский букв и цифр.</font><br><br>...<a href='?section=148780800'>вернуться на шаг назад</a><br><br></center>";
					else
					{
					$manlix['okay']=1;

						$OpenPasswordFile=fopen("./inc/password.inc.dat","w");
						flock($OpenPasswordFile,1);
						flock($OpenPasswordFile,2);
						fwrite($OpenPasswordFile,md5($_POST['NewPassword']));
						fclose($OpenPasswordFile);

						setcookie($manlix['script']['prefix']."password",md5($_POST['NewPassword']));

					$manlix['result'].="<br><center><font color=green>Новый пароль успешно внесён в базу.</font><br><br></center>";
					}
				}
			break;
		}

	$manlix['result'].="</font></td></tr></table>";
	}
}

if(empty($manlix['status']))			$manlix['status']="вход не выполнен";
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
	<tr align=center bgcolor=#faedca height=44><td><font face=verdana size=6 color=#FAD27D><b><?=$manlix['script']['name']?></i></b></font></td></tr>
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
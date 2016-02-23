<?
function translate($string)
{
$array=array(
	"навигация"	=>	"navigation",

	"кука_имя"	=>	"cookie_name",
	"кука_страничка"	=>	"cookie_homepage",
	"кука_почта"	=>	"cookie_mail",
	"кука_аська"	=>	"cookie_icq",
	"кука_сообщение"	=>	"cookie_message",

	"имя"		=>	"name",
	"сайт"		=>	"homepage",
	"почта"		=>	"mail",
	"ася"		=>	"icq",
	"сообщение"	=>	"message",
	"автор"		=>	"author",
	"ответ"		=>	"answer",

	"чётная"		=>	"even",

	"ошибка"		=>	"error",

	"ВсеСообщения"	=>	"AllMessages"
	);

	while(list($looking,$result)=each($array))
	$string=eregi_replace($looking,$result,$string);

return $string;
}

function parse_template($name)
{
global $manlix;

$file=manlix_read_file($template=$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/".$name);

	while(list($line,$string)=each($file))
	{
		if(eregi("^(если|игнорировать|игнор|#!|\\\\|подключить)[[:space:]]?([_a-z0-9а-яё\.\\/:]+)?[[:space:]]?([_a-z0-9а-яё\.]+)?([[:space:]])?(.*)?",$string,$other))
		{
		$other[2]=translate($other[2]);

			if(eregi("^(игнорировать|игнор|#!|\\\\)$",$other[1]))
			$string=null;

			elseif(eregi("^(подключить)$",$other[1]))
			{
				if(empty($other[3]))
				$error=	"Не указано в каком типе подключать файл <i><font color=green>".
					htmlspecialchars($other[2])."</font></i>, укажите <i><font color=green>".
					"пхп</font></i> или <i><font color=green>текст</font></i><br>Пример: ".
					"<font color=#000000>подключить ".htmlspecialchars($other[2])." пхп".
					"</font><br>Пример: <font color=#000000>подключить ".htmlspecialchars($other[2])." текст</font>";

				elseif(file_exists($other[2]))
				{
					if(!is_readable($other[2]))
					$error="нет прав для чтения файла <i><font color=green>".htmlspecialchars($other[2])."</font></i>";

					elseif($other[3]=="пхп")
					{
					$string=include($other[2]);
					$string=substr($string,0,strlen($string)-1);
					}

					else
					{
					$string=null;
					$OpenFile=fopen($other[2],"r");
					flock($OpenFile,1);
					flock($OpenFile,2);
						while(!feof($OpenFile)) $string.=fgets($OpenFile,1024);
					fclose($OpenFile);
					}
				}

				else
				{
				$string=null;
				$error="файл <i><font color=green>".htmlspecialchars($other[2])."</font></i> - не существует";
				}

				if(!empty($error))
				$string=	"<table border=0>".
					"<tr><td align=right><font face=verdana size=1 color=#de0000><b>Строка:</td>	<td><font face=verdana size=1 color=maroon><b>".($line+1)."</td></tr>".
					"<tr><td align=right><font face=verdana size=1 color=#de0000><b>Файл:</td>	<td><font face=verdana size=1 color=maroon><b>".$template."</td></tr>".
					"<tr><td align=right valign=top><font face=verdana size=1 color=#de0000><b>Ошибка:</td>	<td><font face=verdana size=1 color=maroon><b>".$error."</td></tr>".
					"</table>";
			}

			elseif(eregi("^(пустая|пуста|пустое|пусты|пусто|пуст)$",$other[3]))
			{
				if(empty($manlix['other'][$other[2]]))
				$string=$other[5];

				else
				$string=null;
			}

			elseif(eregi("^(есть|существует|определён|определен|определенно|определена|определены)$",$other[3]))
			{
				if(!empty($manlix['other'][$other[2]]))
				$string=$other[5];

				else
				$string=null;
			}

			else
			$string=$other[5];
		}

	if(isset($string))
	echo (!empty($manlix['other']['time']))?replace_time(replace($string),$manlix['other']['time']):replace($string);

	flush();
	}
}
?>
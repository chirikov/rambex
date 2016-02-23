<?
function translate($string)
{
$array=array(
	"���������"	=>	"navigation",

	"����_���"	=>	"cookie_name",
	"����_���������"	=>	"cookie_homepage",
	"����_�����"	=>	"cookie_mail",
	"����_�����"	=>	"cookie_icq",
	"����_���������"	=>	"cookie_message",

	"���"		=>	"name",
	"����"		=>	"homepage",
	"�����"		=>	"mail",
	"���"		=>	"icq",
	"���������"	=>	"message",
	"�����"		=>	"author",
	"�����"		=>	"answer",

	"������"		=>	"even",

	"������"		=>	"error",

	"������������"	=>	"AllMessages"
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
		if(eregi("^(����|������������|�����|#!|\\\\|����������)[[:space:]]?([_a-z0-9�-��\.\\/:]+)?[[:space:]]?([_a-z0-9�-��\.]+)?([[:space:]])?(.*)?",$string,$other))
		{
		$other[2]=translate($other[2]);

			if(eregi("^(������������|�����|#!|\\\\)$",$other[1]))
			$string=null;

			elseif(eregi("^(����������)$",$other[1]))
			{
				if(empty($other[3]))
				$error=	"�� ������� � ����� ���� ���������� ���� <i><font color=green>".
					htmlspecialchars($other[2])."</font></i>, ������� <i><font color=green>".
					"���</font></i> ��� <i><font color=green>�����</font></i><br>������: ".
					"<font color=#000000>���������� ".htmlspecialchars($other[2])." ���".
					"</font><br>������: <font color=#000000>���������� ".htmlspecialchars($other[2])." �����</font>";

				elseif(file_exists($other[2]))
				{
					if(!is_readable($other[2]))
					$error="��� ���� ��� ������ ����� <i><font color=green>".htmlspecialchars($other[2])."</font></i>";

					elseif($other[3]=="���")
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
				$error="���� <i><font color=green>".htmlspecialchars($other[2])."</font></i> - �� ����������";
				}

				if(!empty($error))
				$string=	"<table border=0>".
					"<tr><td align=right><font face=verdana size=1 color=#de0000><b>������:</td>	<td><font face=verdana size=1 color=maroon><b>".($line+1)."</td></tr>".
					"<tr><td align=right><font face=verdana size=1 color=#de0000><b>����:</td>	<td><font face=verdana size=1 color=maroon><b>".$template."</td></tr>".
					"<tr><td align=right valign=top><font face=verdana size=1 color=#de0000><b>������:</td>	<td><font face=verdana size=1 color=maroon><b>".$error."</td></tr>".
					"</table>";
			}

			elseif(eregi("^(������|�����|������|�����|�����|����)$",$other[3]))
			{
				if(empty($manlix['other'][$other[2]]))
				$string=$other[5];

				else
				$string=null;
			}

			elseif(eregi("^(����|����������|��������|���������|�����������|����������|����������)$",$other[3]))
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
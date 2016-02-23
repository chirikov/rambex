<?

#	.................................................................................
#
#		������:	Manlix Guestbook, ������: 1.4
#		�����:	Manlix (http://manlix.ru)
#	.................................................................................

if(phpversion()<4.1) exit("<font face='verdana' size='1' color='#de0000'><b>������ PHP �������������� ������ ���� 4.1.0 ��� ����, �� ����� �� ���� (���� ������ ��������������: ".phpversion().")</b></font>");

function error($error,$file){exit('<font face="verdana" size="1" color="#de0000"><b>'.$error.'<br>['.htmlspecialchars($file).']</b></font>');}

if(!set_time_limit(0)) error("�������� ���� <font color=green>".__FILE__."</font> � ������� � �� <font color=green>".__LINE__."</font> �������",date("����: d.m.Y. �����: H:i:s",time()));

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
			error("� ����� ���������� �������, � ������ � ������ <font color=green>".$section."</font>, ���� ���� <font color=green>".$key."</font>",$conf['dir']['path']."/".$conf['dir']['inc']."/config.inc.dat");
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
					if(!is_readable($dir."/".$file))		error("��� ���� ��� ������ ������� �����",$dir."/".$file);
					elseif(!is_writeable($dir."/".$file))	error("��� ���� ��� ������ � ������� �����",$dir."/".$file);
					else				read_dir($dir."/".$file);
				}

				else
				{
					if(!is_readable($dir."/".$file))		error("��� ���� ��� ������ �����",$dir."/".$file);
					elseif(!is_writeable($dir."/".$file))	error("��� ���� ��� ������ � ����",$dir."/".$file);
				}
			}
		}
}

if(!is_readable("./inc"))		error("��� ���� ��� ������ ������� �����","./inc");
elseif(!is_writeable("./inc"))		error("��� ���� ��� ������ � ������� �����","./inc");
else				read_dir("./inc");

$manlix=parse_ini_file("./inc/config.inc.dat",1);

CheckConf($manlix);

if(!include($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['functions']))	error("�� ���� ��������� ���� � ���������",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['functions']);

$manlix['sections']=array(
		"���������"		=>	array("����������, ���������, �������� �������","�������������� ���������","�������� ���������"),
		"�����"			=>	array("���������� ������ ������","��������� ���������� � ���� ������� ������","�������� ������"),
		"������"			=>	array("����� �������","��������� ������� � ��� ����������","�������� �������"),
		"���������"		=>	array("��������","���������","�������� ����������� ���� �������","���������� � �������"),
		"���������� (�������)"	=>	array("��������","��������","�������"),
		"�������"		=>	array("��������","��������","�������")
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
	else									error("�� ������ ���� � ������� � �������� /���� �� ����������/",$manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);


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
			$manlix['status']="���� �������� ��� ������������ <font color='blue'>".$login."</font>";
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
					$manlix['status']="<a href='?' title='��������� � ������� ����'><font color='#000000'>������� ����</font></a> <font color='blue'>�</font> <font color='green'>".ucfirst($name)."</font>";
					$temp=-1;
						while(list(,$next)=each($array))
						{
						$temp++;
							if($manlix['section']['select'][1]==$temp)
							{
							$manlix['status'].=" <font color='blue'>�</font> <a href='?section=".$void."_".$temp."' title='��������� � ������\"".ucfirst($next)."\"'><font color='#de0000'>".ucfirst($next)."</font></a>";
							break;
							}
						}
					}
				}
			$temp_array=null;
			}

			else
			{
			$manlix['status']="� ��� ��� ���� ��� ����� � ���� ������";
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
	$manlix['status']="������� � ���������� �������� ������ �����, ������ ����� �����";
	}
}

if(isset($_COOKIE[$manlix['script']['prefix']."login"])&&isset($_COOKIE[$manlix['script']['prefix']."password"]))
{$manlix['access']=admin($_COOKIE[$manlix['script']['prefix']."login"],$_COOKIE[$manlix['script']['prefix']."password"])?1:0;}

if(empty($manlix['access']))
{
	if(isset($_POST['login'])||isset($_POST['password']))
	{
		$manlix['status']="���� ��� � ������ �� ��������";

		if(isset($_POST['password'])&&!$_POST['password'])	$manlix['status']="�� �� ����� ���� ������, ������� ���� ������ ��� ���";
		if(isset($_POST['login'])&&!$_POST['login'])		$manlix['status']="�� �� ����� ��� ���, ������� ���� ������ ��� ���";
		if(isset($_POST['login'])&&!isset($_POST['password']))	$manlix['status']="�� �� ����� ��� � ������";
	}

$manlix['section']['name']="���� � ����������";
$manlix['result']='<form method=post><br>
<table border=0 align=center cellspacing=0 cellpadding=1>
<tr><td align=right><font face=verdana size=1 color=maroon>���� ���:</td>	<td><input type=text name=login size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px"></td></tr>
<tr><td align=right><font face=verdana size=1 color=maroon>������:</td>	<td><input type=password name=password size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px"></td></tr>
<tr><td height=10></td></tr>
<tr><td align=right colspan=2>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000>
				<tr><td><input type=submit value="��������� ����" class=submit style="width: 163px"></td></tr>
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
	case "0_0":	$manlix['section']['name']="�������� ������ ���������";
			$manlix['base']=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);

			if(!count($manlix['base']))
			{
			$manlix['result']=<<<HTML
			<center><font face=verdana size=1 color=maroon><i><br>�������� ����� �����<br><br><br><br></i><a href="?">...��������� �����</a></font><br><br></center>
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
			<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>�</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
			<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
			<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."<br>��������� � ".$manlix['other']['num']."</font></i></div>
			".$manlix['other']['message']."
			<div align=right>";

			if(!empty($manlix['other']['homepage']))
			$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='�������� ���������'></a>";

			if(!empty($manlix['other']['mail']))
			$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='����������� �����'></a>";

			if(!empty($manlix['other']['icq']))
			$manlix['result'].="<img src='images/icq.gif' border=0 alt='�����: ".$manlix['other']['icq']."'>";

			$manlix['result'].="</div>
			</font></td></tr>";

			if(!empty($manlix['other']['answer']))
			$manlix['result'].="<tr bgcolor=#faedc0><td><i><font face=verdana size=1><font color=#de0000><b>��� ���� �����</b> (<a href='?section=0_0&MessageId=".$manlix['other']['time']."'>�������� �����</a>) (<a href='?section=0_0&MessageId=".$manlix['other']['time']."&delete=1'>������� �����</a>)</font><br><b>�</b></i> <font color=maroon>".$manlix['other']['author']."</font>: <font color=#000000>".$manlix['other']['answer']."</font><i><div align=right>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</div></i></font></td></tr>";
			else
			$manlix['result'].="<tr bgcolor=#faedc0><td><i><font face=verdana size=1><font color=green><b>��� ������</b></font> (<a href='?section=0_0&MessageId=".$manlix['other']['time']."'>�������� �����</a>)</font></i></td></tr>";

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
					<center><br><font face=verdana size=1>��������� ���� ��������� �� ����������<br><br><br><br><a href="?section=0_0">...��������� �� ��� �����</a><br></font><br></center>
HTML;

					else
					{
					$manlix['section']['name']="����� �����";

					$manlix['result'].=<<<HTML
					<center><br><br><font face=verdana size=1 color=maroon>����� �� ��������� ������� �����<br><br><br>(<a href='?section=0_0'>�������� ���������</a>)</font><br><br><br></center>
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
		document.all.SmilesText.innerText="������ ��������";
		document.all.SmilesTr.style.display='';
		}

		else
		{
		document.all.SmilesText.innerText="�������� ��������";
		document.all.SmilesTr.style.display='none';
		}
	}

	else if(ns)
	{

		if(document.getElementById("SmilesTr").style.display=="none")
		{
		document.getElementById("SmilesText").innerHTML="������ ��������";
		document.getElementById("SmilesTr").style.display='';
		}

		else
		{
		document.getElementById("SmilesText").innerHTML="�������� ��������";
		document.getElementById("SmilesTr").style.display='none';
		}
	}

	else
	alert("��� ������� �� ��������������!");
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
	alert("��� ������� �� ��������������!");
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
	alert("��� ������� �� ��������������!");
}

// -->
</script>
HTML;
						$manlix=array_merge($manlix,parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat",1));

							if(!count($smiles=GetSmiles())) $manlix['other']['smiles']="��������� ���";
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

												

						$manlix['section']['name']="����� �� ���������";

						$manlix['result'].="<table border=0><tr><td></td></tr></table>
						<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
						<form method=post name=guestbook>
						<input type=hidden name=MessageId value='".$manlix['other']['time']."'>
						<tr bgcolor=#faedc0><td><i><font face=verdana size=1 onclick=InsertName(\"".addslashes(str_replace(" ","&nbsp;",$manlix['other']['name']))."\") style='cursor: hand'><b>�</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
						<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
						<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."</font></i></div>
						".$manlix['other']['message']."
						<div align=right>";

						if(!empty($manlix['other']['homepage']))
						$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='�������� ���������'></a>";

						if(!empty($manlix['other']['mail']))
						$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='����������� �����'></a>";

						if(!empty($manlix['other']['icq']))
						$manlix['result'].="<img src='images/icq.gif' border=0 alt='�����: ".$manlix['other']['icq']."'>";

						$manlix['result'].="</div>
						</font></td></tr>";

						if(!empty($manlix['other']['answer']))
						$manlix['result'].="<tr bgcolor=#faedc0><td><i><font face=verdana size=1 color=maroon>������������ �����:<br><table border=0 cellspacing=1 cellpadding=3 bgcolor=gray width=100%><tr><td bgcolor=#fef1d8><font face=verdana size=1><font color=maroon>".$manlix['other']['author']."</font>: ".$manlix['other']['answer']."<i><div align=right><font color=maroon>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</font></div></i></font></td></tr></table><div align=right>(<a href='?'>���������� ��������</a> / <a href='?section=0_0'>�������� ����� � ������� � ������ ���������</a>)</div><br><font color=#de0000>�������� ��������!</font><ul type=square>".(($_COOKIE[$manlix['script']['prefix']."login"]!=$manlix['other']['author'])?"<li>��� ������ ������� ������ (".$manlix['other']['author'].") ��������� �� ���� ��� (".$_COOKIE[$manlix['script']['prefix']."login"].").</li>":null)."<li>������ ����� ��������� �� �����.</li></ul>����� �����:</font></i><center><textarea name=NewAnswer rows=5 cols=46 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 390px; height: 90px\"></textarea></center></td></tr>";
						else
						$manlix['result'].="<tr bgcolor=#faedc0><td><i><div align=right><font face=verdana size=1>(<a href='?section=0_0'>�������� �������� � ��������� � ������ ���������</a>)</font></div><br><font face=verdana size=1 color=maroon>����� �����:</font></i><center><textarea name=NewAnswer rows=5 cols=46 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 390px; height: 90px\"></textarea></center></td></tr>";

						$manlix['result'].="
						<tr bgcolor=#faedc0><td align=center><font face=verdana size=1 color=#de0000 style='cursor: hand' onclick=SmilesTable() id=SmilesText>�������� ��������</font></td></tr>
						<tr bgcolor=#faedca style='display: none' id=SmilesTr><td align=center>".$manlix['other']['smiles']."</td></tr>

						</table>
						<table border=0><tr><td></td></tr></table>


						<br><table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
						<tr><td><input type=submit value='���������' class=submit style='width: 163px'></td></tr>
						</form>
						</table><br>";

						$manlix['found']=1;
						break;
						}
					}

					if(!isset($manlix['found']))
					$manlix['result'].=<<<HTML
					<center><br><font face=verdana size=1>��������� ���� ��������� �� ����������<br><br><br><br><a href="?section=0_0">...��������� �� ��� �����</a><br></font><br></center>
HTML;
				}

				elseif(empty($_POST['NewAnswer']))
				{
				$manlix['section']['name']="������";
				$manlix['result'].="<center><br><font face=verdana size=1 color=maroon>�� �� ����� ����� �����<br><br><br>";
					if(!empty($_POST['MessageId']))
					$manlix['result'].="<a href='?section=0_0&MessageId=".$_POST['MessageId']."'>...��������� �� ��� �����</a> | ";
				$manlix['result'].="<a href='?section=0_0'>������� ������ ���������</a></font><br><br></center>";
				}

				elseif(empty($_POST['MessageId']))
				{
				$manlix['section']['name']="������";
				$manlix['result'].="<center><br><font face=verdana size=1 color=maroon>�� ������ id ���������<br><br><br>";
				$manlix['result'].="<a href='?section=0_0'>������� ������ ���������</a></font><br><br></center>";
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
					<center><br><font face=verdana size=1>��������� ���� ��������� �� ����������<br><br><br><br><a href="?section=0_0">...��������� �� ��� �����</a><br></font><br></center>
HTML;

					else
					{
					$manlix['section']['name']="����� ��������";

					$manlix['result'].=<<<HTML
					<center><br><br><font face=verdana size=1 color=maroon>����� �� ��������� ������� ��������<br><br><br>(<a href='?section=0_0'>�������� ����� �� ������ ���������</a>)</font><br><br><br></center>
HTML;
					}
				}
			}

			break;

	case "0_1":	$manlix['section']['name']="�������� ���������, ������� ������ �������������";

			$manlix['base']=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);

			if(!count($manlix['base']))
			{
			$manlix['result']=<<<HTML
			<center><font face=verdana size=1 color=maroon><i><br>�������� ����� �����<br><br><br><br></i><a href="?">...��������� �����</a></font><br><br></center>
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
			<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>�</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
			<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
			<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."<br>��������� � ".$manlix['other']['num']."<br><a href='?section=0_1&MessageId=".$manlix['other']['time']."'><font color=#de0000>�������������</font></a></font></i></div>
			".$manlix['other']['message']."
			<div align=right>";

			if(!empty($manlix['other']['homepage']))
			$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='�������� ���������'></a>";

			if(!empty($manlix['other']['mail']))
			$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='����������� �����'></a>";

			if(!empty($manlix['other']['icq']))
			$manlix['result'].="<img src='images/icq.gif' border=0 alt='�����: ".$manlix['other']['icq']."'>";

			$manlix['result'].="</div>
			</font></td></tr>";

			if(!empty($manlix['other']['answer']))
			$manlix['result'].="<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>�</b></i> <font color=maroon>".$manlix['other']['author']."</font>: <font color=#000000>".$manlix['other']['answer']."</font><i><div align=right>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</div></i></font></td></tr>";

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
		document.all.SmilesText.innerText="������ ��������";
		document.all.SmilesTr.style.display='';
		}

		else
		{
		document.all.SmilesText.innerText="�������� ��������";
		document.all.SmilesTr.style.display='none';
		}
	}

	else if(ns)
	{

		if(document.getElementById("SmilesTr").style.display=="none")
		{
		document.getElementById("SmilesText").innerHTML="������ ��������";
		document.getElementById("SmilesTr").style.display='';
		}

		else
		{
		document.getElementById("SmilesText").innerHTML="�������� ��������";
		document.getElementById("SmilesTr").style.display='none';
		}
	}

	else
	alert("��� ������� �� ��������������!");
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
	alert("��� ������� �� ��������������!");
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
	alert("��� ������� �� ��������������!");
}

// -->
</script>
HTML;
						$manlix=array_merge($manlix,parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat",1));

							if(!count($smiles=GetSmiles())) $manlix['other']['smiles']="��������� ���";
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

												

						$manlix['section']['name']="����� �� ���������";

						$manlix['result'].="<table border=0><tr><td></td></tr></table>
						<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
						<form method=post name=guestbook>
						<input type=hidden name=MessageId value='".$manlix['other']['time']."'>
						<tr bgcolor=#faedc0><td><i><font face=verdana size=1 onclick=InsertName(\"".addslashes(str_replace(" ","&nbsp;",$manlix['other']['name']))."\") style='cursor: hand'><b>�</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
						<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
						<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."</font></i></div>
						<textarea name=NewMessage rows=5 cols=46 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 390px; height: 90px\">".$manlix['other']['message']."</textarea>
						<div align=right>";

						if(!empty($manlix['other']['homepage']))
						$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='�������� ���������'></a>";

						if(!empty($manlix['other']['mail']))
						$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='����������� �����'></a>";

						if(!empty($manlix['other']['icq']))
						$manlix['result'].="<img src='images/icq.gif' border=0 alt='�����: ".$manlix['other']['icq']."'>";

						$manlix['result'].="</div>
						</font></td></tr>
						<tr bgcolor=#faedc0><td align=center><font face=verdana size=1 color=#de0000 style='cursor: hand' onclick=SmilesTable() id=SmilesText>�������� ��������</font></td></tr>
						<tr bgcolor=#faedca style='display: none' id=SmilesTr><td align=center>".$manlix['other']['smiles']."</td></tr>";

						if(!empty($manlix['other']['answer']))
						$manlix['result'].="<tr bgcolor=#faedc0><td><font face=verdana size=1><i><font color=maroon>".$manlix['other']['author']."</font></i>: ".$manlix['other']['answer']."<i><div align=right><font color=maroon>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</div></i></font></td></tr>";

						$manlix['result'].="	</table>
						<table border=0><tr><td></td></tr></table>


						<br><table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
						<tr><td><input type=submit value='���������' class=submit style='width: 163px'></td></tr>
						</form>
						</table><br>";

						$manlix['found']=1;
						break;

						}
					}

					if(!isset($manlix['found']))
					$manlix['result'].=<<<HTML
					<center><br><font face=verdana size=1>��������� ���� ��������� �� ����������<br><br><br><br><a href="?section=0_1">...��������� �� ��� �����</a><br></font><br></center>
HTML;
				}

				elseif(empty($_POST['NewMessage']))
				{
				$manlix['section']['name']="������";
				$manlix['result'].="<center><br><font face=verdana size=1 color=maroon>�� ����� ��������� ������������<br><br><br>";
					if(!empty($_POST['MessageId']))
					$manlix['result'].="<a href='?section=0_1&MessageId=".$_POST['MessageId']."'>...��������� �� ��� �����</a> | ";
				$manlix['result'].="<a href='?section=0_1'>������� ������ ���������</a></font><br><br></center>";
				}

				elseif(empty($_POST['MessageId']))
				{
				$manlix['section']['name']="������";
				$manlix['result'].="<center><br><font face=verdana size=1 color=maroon>�� ������ ������������� ���������<br><br><br>";
				$manlix['result'].="<a href='?section=0_1'>������� ������ ���������</a></font><br><br></center>";
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
					<center><br><font face=verdana size=1>��������� ���� ��������� �� ����������<br><br><br><br><a href="?section=0_1">...��������� �� ��� �����</a><br></font><br></center>
HTML;

					else
					{
					$manlix['section']['name']="��������� ��������";

					$manlix['result'].=<<<HTML
					<center><br><br><font face=verdana size=1 color=maroon>��������� ���� ������� ��������<br><br><br>(<a href='?section=0_1'>�������� ��� ���� ���������</a>)</font><br><br><br></center>
HTML;
					}
				}
			}

			break;

	case "0_2":	$manlix['section']['name']="�������� ���������, ������� ������ �������";
			$manlix['base']=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['base']);

			if(!count($manlix['base']))
			{
			$manlix['result']=<<<HTML
			<center><font face=verdana size=1 color=maroon><i><br>�������� ����� �����<br><br><br><br></i><a href="?">...��������� �����</a></font><br><br></center>
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
			<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>�</b> ".$manlix['other']['name']." (".$manlix['other']['ip'].")</font></i></td></tr>
			<tr><td bgcolor=#faedca><font face=verdana size=1 color=maroon>
			<div align=right><i><font face=verdana size=1 color=#000000>".date('d.m.Y (H:i)',$manlix['other']['time'])."<br>��������� � ".$manlix['other']['num']."</font></i></div>
			".$manlix['other']['message']."
			<div align=right>";

			if(!empty($manlix['other']['homepage']))
			$manlix['result'].="<a href='http://".$manlix['other']['homepage']."' target='_blank'><img src='images/homepage.gif' border=0 alt='�������� ���������'></a>";

			if(!empty($manlix['other']['mail']))
			$manlix['result'].="<a href='mailto:".$manlix['other']['mail']."'><img src='images/mail.gif' border=0 alt='����������� �����'></a>";

			if(!empty($manlix['other']['icq']))
			$manlix['result'].="<img src='images/icq.gif' border=0 alt='�����: ".$manlix['other']['icq']."'>";

			$manlix['result'].="</div>
			</font></td></tr>";

			if(!empty($manlix['other']['answer']))
			$manlix['result'].=	"<tr bgcolor=#faedc0><td><i><font face=verdana size=1><b>�</b></i> <font color=maroon>".$manlix['other']['author']."</font>: <font color=#000000>".$manlix['other']['answer']."</font><i><div align=right>".date('d.m.Y (H:i)',$manlix['other']['answerTime'])."</div></i></font></td></tr>";

			$manlix['result'].="<tr bgcolor=#faedc0 align=center><td><i><a href='?section=0_2&MessageId=".$manlix['other']['time']."'><font face=verdana size=1 color=#de0000><b>������� ���������</b></font></a></i></td></tr>";

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
					<center><br><font face=verdana size=1>��������� ���� ��������� �� ����������<br><br><br><br><a href="?section=0_2">...��������� �� ��� �����</a><br></font><br></center>
HTML;

					else
					{
					$manlix['section']['name']="��������� �������";

					$manlix['result'].=<<<HTML
					<center><br><br><br><font face=verdana size=1 color=maroon>��������� �������<br><br><br>(<a href='?section=0_2'>������� ��� ���������</a>)</font><br><br><br></center>
HTML;
					}
			}

			break;

	case "1_0":	$manlix['section']['name']="���������� ����� ������� ��� ������ ������";

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
						<tr><td align=center><font face=verdana size=1 color=maroon><i>��� ������ ������:</i></td></tr>
						<tr><td><input type=text name=login2 size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 298px" value="
HTML;
						if(isset($manlix['temp']['login2']))$manlix['result'].=$manlix['temp']['login2'];
						$manlix['result'].=<<<HTML
"></td></tr>
						<tr><td align=center><font face=verdana size=1 color=maroon><i>��� ������:</i></td></tr>
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
				$manlix['result'].='<table border=0 width=300 cellspacing=1 cellpadding=3 align=center bgcolor=#faad1e><caption><font face=verdana size=1 color=maroon>����� ������� ��� ������� <font color=#de0000>'.$section.'</font>:</font></caption>';
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
					<tr><td><input type=submit value="&gt; &gt; �� �����, ���� �������� ������ ������ &lt; &lt;" class=submit style="width: 298px"></td></tr>
					</table><br>
				</td></tr>
				</table><br>
HTML;
			}
			break;

	case "1_1":	$manlix['section']['name']="�������� ��� ������";
			$_POST['a']=(isset($_POST['a']))?$_POST['a']:null;

			$manlix['result'].=<<<HTML
			<table border=0 align=center cellspacing=0 cellpadding=1 width=470>
			<tr><td bgcolor=#faedca>
HTML;

			if(empty($_POST['selected_admin']))
			{
			$admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);
			$manlix['result'].="<center><form method=post><br><select name=selected_admin class=name onfocus='id=className' onblur='id=\"\"' style='font: italic'>";
			$manlix['result'].="<option value=''>�������� �����...</option>";
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
			<tr><td><input type=submit value=������... class=submit style="width: 100px"></td></tr>
			</form>
			</table>';
			}

			elseif(!admin_exists($_POST['selected_admin']))
			{
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>��������� ���� �����  �� ����������.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_1">������� ����, ����� ������� ��� ������ �� ������ ������������.</a><br><br></font>
HTML;
			}

			elseif(!$string=check_permission($_POST['a']))
			{
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>�������� ������ ���� �������.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_1">������� ����, ����� ��� ��� ������� ��� ������ �� ������ ������������.</a><br><br></font>
HTML;
			}

			elseif(ereg("^".strtolower($_POST['selected_admin'])."$",strtolower($_COOKIE[$manlix['script']['prefix']."login"])))
			{
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>�������� ����� ������� ������ ���� ������.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_1">������� ����, ����� ��� ��� ������� ��� ������ �� ������ ������������.</a><br><br></font>
HTML;
			}

			elseif(!empty($_POST['change_info']))
			{
			$manlix['section']['name']="���������";
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

			$manlix['okay']="<center><br><font face=verdana size=1><br>���������� � ������ <i><font color=maroon>".$_POST['selected_admin']."</font></i>, ������� ��������.</font><br><br></center>";
			$manlix['result'].=$manlix['okay'];
			}

			else
			{
			$manlix['section']['name']="����� ���� �������, �� ��� �� ������ �������� ������ ����� ������";
			$admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);
			$manlix['result'].=<<<HTML
			<table border=0 align=center width=300 cellspacing=0 cellpadding=1>
			<form method=post>
			<tr><td><br></td></tr>
			<tr><td width=200 align=right><font face=verdana size=1>��� ������: </font></td><td><input type=text class=name size=28 value="
HTML;
			$manlix['result'].=(isset($_POST['selected_admin']))?$_POST['selected_admin']:null;
			$manlix['result'].=<<<HTML
" disabled></td></tr>

			<tr><td><br></td></tr>
			<tr><td colspan=2 bgcolor=maroon></td></tr>
			<tr><td colspan=2 align=center><font face=verdana size=1 color=gray><i>���� �� ������ �������� ������, �� ��������� ��������� ����, ���� �� �� ������ �������� ������� ������ ��� ����� ������, �� �������� ��� ���� ������.</i></font></td></tr>
			<tr><td align=right><font face=verdana size=1 color=gray>����� ������: </font></td><td><input type=password name=new_password size=28 style="border: 1px; border-style: solid; height: 16px; border-color: gray; background-color: #f2f2f2; font-family: verdana; font-size: 10px; color: gray"></td></tr>
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
				$manlix['result'].='<table border=0 width=300 cellspacing=1 cellpadding=3 align=center bgcolor=#faad1e><caption><font face=verdana size=1 color=maroon>����� ������� ��� ������� <font color=#de0000>'.$section.'</font>:</font></caption>';

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
			<tr><td><input type=submit name=change_info value=��������� class=submit style="width: 100px"></td></tr>
			</form>
			</table><br>
HTML;
			}

			$manlix['result'].=<<<HTML
			</table><br>
HTML;
			break;

	case "1_2":	$manlix['section']['name']="�������� ��� ������, �������� ����� �������";

			$manlix['result'].=<<<HTML
			<table border=0 align=center cellspacing=0 cellpadding=1 width=470>
			<tr><td bgcolor=#faedca>
HTML;

			if(!(count($admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']))-1))
			{
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>������ �������.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?">������� ����, ����� ��������� � ������� ���� ����������.</a><br></font>
HTML;
			}

			elseif(empty($_POST['selected_admin']))
			{
			$manlix['result'].="<center><form method=post><br><select name=selected_admin class=name onfocus='id=className' onblur='id=\"\"' style='font: italic'>";
			$manlix['result'].="<option value=''>�������� �����...</option>";
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
			<tr><td><input type=submit value="������� ���������� ���� ������" class=submit style="width: 200px"></td></tr>
			</form>
			</table>';
			}

			elseif(!admin_exists($_POST['selected_admin']))
			{
			$manlix[result].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>��������� ���� �����  �� ����������.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_2">������� ����, ����� ������� ��� ������ �� ������ ������������.</a><br><br></font>
HTML;
			}

			elseif(ereg("^".strtolower($_POST['selected_admin'])."$",strtolower($_COOKIE[$manlix['script']['prefix']."login"])))
			{
			$manlix['section']['name']="���������";
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>���� ���� �� ������� �� ������.<br><br><hr color=maroon size=1 width=415><br></font>
			<font face=verdana size=1><a href="?section=1_2">������� ����, ����� ������� ������� ������ �� ������.</a><br></font>
HTML;
			}

			else
			{
			$admins=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['admins']);
			$manlix['section']['name']="���������";
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

			$manlix['okay']="<center><font face=verdana size=1><br><br><br>����� <i><font color=maroon>".$_POST['selected_admin']."</font></i>, ������� �����.</font><br><br></center>";
			$manlix['result'].=$manlix['okay'];
			}

			$manlix['result'].=<<<HTML
			</td></tr>
			</table><br>
HTML;
			break;

	case "2_0":	if(empty($_POST['SelectedTemplate']))
			{
			$manlix['section']['name']="�������� ������, ������� ����� ������������ �������� �����";

			$manlix['result'].="<center><form method=post><br><select name=SelectedTemplate class=name onfocus='id=className' onblur='id=\"\"' style='font: italic'>";
			$manlix['result'].="<option value=''>�������� �����...</option>";
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
			<tr><td><input type=submit value=��������� class=submit style="width: 100px"></td></tr>
			</table>
			</form>';
			}

			elseif($_POST['SelectedTemplate']==$manlix['template']['parse'])
			{
			$manlix['section']['name']="������";

			$manlix['result'].=<<<HTML
			<center><br><font face=verdana size=1 color=maroon>��������� ���� ������ ��� ������������<br><br>(<a href='?section=2_0'>...��������� �� ��� �����</a> | <a href='?'>��������� � ������� ����</a>)</font><br><br></center>
HTML;
			}

			elseif(!is_dir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']))
			{
			$manlix['section']['name']="������";

			$manlix['result'].=<<<HTML
			<center><br><font face=verdana size=1 color=maroon>��������� ���� ������ �� ����������<br><br>(<a href='?section=2_0'>...��������� �� ��� �����</a> | <a href='?'>��������� � ������� ����</a>)</font><br><br></center>
HTML;
			}

			else
			{
			$manlix['section']['name']="������ �������";
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
			<center><br><font face=verdana size=1 color=maroon>�������� ������ �������.<br><br>(<a href='?section=2_0'>...��������� �� ��� �����</a> | <a href='?'>��������� � ������� ����</a>)</font><br><br></center>
HTML;
			}

			break;

	case "2_1":	if(empty($_POST['SelectedTemplate']))
			{
			$manlix['section']['name']="�������� ������, ������� ������ ��������";

			$manlix['result'].="<center><form method=post><br><select name=SelectedTemplate class=name onfocus='id=className' onblur='id=\"\"' style='font: italic'>";
			$manlix['result'].="<option value=''>�������� �����...</option>";
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
			<tr><td><input type=submit value=�������... class=submit style="width: 100px"></td></tr>
			</table>
			</form>';
			}

			elseif(!is_dir($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']))
			{
			$manlix['section']['name']="������";

			$manlix['result'].=<<<HTML
			<center><br><font face=verdana size=1 color=maroon>��������� ���� ������ �� ����������<br><br>(<a href='?section=2_1'>...��������� �� ��� �����</a> | <a href='?'>��������� � ������� ����</a>)</font><br><br></center>
HTML;
			}

			elseif(count($_POST)<2)
			{
			$manlix['section']['name']="��������� ����� ��� �������";
			$manlix['result'].=	"<table border=0 align=center>".
					"<form method=post>".
					"<input type=hidden name=SelectedTemplate value='".$_POST['SelectedTemplate']."'>".
					"<tr><td><div align=right><font face=verdana size=2 color=green><br><b><i>�������<br>(���������� �� ���������� �������� � ����� <a href='info.html'>info.html</a>)</i></b></font></div></td></tr>";
				while(list($key,$FileName)=each($manlix['templates']))
				{
					switch($key)
					{
					case "top":		$TemplateName="��������";
								break;

					case "form":		$TemplateName="����� ��� ���������� ���������";
								break;

					case "no_messages":	$TemplateName="�������� ����� �����";
								break;

					case "message":		$TemplateName="���������";
								break;

					case "bottom":		$TemplateName="���";
								break;

					case "okay":		$TemplateName="��������� ���������";
								break;

					case "closed":		$TemplateName="�������� ����� �������";
								break;

					default:			$TemplateName=$key;
					}

				$manlix['result'].=	"<tr><td><font face=verdana size=1><b><i>� <font color=maroon>".$TemplateName."</font></i></b></font></td></tr>".
						"<tr><td><textarea wrap=off rows=10 cols=56 name=templates__".$key." class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 450px; height: 150px\">".ReadTemplate($_POST['SelectedTemplate'],$FileName)."</textarea></td></tr>";
				}

			$manlix['result'].="<tr><td><div align=right><font face=verdana size=2 color=green><br><b><i>�������������� ��������� �������</i></b></font></div><br></td></tr>";

			$manlix['color']=parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$_POST['SelectedTemplate']."/config.inc.dat");
			$manlix['result'].="<tr><td><table width=100% align=center>";
				while(list($key,$value)=each($manlix['color']))
				{
					switch($key)
					{
					case "current_page":	$KeyName="���� �����/����� � ��������� ��� ������� ��������";
								break;

					case "not_current_page":	$KeyName="���� �����/����� � ��������� ��� ��������� ��������";
								break;

					case "even":		$KeyName="���� ��� ����";
								break;

					case "uneven":		$KeyName="���� ��� ������";
								break;

					case "active_symbol":	$KeyName="���� ��� �������� �������� � ��������� �� �����";
								break;

					case "not_active_symbol":	$KeyName="���� ��� ���������� �������� � ��������� �� �����";
								break;

					default:			$KeyName=$key;
					}
				$manlix['result'].='<tr><td align=right><font face=verdana size=1>'.$KeyName.':</font></td><td><input type=text name=color__'.$key.' size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px" value="'.htmlspecialchars($value).'"></td></tr>';
				}
			$manlix['result'].="</table></td></tr>";
			$manlix['result'].=<<<HTML
			<tr><td><br>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=��������� class=submit style="width: 100px"></td></tr>
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
			$manlix['section']['name']="�������� ������ �������";
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>������ ������� ������<br><br>(<a href='?section=2_1'>������� ������ ������ ��� ���������</a> | <a href='?'>��������� � ������� ����</a>)<br><br></font></center>
HTML;
			}

			break;

	case "2_2":	if(!eregi("^[a-z0-9_-]+$",(!empty($_POST['TemplateName']))?$_POST['TemplateName']:null))
			{
			$manlix['section']['name']="������� ��� ��� ������ �������";
			$manlix['result'].='
			<center><br>
			<form method=post>
				<font face=verdana size=1>�����������, ������: �������,<br>��������� �����, �����, ���� ������������� � �������� (�����).</font>
				<br><br>
				<input type=text name=TemplateName size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px" value="'.htmlspecialchars((!empty($_POST['TemplateName']))?$_POST['TemplateName']:null).'"><br><br>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=������... class=submit style="width: 100px"></td></tr>
				</table>			
			</form>
			</center>';
			}

			elseif(count($_POST)<2)
			{
			$manlix['section']['name']="��������� �����";

			$manlix['result'].=	"<table border=0 align=center>".
					"<form method=post>".
					"<input type=hidden name=TemplateName value='".$_POST['TemplateName']."'>".
					"<tr><td><div align=right><font face=verdana size=2 color=green><br><b><i>�������<br>(���������� �� ���������� �������� � ����� <a href='info.html'>info.html</a>)</i></b></font></div></td></tr>";
				while(list($key,$FileName)=each($manlix['templates']))
				{
					switch($key)
					{
					case "top":		$TemplateName="��������";
								break;

					case "form":		$TemplateName="����� ��� ���������� ���������";
								break;

					case "no_messages":	$TemplateName="�������� ����� �����";
								break;

					case "message":		$TemplateName="���������";
								break;

					case "bottom":		$TemplateName="���";
								break;

					case "okay":		$TemplateName="��������� ���������";
								break;

					default:			$TemplateName=$key;
					}

				$manlix['result'].=	"<tr><td><font face=verdana size=1><b><i>� <font color=maroon>".$TemplateName."</font></i></b></font></td></tr>".
						"<tr><td><textarea wrap=off rows=10 cols=56 name=templates__".$key." class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"width: 450px; height: 150px\"></textarea></td></tr>";
				}

			$manlix['result'].="<tr><td><div align=right><font face=verdana size=2 color=green><br><b><i>�������������� ��������� �������</i></b></font></div></td></tr>";

			$manlix['color']=parse_ini_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['dir']['templates']."/".$manlix['template']['parse']."/config.inc.dat");
			$manlix['result'].="<tr><td><table width=100% align=center>";
				while(list($key,$value)=each($manlix['color']))
				{
					switch($key)
					{
					case "current_page":	$KeyName="���� �����/����� � ��������� ��� ������� ��������";
								break;

					case "not_current_page":	$KeyName="���� �����/����� � ��������� ��� ��������� ��������";
								break;

					case "even":		$KeyName="���� ��� ����";
								break;

					case "uneven":		$KeyName="���� ��� ������";
								break;

					case "active_symbol":	$KeyName="���� ��� �������� �������� � ��������� �� �����";
								break;

					case "not_active_symbol":	$KeyName="���� ��� ���������� �������� � ��������� �� �����";
								break;

					default:			$KeyName=$key;
					}
				$manlix['result'].='<tr><td align=right><font face=verdana size=1>'.$KeyName.':</font></td><td><input type=text name=color__'.$key.' size=30 class=name onfocus="id=className" onblur="id=\'\'"" style="font: italic; width: 165px"></td></tr>';
				}
			$manlix['result'].="</table></td></tr>";
			$manlix['result'].=<<<HTML
			<tr><td>	<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=������� class=submit style="width: 100px"></td></tr>
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
			$manlix['section']['name']="�������� ������ �������";
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br>������ ������� ������<br><br>(<a href='?section=2_2'>������� ��� ������</a> | <a href='?'>��������� � ������� ����</a>)<br><br></font></center>
HTML;
			}

			break;

	case "3_0":	$manlix['section']['name']="�������� ���������� �������";
			$conf=parse_ini_file("./inc/config.inc.dat",1);
			$void=0;
			$manlix['result'].="<table border=0 width=440 align=center><tr><td>";
				while(list($section,$array)=each($conf))
				{
				$manlix['result'].="<table border=0 width=100% align=center cellspacing=0 cellpadding=1><tr><td colspan=2 bgcolor=#7e5757></td></tr><tr><td colspan=2 bgcolor=#f7f1e1 align=center><font face=verdana size=1><b>".((description($section,0))?description($section,0):$section)."</td></tr><tr><td colspan=2 bgcolor=#7e5757></td></tr>";
					while(list($key,$value)=each($array))
					{
					$bgcolor=(strstr(($void/2),"."))?"#f6f4dd":"#f6f4cd";
					$manlix['result'].="<tr bgcolor=".$bgcolor." valign=top><td width=200><font face=verdana size=1 color=maroon>&nbsp;<i>".($void+1)."</i>&nbsp;<b>�</b>&nbsp;".((description($section,$key))?description($section,$key):$key)."&nbsp;</td><td><font face=verdana size=1>".((!empty($value))?($value!=" ")?htmlspecialchars($value):"<b>������</b>":0)."</td></tr>";
					$void++;
					}
				$manlix['result'].=	"<tr><td colspan=2 bgcolor=#7e5757></td></tr>".
						"</table>".
						"<table border=0 height=10><tr><td></td></tr></table>";
				}
			$manlix['result'].="</td></tr></table>";
			break;

	case "3_1":	$manlix['section']['name']="��������� ���������� �������";
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
						$manlix['result'].="<tr bgcolor=".$bgcolor." valign=top><td width=200><font face=verdana size=1 color=maroon>&nbsp;<i>".($void+1)."</i>&nbsp;<b>�</b>&nbsp;".((description($section,$key))?description($section,$key):$key)."&nbsp;</td><td><input type=text name=".$section."__".$key." size=42 class=name onfocus=\"id=className\" style=\"font: italic; width: 228px\" onblur=\"id=''\"\" value=\"".((!empty($value))?htmlspecialchars($value):0)."\" ".(($section=="dir"&&$key=="inc")?" disabled":null)."></td></tr>";
						$void++;
						}
					$manlix['result'].=	"<tr><td colspan=2 bgcolor=#7e5757></td></tr>".
							"</table>".
							"<table border=0 height=10><tr><td></td></tr></table>";
					}
				$manlix['result'].=	"</td></tr></table>".
						"<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
						<tr><td><input type=submit value=��������� class=submit style='width: 100px'></td></tr>
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
			<center><br><br><font face=verdana size=1 color=maroon>��������� ������� ������� ��������</font><br><br><br></center>
HTML;
			}

			break;

	case "3_2":	$manlix['section']['name']="���� ����� ������� �������� ��������";
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
			$manlix['result'].='<table border=0 width=300 cellspacing=1 cellpadding=2 align=center bgcolor=#faad1e><caption><font face=verdana size=1 color=maroon>����� ������� ��� ������� <font color=#de0000>'.$section.'</font>:</font></caption>';

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
			$manlix['result'].="<center><a href='?'><font face=verdana size=1>...��������� � ������� ���� ����������</font></a></center><br>";
			$manlix['result'].=<<<HTML
			</td></tr>
			</table>
HTML;
			break;

	case "3_3":	$manlix['section']['name']="���������� � �������";
			$manlix['script']['info']=(!empty($manlix['script']['info']))?$manlix['script']['info']:"��� ����������";

			$manlix['result'].=	"<table border=0 width=400 align=center>".
					"<tr><td width=80><font face=verdana size=1>��� �������:</td><td><font face=verdana size=1 color=maroon>".$manlix['script']['name']."</td></tr>".
					"<tr><td><font face=verdana size=1>��-������:</td><td><font face=verdana size=1 color=maroon>".$manlix['script']['russian']."</td></tr>".
					"<tr><td><font face=verdana size=1>������:</td><td><font face=verdana size=1 color=#de0000>".$manlix['script']['version']."</td></tr>".
					"<tr><td colspan=2><font face=verdana size=1>�������������:</td></tr>".
					"<tr><td colspan=2><font face=verdana size=1><i>".$manlix['script']['info']."</td></tr>".
					"</table>";
			break;

	case "4_0":	$manlix['section']['name']="���������� ������ �������� ��� ������";

			if(empty($_POST))
			{
			$manlix['result'].=<<<HTML
			<center><br><form method=post><font face=verdana size=1>
			<table border=0>
			<tr><td align=right><font face=verdana size=1>������:</td><td><input type=text name=autochange1 size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 165px"></td></tr>
			<tr><td align=right><font face=verdana size=1>�������� ��:</td><td><input type=text name=autochange2 size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 165px"></td></tr>
			<tr><td colspan=2 align=center><br>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=�������� class=submit style='width: 100px'></td></tr>
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
			<font face=verdana size=1><br>�� �� �������, ��� ����� ������<br><br><a href="?section=4_0">...��������� �� ��� �����</a><br><br></font>
			</center>
HTML;

			elseif(empty($_POST['autochange2']))
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br>�� �� �������, �� ��� ������ ��������<br><br><a href="?section=4_0">...��������� �� ��� �����</a><br><br></font>
			</center>
HTML;

			elseif(CheckAutochange($_POST['autochange1']=preg_quote(manlix_stripslashes($_POST['autochange1']))))
			{
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br>��, ��� �� ������� � �������� �������� - ��� ���� � ����<br><br><a href="?section=4_0">...��������� �� ��� �����</a><br><br></font>
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
			<font face=verdana size=1><br>���������� ������� ��������� � ����<br><br>(<a href="?section=4_0">�������� ��� ���� ����������</a>)<br><br></font>
			</center>
HTML;
			}

			break;

	case "4_1":	$manlix['section']['name']="��������� ����������";
			$AutochangeFile=$AutochangeFileRead=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['autochange']);

			if(!count($AutochangeFile))
			{
			$manlix['result'].='<center>
			<font face=verdana size=1><br>� ���� ��� ������� ����������<br><br><a href="?">...��������� � ������� ����</a><br><br></font>
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
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>�������</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=180 bgcolor=#faad1e>
					<tr><td bgcolor=#f6f4cd align=center><font face=verdana size=1 color=maroon>����������</td></tr>
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
				<tr><td align=right><font face=verdana size=1>(<i><a href='?section=4_1&AutochangeId=".$c."'>��������</a></i>)</td></tr>
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
				<br>�������� ���� ���������� �� ������� � ����<br><br><a href="?section=4_1">...��������� �� ��� �����</a><br><br>
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
					<tr><td bgcolor=#faedc0><font face=verdana size=1 color=#de0000><input type=text name=autochange1 size=58 class=name onfocus=\"id=className\" onblur=\"id=''\"\" value='".htmlspecialchars($other[1])."'> -  �������</td></tr>
					<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon><input type=text name=autochange2 size=58 class=name onfocus=\"id=className\" onblur=\"id=''\"\" value='".htmlspecialchars($b)."' style='color: maroon'> - ����������</td></tr>
					</table>
					<table border=0><tr><td></td></tr></table>
					<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
					<tr><td><input type=submit value=�������� class=submit style='width: 100px'></td></tr>
					</table>
					<table border=0>
					</form>
					<tr><td></td></tr></table>";
					}

					elseif(empty($_POST['autochange1']))
					$manlix['result'].='<center>
					<font face=verdana size=1><br>�� �� �������, ��� ����� ������<br><br><a href="?section=4_1&AutochangeId='.(!empty($_POST['AutochangeId'])?$_POST['AutochangeId']:null).'">...��������� �� ��� �����</a><br><br></font>
					</center>';

					elseif(empty($_POST['autochange2']))
					$manlix['result'].='<center>
					<font face=verdana size=1><br>�� �� ������� ����������<br><br><a href="?section=4_1&AutochangeId='.(!empty($_POST['AutochangeId'])?$_POST['AutochangeId']:null).'">...��������� �� ��� �����</a><br><br></font>
					</center>';

					elseif(CheckAutochange($_POST['autochange1']=preg_quote(manlix_stripslashes($_POST['autochange1']))))
					$manlix['result'].='<center>
					<font face=verdana size=1><br>��, ��� �� ������� � �������� �������� - ��� ���� � ����<br><br><a href="?section=4_1&AutochangeId='.(!empty($_POST['AutochangeId'])?$_POST['AutochangeId']:null).'">...��������� �� ��� �����</a><br><br></font>
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
					<font face=verdana size=1><br>���������� ������� ��������<br><br>(<a href="?section=4_1">�������� ��� ���� ����������</a>)<br><br></font>
					</center>
HTML;
					}
				}
			}

			break;

	case "4_2":	$manlix['section']['name']="�������� ����������";
			$AutochangeFile=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['autochange']);

			if(!count($AutochangeFile))
			{
			$manlix['result'].='<center>
			<font face=verdana size=1><br>� ���� ��� ������� ����������<br><br><a href="?">...��������� � ������� ����</a><br><br></font>
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
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>�������</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=180 bgcolor=#faad1e>
					<tr><td bgcolor=#f6f4cd align=center><font face=verdana size=1 color=maroon>����������</td></tr>
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
				<tr><td align=right><font face=verdana size=1>(<i><a href='?section=4_2&AutochangeId=".$c."'>�������</a></i>)</td></tr>
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
				<br>�������� ���� ���������� �� ������� � ����<br><br><a href="?section=4_2">...��������� �� ��� �����</a><br><br>
				</font>
				</center>
HTML;

				else
				$manlix['result'].=<<<HTML
				<center>
				<font face=verdana size=1><br>���������� ������� �������<br><br>(<a href="?section=4_2">������� ��� ���� ����������</a>)<br><br></font>
				</center>
HTML;
			}
			break;

	case "5_0":	$manlix['section']['name']="���������� ������ � �������";

			if(empty($_POST))
			{
			$manlix['result'].=<<<HTML
			<br><table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
			<form method=post>
			<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon><font color=#de0000><i><br>�������� ��������!</i></font><ul type=square><li>�� ������ ������� ������ ����� �������, ��� ����� �����-���� ����� ������ � ���� <i>IP �����</i>: <b>127.1.1.</b> �.�. ���������, ��� �� �������� ������ � �������� ����� ���� � ���� ip ���������� � <b>127.1.1.</b> (�������� � <b>127.1.1.0</b> �� <b>127.1.1.255</b>)</li><li>���� ������ <b>127.1.1</b> (��� ����� �� �����), � �� <b>127.1.1.</b> (� ������ �� �����), �� ���������, ��� �� �������� ������ � �������� ����� ����, � ���� ip ���������� � <b>127.1.1</b>, �.�. ������������� ������ � ��������� � <b>127.1.1.0</b> �� <b>127.1.199.255</b></li><li>����� ������� ������ ������ ip ������, ������-��� ������� ���� ����� �����, ������ ��� ���������, ��������: <b>127.110.23.45</b></li></ul></td></tr>
			<tr><td bgcolor=#faedc0><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>IP �����:</td><td><input type=text name=ip size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 165px"></td></tr></table></td></tr>
			<tr><td bgcolor=#f6f4cd><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>�������:</td><td><input type=text name=reason size=30 class=name onfocus="id=className" onblur="id=''"" style="font: italic; width: 165px"></td></tr></table></td></tr>
			<tr><td bgcolor=#faedc0><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>�� ������� ������� ������:</td>
				<td>
				<select name=time class=name onfocus="id=className" onblur="id=''"" style="font: italic">
				<option value='' selected>�������� �����</option>
				<option value=10>10 �����</option>
				<option value=30>30 �����</option>
				<option value=60>1 ���</option>
				<option value=120>2 ����</option>
				<option value=240>4 ����</option>
				<option value=480>8 �����</option>
				<option value=960>16 �����</option>
				<option value=1440>1 ����</option>
				<option value=2880>2 ���</option>
				<option value=5760>4 ���</option>
				<option value=10080>1 ������</option>
				<option value=20160>2 ������</option>
				<option value=30240>3 ������</option>
				<option value=43200>1 �����</option>
				<option value=86400>2 ������</option>
				</select>
				</td></tr></table></td></tr>
			<tr><td colspan=2 bgcolor=#f6f4cd><br>
				<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
				<tr><td><input type=submit value=�������� class=submit style='width: 100px'></td></tr>
				</table><br>
			</td></tr>
			</form>
			</table><br>
HTML;
			}

			elseif(empty($_POST['ip']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>�� �� ����� ip �����<br><br><a href="?section=5_0">...��������� �� ��� �����</a><br><br></font></center>
HTML;

			elseif(empty($_POST['reason']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>�� �� ����� �������<br><br><a href="?section=5_0">...��������� �� ��� �����</a><br><br></font></center>
HTML;

			elseif(empty($_POST['time']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>�� �� ������� ���� ����������<br><br><a href="?section=5_0">...��������� �� ��� �����</a><br><br></font></center>
HTML;

			elseif(!is_numeric($_POST['time']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>���� ���������� ������ ���� ������ ��� ������<br><br><a href="?section=5_0">...��������� �� ��� �����</a><br><br></font></center>
HTML;
			elseif(CheckBanlist($_POST['ip']))
			$manlix['result'].=<<<HTML
			<center><font face=verdana size=1 color=maroon><br><br>��������� ���� ip ����� ��� ���� � ��������<br><br><a href="?section=5_0">...��������� �� ��� �����</a><br><br></font></center>
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
			<center><font face=verdana size=1 color=maroon><br><br>IP ����� ������� �������� � �������<br><br>(<a href="?section=5_0">�������� ��� ip �����</a>)<br><br></font></center>
HTML;
			}

			break;

	case "5_1":	$manlix['section']['name']="��������� ��������";

			$BanlistFile=$BanlistFileRead=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['banlist']);

			if(!count($BanlistFile))
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br><br>� �������� ������ ���<br><br><a href="?">...��������� � ������� ����</a><br><br></font>
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
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>ip �����</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=100 bgcolor=#faad1e>
					<tr><td bgcolor=#f6f4cd align=center><font face=verdana size=1 color=maroon>�������</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=192 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>����� �������������</td></tr>
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
				<tr><td align=right><font face=verdana size=1>(<i><a href='?section=5_1&BanId=".$id."'>��������</a></i>)</td></tr>
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
				<font face=verdana size=1><br><br>��������� ���� ������������� �� ����������<br><br><a href="?section=5_1">...��������� �� ��� �����</a><br><br></font>
				</center>
HTML;

				elseif(empty($_POST['BanId']))
				{
				$manlix['result'].="<br>
				<table border=0 cellspacing=1 cellpadding=3 align=center width=400 bgcolor=#faad1e>
				<form method=post>
				<input type=hidden name=BanId value='".$id."'>
				<tr><td bgcolor=#f6f4cd><font face=verdana size=1 color=maroon><font color=#de0000><i><br>�������� ��������!</i></font><ul type=square><li>�� ������ ������� ������ ����� �������, ��� ����� �����-���� ����� ������ � ���� <i>IP �����</i>: <b>127.1.1.</b> �.�. ���������, ��� �� �������� ������ � �������� ����� ���� � ���� ip ���������� � <b>127.1.1.</b> (�������� � <b>127.1.1.0</b> �� <b>127.1.1.255</b>)</li><li>���� ������ <b>127.1.1</b> (��� ����� �� �����), � �� <b>127.1.1.</b> (� ������ �� �����), �� ���������, ��� �� �������� ������ � �������� ����� ����, � ���� ip ���������� � <b>127.1.1</b>, �.�. ������������� ������ � ��������� � <b>127.1.1.0</b> �� <b>127.1.199.255</b></li><li>����� ������� ������ ������ ip ������, ������-��� ������� ���� ����� �����, ������ ��� ���������, ��������: <b>127.110.23.45</b></li></ul></td></tr>
				<tr><td bgcolor=#faedc0><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>IP �����:</td><td><input type=text name=ip size=30 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"font: italic; width: 165px\" value='".htmlspecialchars($ip)."'></td></tr></table></td></tr>
				<tr><td bgcolor=#f6f4cd><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>�������:</td><td><input type=text name=reason size=30 class=name onfocus=\"id=className\" onblur=\"id=''\"\" style=\"font: italic; width: 165px\" value='".htmlspecialchars($reason)."'></td></tr></table></td></tr>
				<tr><td bgcolor=#faedc0><table border=0><tr><td align=right width=180><font face=verdana size=1 color=maroon>������ ������ ��:</td><td><font face=verdana size=1 color=#de0000><i>".date('d.m.Y (H:i)',$time)."</i></font></td></tr></table></td></tr>
				<tr><td colspan=2 bgcolor=#f6f4cd><br>
					<table border=0 cellspacing=0 cellpadding=1 bgcolor=#000000 align=center>
					<tr><td><input type=submit value=�������� class=submit style='width: 100px'></td></tr>
					</table><br>
				</td></tr>
				</form>
				</table><br>";
				}

				elseif(empty($_POST['ip']))
				$manlix['result'].="<center>
				<font face=verdana size=1><br><br>�� �� ������� ip �����<br><br><a href='?section=5_1&BanId=".$_POST['BanId']."'>...��������� �� ��� �����</a><br><br></font>
				</center>";

				elseif(empty($_POST['reason']))
				$manlix['result'].="<center>
				<font face=verdana size=1><br><br>�� �� ������� ������� ����������<br><br><a href='?section=5_1&BanId=".$_POST['BanId']."'>...��������� �� ��� �����</a><br><br></font>
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
						<font face=verdana size=1><br><br>������� ������� ������<br><br>(<a href='?section=5_1'>�������� ���</a>)<br><br></font>
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

	case "5_2":	$manlix['section']['name']="�������� �� ��������";

			$BanlistFile=$BanlistFile=manlix_read_file($manlix['dir']['path']."/".$manlix['dir']['inc']."/".$manlix['file']['banlist']);

			if(!count($BanlistFile))
			$manlix['result'].=<<<HTML
			<center>
			<font face=verdana size=1><br><br>� �������� ������ ���<br><br><a href="?">...��������� � ������� ����</a><br><br></font>
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
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>ip �����</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=100 bgcolor=#faad1e>
					<tr><td bgcolor=#f6f4cd align=center><font face=verdana size=1 color=maroon>�������</td></tr>
					</table>
				</td><td>
					<table border=0 cellspacing=1 cellpadding=3 align=center width=192 bgcolor=#faad1e>
					<tr><td bgcolor=#faedc0 align=center><font face=verdana size=1 color=#de0000>����� �������������</td></tr>
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
				<tr><td align=right><font face=verdana size=1>(<i><a href='?section=5_2&BanId=".$id."'>�������</a></i>)</td></tr>
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
				<font face=verdana size=1><font face=verdana size=1><br><br>��������� ���� ������ � �������� - �� ����������<br><br><a href="?section=5_2">...��������� �� ��� �����</a><br><br></font></font>
				</center>
HTML;

				else
				{
				$manlix['result'].=<<<HTML
				<center>
				<font face=verdana size=1><font face=verdana size=1><br><br>������ ������� ������� �� ��������<br><br>(<a href="?section=5_2">������� ���</a>)<br><br></font></font>
				</center>
HTML;
				$manlix['okay']=1;
				}
			}

			break;

	case "deny":	$manlix['section']['name']="��� �������";
			$manlix['result']=<<<HTML
			<table border="0" align="center" cellspacing="0" cellpadding="1" width="470">
			<tr><td><br></td></tr>
			<tr><td bgcolor="#faedca" align="center"><font face="verdana" size="1" color="maroon"><br>� ��� ��� ����� ����, ������� ��������� �� ��� �������� � ���� ������.<br><br><hr color="maroon" size="1" width="415"><br></font></td></tr>
			<tr><td bgcolor="#faedca" align="center"><font face="verdana" size="1"><a href="?">������� ����, ����� ��������� � ������� ���� ����������</a><br><br></font></td></tr>
			<tr><td><br></td></tr>
			</table>
HTML;
			break;

	default: $manlix['section']['name']="�������� ���� �� �������� ��� ���������� �����-���� ��������";

	$void=-1;
		while(list($section)=each($manlix['sections']))
		{
		$void++;
		$manlix['result'].='<table border=0 width=300 cellspacing=1 cellpadding=3 align=center bgcolor=#faad1e><caption><font face=verdana size=1 color=maroon>������ <font color=#de0000>'.$section.'</font>, �������� ��������:</font></caption>';

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

if(!isset($manlix['status']))	$manlix['status']="���� �� ��������";
?>
<html>
<head>
<title><?=$manlix['script']['name'],", ������: ",$manlix['script']['version']?> � ���������� � <?=ereg_replace("<[^>]+>", "",ucfirst($manlix['status']))?></title>
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
	<tr align=center bgcolor=#faedca><td align=center><font face=verdana size=1><a href="http://manlix.ru" target="_blank">���������� �������: Manlix</a></font></td></tr>
	</table>
</td></tr>
<?
if(!empty($manlix['access']))
{
echo "<tr><td align=right><font face=verdana size=1>(<a href='?exit'>������� ������</a>)</font></td></tr>";
}
?>
</table>
</body>
</html>
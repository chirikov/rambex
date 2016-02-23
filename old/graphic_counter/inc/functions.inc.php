<?
function addSpace($num)
{
$strlen=17-strlen($num);
$space=null;
	while($strlen) {$space.=" "; $strlen--;}
return $space.$num;
}

function manlix_char_generator($chars,$times)
{

	if(!strlen($chars))		return false;
	elseif(!is_numeric($times))	return false;

	else
	{
	$result=null;
		for($i=0;$i<$times;$i++)
		$result.=$chars[rand(0,strlen($chars)-1)];
	}

return $result;
}

function manlix_read_file($path)
{
	if(!is_file($path))		return false;
	elseif(!filesize($path))	return array();
	elseif($array=file($path))	return $array;

	else
	while(!$array=file($path))sleep(1);

	return $array;
}

function manlix_normal_numeric($number)
{

	if(!isset($number))		return false;
#	elseif(!is_numeric($number))	return false;

	else
	{
	$strlen=strlen($number);
	$new=null;

		for ($i=$strlen-1;$i>-1;$i--)
		{
			$n = $i;				$n++;

			if	(strstr($n/3,"."))	$new.=		$number[$strlen-1-$i];
			else if	($n!=$strlen)	$new.=	    " ".	$number[$strlen-1-$i];
			else			$new.=		$number[$strlen-1-$i];
		}

	return $new;
	}
}
?>
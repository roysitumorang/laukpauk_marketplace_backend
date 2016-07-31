<?php
error_reporting(0);
include '../../../config.php';
mysql_connect($App['db']['host'],$App['db']['user'],$App['db']['pass']);
mysql_select_db($App['db']['name']);
	
function listState()
{
	$baca = mysql_query("SELECT * FROM cpstate");		
	$i=0;
	while ($Baca = mysql_fetch_array($baca))
	{
		$Data[$i] = array(	'No' => ($i+1), 'Item' => $Baca	);
		$i++;
	}
	return $Data;
}

function listCity($idState)
{
	global $Db;
	$baca = mysql_query("SELECT * FROM cpcity WHERE idState='".$idState."'");		
	$i=0;
	while ($Baca = mysql_fetch_array($baca))
	{
		$Data[$i] = array(	'No' => ($i+1), 'Item' => $Baca	);
		$i++;
	}
	return $Data;
}

?>
var hide_empty_list=true;
addListGroup("lokasi", "propinsi");

<?php
$State = $_GET['state'];
$City = $_GET['city'];

$listPropinsi = listState();
for ($i=0;$i<count($listPropinsi);$i++)
{
	$Selected = ($listPropinsi[$i]['Item']['vState']==$State)?", \"".$listPropinsi[$i]['Item']['id']."\"":"";
	echo "addList(\"propinsi\", \"".$listPropinsi[$i]['Item']['vState']."\", \"".$listPropinsi[$i]['Item']['vState']."\", \"".$listPropinsi[$i]['Item']['id']."\"".$Selected.");\n";
}

for ($i=0;$i<count($listPropinsi);$i++)
{
	$listCity = listCity($listPropinsi[$i]['Item']['id']);
	for ($j=0;$j<count($listCity);$j++)
	{
		$Selected = ($listCity[$j]['Item']['vCity']==$City)?", \"".$listCity[$j]['Item']['id']."\"":"";
		echo "addOption(\"".$listPropinsi[$i]['Item']['id']."\", \"".$listCity[$j]['Item']['vCity']."\", \"".$listCity[$j]['Item']['id']."\"".$Selected.");\n";
	}
}
?>

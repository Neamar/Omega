<?php
$envContent=str_replace(' ','',$envContent);
$envContent=str_replace('&nbsp;','',$envContent);

$Param=array();

if($Env[2]=='')
	$Env[2]='lc|400x200';

$Opt=explode("|",$Env[2]);
$Param['cht']=$Opt[0];
$Param['chs']=$Opt[1];

$Alias=array(
'Type'=>'cht',
'Size'=>'chs',
'Data'=>'chd',
'Datas'=>'chd',
'Label'=>'chl',
'Labels'=>'chl',
'Legend'=>'chl',
'Title'=>'chtt',
'Titre'=>'chtt',
'Color'=>'chco',
'Colors'=>'chco',
'Axis'=>'chxt',
'AxisLabel'=>'chxl');
preg_match_all('#(\w+):(\S+);?#',$envContent,$Params,PREG_SET_ORDER);
foreach($Params as $Opt)
{
	if(isset($Alias[$Opt[1]]))
		$Param[$Alias[$Opt[1]]]=$Opt[2];
	else
		$Param[$Opt[1]]=$Opt[2];
}

if(!isset($Param['chd']))
	Typo::raiseError('Vous devez utiliser un <em>Data</em> pour un environnement chart !');

$Param['chd'] = 't:' . $Param['chd'];

$URL='http://chart.apis.google.com/chart?1';
foreach($Param as $Opt=>$Val)
	$URL .= '&amp;' . $Opt . '=' . str_replace('_','+',$Val);

$envContent='<p class="centre no_lettrine"><img class="nonflottant" alt="Graphique de données" src="' . $URL . '" /></p>';
?>
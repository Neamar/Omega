<?php
$envArr=explode("\n",$envContent);

$Question=0;
$envContent='
<form method="post" action="" class="qcm">
<h5>QCM</h5>
<div class="qcm_wrapper">
';

for($qcm_i=0;$qcm_i<count($envArr);$qcm_i++)
{
	if(substr(trim($envArr[$qcm_i]),0,9)=='\question')
	{
		$Question++;
		$envContent .='<fieldset class="qcm_question">
		<legend>Question n<sup>o</sup> ' . $Question . '</legend>

		<p class="qcm_question">' . substr(trim($envArr[$qcm_i]),10) . '</p>
		<div class="qcm_answer">' . "\n";
		$qcm_i++;
		$qcm_j=0;
		while(substr(trim($envArr[$qcm_i]),0,7)=='\answer')
		{
			$Item_ID='solution-' . $qcm_i . '-' . $qcm_j;
			$PlusHTML='';
			if((substr(trim($envArr[$qcm_i]),7,7)=='[right]'))
			{
				$PlusHTML=' class="qcm_right"';
				$envArr[$qcm_i]=substr_replace(trim($envArr[$qcm_i]),'',7,7);
			}
			$Caption=substr(trim($envArr[$qcm_i]),8);
			$envContent .= '	<input type="radio" name="question-' . $Question . '" value="' . $qcm_j . '" id="' . $Item_ID . '"' . $PlusHTML .' /><label for="' . $Item_ID . '" id="' . $Item_ID . '-label">' . $Caption . '</label><br />' . "\n";
			$qcm_i++;
			$qcm_j++;
		}
		$envContent .= '</div>' . "\n" . '</fieldset>';
	}
	elseif(trim($envArr[$qcm_i]!=''))
		Typo::RaiseWarning("Paramètre inconnu dans le QCM. Abandon.");
}

$envContent .='
</div>
<div id="qcm_score">
<p class="noLettrine centre"><input type="button" value="Notez moi !" onclick="noterQcm(this);"/></p>
</div>
</form>
';

$envContent .= '<script type="text/javascript" src="http://neamar.fr/lib/Typo/Env/qcm.js"></script>';

?>
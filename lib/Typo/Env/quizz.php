<?php
$envArr=explode("\n",$envContent);

$envContent='
<table class="quizz">
	<caption>Quizz</caption>
	<thead>
		<tr>
			<th>Question</th>
			<th>Réponse</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="2" class="centre petitTexte">(passez votre souris sur une question pour afficher la réponse)</td>
		</tr>
';

for($quizz_i=0;$quizz_i<count($envArr);$quizz_i++)
{
	if(substr(trim($envArr[$quizz_i]),0,9)=='\question' && substr(trim($envArr[$quizz_i+1]),0,7)=='\answer')
	{
 		$envContent .='		<tr>' . "\n" . '			<td>' . substr(trim($envArr[$quizz_i]),9) . '</td>' . "\n" . '			<td class="answer">' . substr(trim($envArr[$quizz_i+1]),7) . '</td>' . "\n" . '		</tr>' . "\n";
	}
}
$envContent .='
	</tbody>
</table>
';

unset($envArr);
?>
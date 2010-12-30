function noterQcm(Item)
{
	Score=0;
	Note=Item.parentNode.parentNode;
	QCM=Note.parentNode;
	Questions=QCM.getElementsByTagName('fieldset');
	for(var i=0;i<Questions.length;i++)
	{
		Reponses=Questions[i].getElementsByTagName('input');
		for(var j=0;j<Reponses.length;j++)
		{
			if(Reponses[j].className=="qcm_right")
			{
				if(Reponses[j].checked)
					Score++;
				document.getElementById(Reponses[j].id + "-label").style.color="green";
			}
			else if(Reponses[j].checked)
				document.getElementById(Reponses[j].id + "-label").style.color="red";
		}
	}
	Note.innerHTML ='<p class="noLettrine centre">Votre score : <img src="http://neamar.fr/Latex/TEX.php?m=\\frac{' + Score + '}{' + Questions.length + '}" alt="Votre note." class="TexPic"/>, ce qui correspond à <img src="http://neamar.fr/Latex/TEX.php?m=\\frac{' + Math.round((Score/Questions.length)*20) + '}{20}" alt="Votre note sur 20." class="TexPic"/>.</p>';
}
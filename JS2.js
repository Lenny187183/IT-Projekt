function weiterleiten2(){

	const buttonA = document.querySelector('input[id="yesSGBXII"]').checked;
	const buttonB = document.querySelector('input[id="yesSGBII"]').checked;
	const buttonC = document.querySelector('input[id="leistung6"]').checked;
	const buttonD = document.querySelector('input[id="rechnung1"]').checked;
	const buttonE = document.querySelector('input[id="leistung5"]').checked;

	
	if(buttonA){
		window.location.href ="https://www.nuernberg.de/internet/sozialamt/";
	}else if(buttonB){
		window.location.href = "https://www.arbeitsagentur.de/vor-ort/jobcenter/jobcenter-nuernberg-stadt-nuernberg.html";
	}else if(buttonC && buttonD){
		window.location.href = "https://www.nuernberg.de/internet/jugendamt/allgemeinersozialdienst.html";

	}else if(buttonC && buttonE){
		window.location.href = "https://www.nuernberg.de/internet/sozialamt/sozialpaedagogischerfachdienst.html";
	}else{
		alert("Bitte sehen Sie sich ihre Eingabe nochmal");
	}
	


		


}
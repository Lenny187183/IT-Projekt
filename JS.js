function validateForm() {
	var schrifteingabe = document.getElementById('schrifteingabe').value;
	var errorMessage = document.getElementById('error-message');
	if (schrifteingabe.trim() === '') {
	errorMessage.textContent = 'Bitte geben Sie eine Antwort ein.';
	return false;
	} else {
	errorMessage.textContent = '';
	return true;
	}
	}
	
	const buttonA = document.querySelector('input[id="leistung1"]').checked;
	const buttonB = document.querySelector('input[id="leistung2"]').checked;
	const buttonC = document.querySelector('input[id="leistung3"]').checked;
	const buttonD = document.querySelector('input[id="rechnung1"]').checked;
	const buttonF = document.querySelector('input[id="rechnung2"]').checked;
	const buttonG = document.querySelector('input[id="trueAltersgrenze"]').checked;
	const buttonH = document.querySelector('input[id="falseAltersgrenze"]').checked;
	const buttonI = document.querySelector('input[id="u21Ang"]').checked;
	const buttonJ = document.querySelector('input[id="ab21Ang"]').checked;
	const buttonK = document.querySelector('input[id="noAltersrente1"]').checked;
	const buttonL = document.querySelector('input[id="yesAltersrente2"]').checked;
	
	
	function weiterleiten(buttonA, buttonL){

		
		if(buttonA.checked == true && buttonL.checked == true){
			window.location.href = "https://www.nuernberg.de/internet/sozialamt/";
		}
	
	
	}
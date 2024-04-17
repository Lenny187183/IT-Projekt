function weiterleiten2() {
	// Hier überprüfen wir, welche Optionen ausgewählt wurden
	const option1 = document.querySelector('input[id ="yesSGBXII"]').checked;
	const option2 = document.querySelector('input[id ="yesSGBII"]').checked;
	const option3 = document.querySelector('input[id ="leistung7"]').checked;
	const option4 = document.querySelector('input[id ="leistung5"]').checked;



	// Je nach Kombination der Optionen kannst du den Benutzer zu verschiedenen URLs weiterleiten
	if (option1) {
		window.location.href = "https://www.nuernberg.de/internet/sozialamt";
	} else if (option2) {
		window.location.href = "https://www.sozialamt.nuernberg.de/kombination2";
	} else if (option3) {
		window.location.href = "https://www.nuernberg.de/internet/jugendamt/allgemeinersozialdienst.html";
	} else if (option4) {
		window.location.href = "https://www.nuernberg.de/internet/sozialamt/sozialpaedagogischerfachdienst.html";
	} else {
		alert("Bitte wählen Sie die Optionen aus, um fortzufahren.");
	}
}
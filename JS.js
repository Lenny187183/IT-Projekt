function weiterleiten(){

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

		function confirmAction(targetUrl, targetDialog) {
			
			
		    const dialog = document.getElementById(targetDialog);
			
			
			dialog.showModal();
		  
			const confirmBtn = document.getElementById('confirmBtn');
			confirmBtn.addEventListener('click', () => {
			  
			  window.location.href = targetUrl; 
			  dialog.close();
			});
		  
			const cancelBtn = document.getElementById('cancelBtn');
			cancelBtn.addEventListener('click', () => {
			  alert("Aktion abgebrochen.");
			  dialog.close();
			});

			const downloadBtn = document.getElementById('downloadBtn');
            downloadBtn.addEventListener('click', () => {
            const filePath = 'C:\Users\lenna\Desktop\ScrumCheatSheet.pdf'; // Replace with actual file path
            window.open(filePath, '_blank');
		    });

			

		  }
		
		
		  if(buttonC){

			confirmAction("https://www.arbeitsagentur.de/vor-ort/jobcenter/jobcenter-nuernberg-stadt-nuernberg.html", "JobcenterUndASD");
	    
		}
		else if(buttonA && buttonF && buttonI){
			confirmAction("https://www.nuernberg.de/internet/jugendamt/allgemeinersozialdienst.html", "JobcenterUndASD");
		}
		else if(buttonA && buttonF && buttonJ){
			confirmAction("https://www.nuernberg.de/internet/sozialamt/sozialpaedagogischerfachdienst.html", "ExistenzUndSFD");
		}
		else if(buttonB){
			confirmAction("https://www.nuernberg.de/internet/sozialamt/", "JobcenterUndASD");
		}else if(buttonA && buttonD && buttonG && buttonK){
			confirmAction("https://www.arbeitsagentur.de/vor-ort/jobcenter/jobcenter-nuernberg-stadt-nuernberg.html", "JobcenterUndASD");
		}else if(buttonA && buttonD && buttonH){
			confirmAction("https://www.nuernberg.de/internet/sozialamt/", "JobcenterUndASD");
		}else if(buttonA && buttonD && buttonG && buttonL){
			confirmAction("https://www.nuernberg.de/internet/sozialamt/", "JobcenterUndASD");
		}else{
			alert("Bitte sehen Sie sich ihre Eingabe nochmal an");
		}


			
}

	
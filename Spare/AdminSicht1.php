<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Admin-Bereich</title>
</head>
<body>
    <h1>Fragen bearbeiten</h1>

    <form action="FrageEingabe.php" method="post">
        <div class="form-group">
			<label for="frage">Frage:</label>
        <input type="text" id="frage" name="frage" required>
		</div>

		<div class="form-group">
			<label for="antworttyp">Antworttyp:</label>
			<div>
				<select id="antworttyp" name="antworttyp" required>
					<option value="text">Textfeld</option>
					<option value="radio">Radiobutton</option>
					<option value="select">Dropdown-Menü</option>
				</select>
			</div>
		</div>
		

        
    

        <button type="submit">Frage hinzufügen</button>


       
    </form>

    

    


    


    

    <a href="index.html">Zurück zur Hauptseite</a>


    <link rel="stylesheet" href="schön.css">
</body>
</html>

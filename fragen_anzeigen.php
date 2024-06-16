<?php
// Datenbankverbindung (gleiche Daten wie beim Einfügen)
$conn = new mysqli('localhost', 'testserver', '123', 'fragen');

// Verbindung prüfen
if (!$conn) {
    echo 'Connection error: '. mysqli_connect_error();
}

// SQL-Abfrage zum Auslesen aller Fragen
$sql = 'SELECT frage, antworttyp, id FROM fragen';

$result = mysqli_query($conn, $sql);


$fragen = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_free_result($result);

mysqli_close($conn);



?>

<!DOCTYPE html>
<html>
<h4 class="center grey-text">fragen!</h4>

<div class="container">
		<div class="row">

			<?php foreach($fragen as $fragen){ ?>

				<div class="col s6 md3">
					<div class="card z-depth-0">
						<div class="card-content center">
							<h6><?php echo htmlspecialchars($fragen['frage']); ?></h6>
							<div><?php echo htmlspecialchars($fragen['antworttyp']); ?></div>
						</div>
						
					</div>
				</div>

			<?php } ?>

		</div>
	</div>

</html>
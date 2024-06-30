<?php
// Datenbankverbindung (gleiche Daten wie beim Einfügen)
$conn = new mysqli('localhost', 'testserver', '123', 'fragen');

// Verbindung prüfen
if (!$conn) {
    echo 'Connection error: '. mysqli_connect_error();
}

// SQL-Abfrage zum Auslesen aller Fragen
$sql = 'SELECT frage, antworttyp, antwortmöglichkeit, überschrift, id FROM fragen';

$result = mysqli_query($conn, $sql);


$fragen = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_free_result($result);

mysqli_close($conn);



?>



<!DOCTYPE html>
<html>
<h4 style= "text-align: center;" class="center grey-text"><?php echo htmlspecialchars($fragen[0]['überschrift']); ?></h4>

<div class="container">
    <?php
    $aktuelleFrage = null;
    foreach ($fragen as $frage):
        if ($frage['frage'] !== $aktuelleFrage):
            $aktuelleFrage = $frage['frage'];
    ?>
            <div class="row">
                <div class="col s12">
                    <div class="card z-depth-0">
                        <div class="card-content center">
                            <h5><?php echo htmlspecialchars($frage['frage']); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        endif;

        $antwortmoeglichkeiten = explode(',', $frage['antwortmöglichkeit']);
    ?>
        <form> <?php foreach ($antwortmoeglichkeiten as $moeglichkeit):
                // Input-Typ basierend auf antworttyp bestimmen
                $inputType = ($frage['antworttyp'] === 'Checkbox') ? 'checkbox' : 'radio';
            ?>
                <div class="row">
                    <div class="col s12">
                        <label>
                            <input type="<?php echo $inputType; ?>" name="ausgewaehlte_antwort_<?php echo $frage['id']; ?>" value="<?php echo htmlspecialchars($moeglichkeit); ?>">
                            <span><?php echo htmlspecialchars($moeglichkeit); ?></span>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </form> <?php endforeach; ?>
</div>

<link rel="stylesheet" href="schön.css">
</html>

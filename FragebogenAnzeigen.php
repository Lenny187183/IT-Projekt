<h2><?php echo $fragebogenTitel; ?></h2>

<form method="post" action="FragebogenVerarbeiten.php">
    <?php foreach ($fragen as $frage): ?>
        <div class="frage">
            <h3><?php echo $frage['fragetext']; ?></h3>

            <?php
            // Antworten zur Frage laden
            $antwort = new Antwort();
            $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frage['id']);

            foreach ($antworten as $antwort): 
            ?>
                <label>
                    <input type="radio" name="frage_<?php echo $frage['id']; ?>" value="<?php echo $antwort['id']; ?>" required>
                    <?php echo $antwort['antworttext']; ?>
                </label><br>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <button type="submit">Absenden</button>
</form>
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiterbereich - Sozialamt Nürnberg</title>
    <link rel="stylesheet" href="Startseite.css">
</head>
<body>
    <header>
        <h1>Willkommen im Mitarbeiterbereich des Sozialamts Nürnberg</h1>
    </header>

    <main>
        <section id="mitarbeiter">
            <h2>Mitarbeiter-Login</h2>
            <?php if (isset($_SESSION['benutzer_id'])): ?>
                <p>Sie sind bereits angemeldet.</p>
                <a href="FragebogenErstellen.php">Fragebögen verwalten</a>
                <br>
                <a href="logout.php">Abmelden</a>
            <?php else: ?>
                <button onclick="window.location.href='Login.html'">Anmelden</button>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Sozialamt Nürnberg</p>
    </footer>
</body>
</html>
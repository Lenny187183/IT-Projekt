<?php
class antwort {
    private $id;
    private $frage_id;
    private $antworttext;

    // Konstruktor
    public function __construct($id, $frage_id, $antworttext) {
        $this->id = $id;
        $this->frage_id = $frage_id;
        $this->antworttext = $antworttext;
    }

    // Getter und Setter
    public function getId() {
        return $this->id;
    }

    public function getFrageId() {
        return $this->frage_id;
    }

    public function getAntworttext() {
        return $this->antworttext;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setFrageId($frage_id) {
        $this->frage_id = $frage_id;
    }

    public function setAntworttext($antworttext) {
        $this->antworttext = $antworttext;
    }

	// Methoden zum Laden und Speichern in der Datenbank
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM antwort WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->id = $row["id"];
            $this->frage_id = $row["frage_id"];
            $this->antworttext = $row["antworttext"];
        }
    }

    public function speichernInDatenbank($conn) {
        if ($this->id == null) {
            // Neue Antwort einfügen
            $sql = "INSERT INTO antwort (frage_id, antworttext) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $this->frage_id, $this->antworttext);
        } else {
            // Bestehende Antwort aktualisieren
            $sql = "UPDATE antwort SET frage_id = ?, antworttext = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $this->frage_id, $this->antworttext, $this->id);
        }

        $stmt->execute();
    }
}
?>
<?php

class antwort { 
    private $id;
    private $frage_id;
    private $antworttext;
    private $ziel_url; 

    // Konstruktor
    public function __construct($id = null, $frage_id = null, $antworttext = null, $ziel_url = null) {
        $this->id = $id;
        $this->frage_id = $frage_id;
        $this->antworttext = $antworttext;
        $this->ziel_url = $ziel_url;
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

    public function getZielUrl(){
        return $this->ziel_url;
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
    
    public function setZielUrl($ziel_url){
        $this->ziel_url = $ziel_url;
    }

    // Methoden zum Laden und Speichern in der Datenbank
    
    public function ladenAntwortenFuerFrage($conn, $frageId) {
        $sql = "SELECT * FROM antwort WHERE frage_id = ?"; // Alle Spalten auswählen
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $frageId);
        $stmt->execute();
        $result = $stmt->get_result();

        $antworten = [];
        while ($row = $result->fetch_assoc()) {
            $antwort = new Antwort();
            $antwort->setId($row['id']);
            $antwort->setAntworttext($row['antworttext']);
            $antwort->setFrageId($row['frage_id']);
            $antwort->setZielUrl($row['ziel_url']); // Ziel-URL laden
            $antworten[] = $antwort;
        }

        return $antworten;
    }
    
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM antwort WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc(); 

            $this->id = $row['id'];
            $this->antworttext = $row['antworttext'];
            $this->frageId = $row['frage_id'];
            $this->ziel_url = $row['ziel_url']; // Ziel-URL laden
        } else {
            return false; // Antwort nicht gefunden
        }
    }

    // Methode zum Speichern einer Antwort in der Datenbank
    public function speichernInDatenbank($conn) {
        if ($this->id) {
            // Update
            $sql = "UPDATE antwort SET antworttext = ?, frage_id = ?, ziel_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisi", $this->antworttext, $this->frageId, $this->ziel_url, $this->id);
        } else {
            // Insert
            $sql = "INSERT INTO antwort (antworttext, frage_id, ziel_url) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sis", $this->antworttext, $this->frageId, $this->ziel_url);
        }
    
        if ($stmt->execute()) {
            if (!$this->id) {
                $this->id = $conn->insert_id;
            }
            return true;
        } else {
            return false; // Fehler beim Speichern
        }
    }
}
?>
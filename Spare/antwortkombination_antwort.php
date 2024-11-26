<?php

class AntwortkombinationAntwort  {
    private $id;
    private $antwortkombination_id;
    private $antwort_id;

    // Konstruktor
    public function __construct($id = null, $antwortkombination_id = null, $antwort_id = null) {
        $this->id = $id;
        $this->antwortkombination_id = $antwortkombination_id;
        $this->antwort_id = $antwort_id;
    }

    // Getter und Setter
    public function getId() {
        return $this->id;
    }

    public function getAntwortkombinationId() {
        return $this->antwortkombination_id;
    }

    public function getAntwortId() {
        return $this->antwort_id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAntwortkombinationId($antwortkombination_id) {
        $this->antwortkombination_id = $antwortkombination_id;
    }

    public function setAntwortId($antwort_id) {
        $this->antwort_id = $antwort_id;
    }

    // Methoden zum Laden und Speichern in der Datenbank
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM antwortkombination_antwort WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc(); 

            $this->id = $row["id"];
            $this->antwortkombination_id = $row["antwortkombination_id"];
            $this->antwort_id = $row["antwort_id"];
        } else {
            throw new Exception("Antwortkombination_Antwort nicht gefunden."); 
        }
    }

    public function speichernInDatenbank($conn) {
        if ($this->id == null) {
            // Neuen Eintrag einfügen
            $sql = "INSERT INTO antwortkombination_antwort (antwortkombination_id, antwort_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $this->antwortkombination_id, $this->antwort_id);
        } else {
            // Bestehenden Eintrag aktualisieren (eher selten benötigt)
            $sql = "UPDATE antwortkombination_antwort SET antwortkombination_id = ?, antwort_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $this->antwortkombination_id, $this->antwort_id, $this->id);
        }
    
        if ($stmt->execute()) {
            // Wenn es sich um einen neuen Eintrag handelt, setze die ID
            if ($this->id == null) { 
                $this->id = $stmt->insert_id; 
            }
            return true; // Erfolg
        } else {
            return false; // Fehler
        }
    } // <-- Removed the extra closing curly brace here
}
?>
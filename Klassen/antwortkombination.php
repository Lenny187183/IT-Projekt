<?php
class Antwortkombination {
    private $id;
    private $weiterleitung_id;

    // Konstruktor
    public function __construct($id = null, $weiterleitung_id = null) {
        $this->id = $id;
        $this->weiterleitung_id = $weiterleitung_id;
    }

    // Getter und Setter
    public function getId() {
        return $this->id;
    }

    public function getWeiterleitungId() {
        return $this->weiterleitung_id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setWeiterleitungId($weiterleitung_id) {
        $this->weiterleitung_id = $weiterleitung_id;
    }

    // Methoden zum Laden und Speichern in der Datenbank
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM antwortkombination WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();   

            $this->id = $row["id"];
            $this->weiterleitung_id   
 = $row["weiterleitung_id"];
        } else {
            throw new Exception("Antwortkombination nicht gefunden."); 
        }
    }

    public function speichernInDatenbank($conn) {
        if ($this->id == null) {
            // Neue Antwortkombination einfügen
            $sql = "INSERT INTO antwortkombination (weiterleitung_id) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $this->weiterleitung_id);
        } else {
            // Bestehende Antwortkombination aktualisieren
            $sql = "UPDATE antwortkombination SET weiterleitung_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $this->weiterleitung_id, $this->id);
        }

        if ($stmt->execute()) {
            // Wenn es sich um eine neue Antwortkombination handelt, setze die ID
            if ($this->id == null) {
                $this->id = $stmt->insert_id;
            }
            return true; // Erfolg
        } else {
            return false; // Fehler
        }
    }

    // Methode zum Hinzufügen von Antworten zu dieser Kombination
    public function antwortenHinzufuegen($conn, $antwortIds) {
        $stmt = $conn->prepare("INSERT INTO antwortkombination_antwort (antwortkombination_id, antwort_id) VALUES (?, ?)");
        foreach ($antwortIds as $antwortId) {
            $stmt->bind_param("ii", $this->id, $antwortId);
            $stmt->execute();
        }
    }
}
?>
<?php
class antwortkombination {
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
            $this->weiterleitung_id = $row["weiterleitung_id"];
        } else {
            // Option 1: Throw an exception
            // throw new Exception("Antwortkombination nicht gefunden.");

            // Option 2: Return false
            return false; 
        }
    }

    public function speichernInDatenbank($conn) {
        // ... (your existing code for insert/update)

        if ($stmt->execute()) {
            // ... 
        } else {
            // Option 1: Throw an exception
            // throw new Exception("Fehler beim Speichern der Antwortkombination: " . $stmt->error);

            // Option 2: Return false
            return false; 
        }
    }

    // Methode zum Hinzufügen von Antworten zu dieser Kombination
    public function antwortenHinzufuegen($conn, $antwortIds) {
        $stmt = $conn->prepare("INSERT INTO antwortkombination_antwort (antwortkombination_id, antwort_id) VALUES (?, ?)");
        foreach ($antwortIds as $antwortId) {
            $stmt->bind_param("ii", $this->id, $antwortId);
            if (!$stmt->execute()) {
                // Option 1: Throw an exception
                // throw new Exception("Fehler beim Hinzufügen der Antwort zur Kombination: " . $stmt->error);

                // Option 2: Return false
                return false; 
            }
        }
        return true; // Erfolg
    }

    // Methode zum Abrufen der zugehörigen Antworten
    public function getAntworten($conn) {
        $sql = "SELECT antwort_id FROM antwortkombination_antwort WHERE antwortkombination_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC); 
    }
}
}
?>
<?php
class antwortkombination {
    private $id;
    private $ziel_url; 

    // Constructor
    public function __construct($id = null, $ziel_url = null) {
        $this->id = $id;
        $this->ziel_url = $ziel_url;
    }

    // Getters and Setters
    public function getId() {
        return $this->id;
    }

    public function getZielUrl() {
        return $this->ziel_url;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setZielUrl($ziel_url) {
        $this->ziel_url = $ziel_url;
    }

    // Database interaction methods
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM antwortkombination WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc(); 

            $this->id = $row["id"];
            $this->ziel_url = $row["ziel_url"];
        } else {
            throw new Exception("Antwortkombination nicht gefunden."); 
        }
    }

    public function speichernInDatenbank($conn) {
        if ($this->id == null) {
            // Neue Antwortkombination einfügen
            $sql = "INSERT INTO antwortkombination (ziel_url) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $this->ziel_url); 
        } else {
            // Bestehende Antwortkombination aktualisieren
            $sql = "UPDATE antwortkombination SET ziel_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $this->ziel_url, $this->id);
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

    // Method to add answers to this combination
    public function antwortenHinzufuegen($conn, $antwortIds) {
        $stmt = $conn->prepare("INSERT INTO antwortkombination_antwort (antwortkombination_id, antwort_id) VALUES (?, ?)");
        foreach ($antwortIds as $antwortId) {
            $stmt->bind_param("ii", $this->id, $antwortId);
            $stmt->execute();
        }
    }
}
?>


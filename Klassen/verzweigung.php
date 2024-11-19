
<?php
class verzweigung {
    private $id;
    private $antwortId;
    private $folgefrageId;
    private $parentFrageId;

    public function __construct($antwortId = null, $folgefrageId = null, $parentFrageId = null) {
        $this->antwortId = $antwortId;
        $this->folgefrageId = $folgefrageId;
        $this->parentFrageId = $parentFrageId;
    }

    // Getter und Setter für alle Eigenschaften
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getAntwortId() {
        return $this->antwortId;
    }

    public function setAntwortId($antwortId) {
        $this->antwortId = $antwortId;
    }

    public function getFolgefrageId() {
        return $this->folgefrageId;
    }

    public function setFolgefrageId($folgefrageId) {
        $this->folgefrageId = $folgefrageId;
    }

    public function getParentFrageId() {
        return $this->parentFrageId;
    }

    public function setParentFrageId($parentFrageId) {
        $this->parentFrageId = $parentFrageId;
    }

    // Methode zum Laden einer Verzweigung aus der Datenbank
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM verzweigung WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id); // Nur die ID binden
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->antwortId = $row['antwort_id'];
            $this->folgefrageId = $row['folgefrage_id'];
            $this->parentFrageId = $row['parent_frage_id'];
        } else {
            return false; // Verzweigung nicht gefunden
        }
    }

    // Methode zum Speichern einer Verzweigung in der Datenbank
    public function speichernInDatenbank($conn) {
        if ($this->id) {
            // Update
            if ($this->folgefrageId === "") { 
                $this->folgefrageId = null;
            }
            $sql = "UPDATE verzweigung SET antwort_id = ?, folgefrage_id = ?, parent_frage_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $this->antwortId, $this->folgefrageId, $this->parentFrageId, $this->id);
        } else {
            // Insert
            if ($this->folgefrageId === "") {
                $this->folgefrageId = null;
            }
            $sql = "INSERT INTO verzweigung (antwort_id, folgefrage_id, parent_frage_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $this->antwortId, $this->folgefrageId, $this->parentFrageId);
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

    public function ladenAusDatenbankMitAntwortId($conn, $antwortId) {
        $sql = "SELECT * FROM verzweigung WHERE antwort_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $antwortId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->id = $row['id']; // ID aus der Datenbank laden
            $this->antwortId = $row['antwort_id'];
            $this->folgefrageId = $row['folgefrage_id'];
            $this->parentFrageId = $row['parent_frage_id'];
            return true; // Verzweigung gefunden
        } else {
            return false; // Verzweigung nicht gefunden
        }
    }


    public function setParentFrageIdAusAntwortId($conn, $antwortId) {
        $sql = "SELECT id, frage_id FROM antwort WHERE id = ?"; 
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $antwortId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            var_dump($row);
            $this->parentFrageId = $row['frage_id'];
        } else {
            $this->parentFrageId = null; // Oder eine andere Fehlerbehandlung
        }
    }


    
}

?>
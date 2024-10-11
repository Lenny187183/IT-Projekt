<?php


class fragebogen {
    private $id;
    private $titel;
    private $aktiv;

    // Konstruktor
    public function __construct($id = null, $titel = null, $aktiv = false) {
        $this->id = $id;
        $this->titel = $titel;
        $this->aktiv = is_bool($aktiv) ? $aktiv : false;    
    }

    // Getter und Setter
    public function getId() {
        return $this->id;
    }

    public function getTitel() {
        return $this->titel;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTitel($titel) {
        $this->titel = $titel;
    }
    
    public function getAktiv() {
        return $this->aktiv;
    }

    public function setAktiv($aktiv) {
        // Nur setzen, wenn der übergebene Wert ein Boolean ist
        if (is_bool($aktiv)) {
            $this->aktiv = $aktiv;
        } else {
            echo "Error: aktiv muss ein Boolean-Wert sein.";
        }
    }



    // Methoden zum Laden und Speichern in der Datenbank
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM fragebogen WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->id = $row["id"];
            $this->titel = $row["titel"];
        }
    }

    public function speichernInDatenbank($conn) {
        if ($this->id == null) {
            // Neuen Fragebogen einfügen
            $sql = "INSERT INTO fragebogen (titel) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $this->titel);
        } else {
            // Bestehenden Fragebogen aktualisieren
            $sql = "UPDATE fragebogen SET titel = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $this->titel, $this->id);
        }

        $stmt->execute();
    }


    public function getFragebogenDropdownOptions($conn) {
        $sql = "SELECT id, titel FROM fragebogen";
        $result = $conn->query($sql);

        if (!$result) {
            die("Fehler bei der Abfrage: " . $conn->error); 
        }

        $fragebogen = $result->fetch_all(MYSQLI_ASSOC);

        $options = '';
        foreach ($fragebogen as $fb) {
            $options .= "<option value='{$fb['id']}'>{$fb['titel']}</option>";
        }
        return $options;
    }
}





?>
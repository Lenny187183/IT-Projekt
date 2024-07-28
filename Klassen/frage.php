
<?php
class frage {
    private $id;
    private $fragebogen_id;
    private $fragetext;

    // Konstruktor
    public function __construct($id = null, $fragebogen_id = null, $fragetext = null) {
        $this->id = $id;
        $this->fragebogen_id = $fragebogen_id;
        $this->fragetext = $fragetext;
    }

    // Getter und Setter
    public function getId() {
        return $this->id;
    }

    public function getFragebogenId() {
        return $this->fragebogen_id;
    }

    public function getFragetext() {
        return $this->fragetext;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setFragebogenId($fragebogen_id) {
        $this->fragebogen_id = $fragebogen_id;
    }

    public function setFragetext($fragetext) {
        $this->fragetext = $fragetext;
    }

    // Methoden zum Laden und Speichern in der Datenbank
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM frage WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->id = $row["id"];
            $this->fragebogen_id = $row["fragebogen_id"];
            $this->fragetext = $row["fragetext"];
        }
    }

    public function speichernInDatenbank($conn) {
        if ($this->id == null) {
            // Neue Frage einfügen
            $sql = "INSERT INTO frage (fragebogen_id, fragetext) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $this->fragebogen_id, $this->fragetext);
        } else {
            // Bestehende Frage aktualisieren
            $sql = "UPDATE frage SET fragebogen_id = ?, fragetext = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $this->fragebogen_id, $this->fragetext, $this->id);
        }

        $stmt->execute();
    }
}
?>

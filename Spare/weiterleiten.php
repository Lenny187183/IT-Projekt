<?php
class weiterleiten {
    private $id;
    private $antwort_id;
    private $ziel_url;

    // Konstruktor
    public function __construct($id = null, $antwort_id = null, $ziel_url = null) {
        $this->id = $id;
        $this->antwort_id = $antwort_id;
        $this->ziel_url = $ziel_url;
    }

    // Getter und Setter
    public function getId() {
        return $this->id;
    }

    public function getAntwortId() {
        return $this->antwort_id;
    }

    public function getZielUrl() {
        return $this->ziel_url;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAntwortId($antwort_id) {
        $this->antwort_id = $antwort_id;
    }

    public function setZielUrl($ziel_url) {
        $this->ziel_url = $ziel_url;
    }

    // Methoden zum Laden und Speichern in der Datenbank
    public function ladenAusDatenbank($conn, $id) {
        $sql = "SELECT * FROM weiterleitung WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->id = $row["id"];
            $this->antwort_id = $row["antwort_id"];
            $this->ziel_url = $row["ziel_url"];
        }
    }

    public function speichernInDatenbank($conn) {
        if ($this->id == null) {
            // Neue Weiterleitung einfügen
            $sql = "INSERT INTO weiterleitung (antwort_id, ziel_url) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $this->antwort_id, $this->ziel_url);
        } else {
            // Bestehende Weiterleitung aktualisieren
            $sql = "UPDATE weiterleitung SET antwort_id = ?, ziel_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $this->antwort_id, $this->ziel_url, $this->id);
        }

        $stmt->execute();
    }
}
?>


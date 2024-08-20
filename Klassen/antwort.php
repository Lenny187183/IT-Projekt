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
    

    
}
?>
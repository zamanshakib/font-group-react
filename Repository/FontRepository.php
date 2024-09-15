<?php

class FontRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function isFontExists($fileName, $filePath)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM fonts WHERE name=:name OR path=:path");
        $stmt->execute([
            'name' => $fileName,
            'path' => $filePath
        ]);

        return $stmt->fetchColumn() > 0;
    }

    public function saveFont($fileName, $filePath)
    {
        $stmt = $this->db->prepare("INSERT INTO fonts (name, path) VALUES (:name, :path)");
        $stmt->execute([
            'name' => $fileName,
            'path' => $filePath
        ]);
        return $this->db->lastInsertId();
    }
}

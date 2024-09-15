<?php

class FontGroupRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function groupExists($groupName)
    {
        $stmt = $this->db->prepare("SELECT * FROM font_groups WHERE name = :groupName");
        $stmt->execute([
            'groupName' => $groupName
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveGroup($groupName)
    {
        $stmt = $this->db->prepare("INSERT INTO font_groups (name) VALUES (:groupName)");
        $stmt->execute([
            'groupName' => $groupName
        ]);
        return $this->db->lastInsertId();
    }

    public function mapFontsToGroup($groupId, array $fonts)
    {
        foreach ($fonts as $font) {
            $stmt = $this->db->prepare("INSERT INTO font_group_mapping (group_id, font_id) VALUES (:groupId, :fontId)");
            $stmt->execute([
                'groupId' => $groupId,
                'fontId' => (int) $font['fontId']
            ]);
        }
    }
    public function findGroupById($groupId)
    {
        $stmt = $this->db->prepare("SELECT * FROM font_groups WHERE id = :groupId");
        $stmt->execute(['groupId' => $groupId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateGroupName($groupId, $groupName)
    {
        $stmt = $this->db->prepare("UPDATE font_groups SET name = :groupName WHERE id = :groupId");
        $stmt->execute([
            'groupName' => $groupName,
            'groupId' => $groupId
        ]);
    }

    public function deleteExistingFonts($groupId)
    {
        $stmt = $this->db->prepare("DELETE FROM font_group_mapping WHERE group_id = :groupId");
        $stmt->execute(['groupId' => $groupId]);
    }
}

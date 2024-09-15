<?php

class FontGroupService
{
    private $fontGroupRepository;

    public function __construct(FontGroupRepository $fontGroupRepository)
    {
        $this->fontGroupRepository = $fontGroupRepository;
    }

    public function saveFontGroup($groupName, array $fonts)
    {
        if ($this->fontGroupRepository->groupExists($groupName)) {
            throw new Exception('Group name already exists');
        }

        $groupId = $this->fontGroupRepository->saveGroup($groupName);
        $this->fontGroupRepository->mapFontsToGroup($groupId, $fonts);

        return [
            'status' => 'success',
            'message' => 'Fonts Group Created successfully',
            'fonts' => $fonts
        ];
    }

    public function updateFontGroup($groupId, $groupName, array $fonts)
    {
        $existingGroup = $this->fontGroupRepository->findGroupById($groupId);

        if (!$existingGroup) {
            throw new Exception('Group not found');
        }

        $this->fontGroupRepository->updateGroupName($groupId, $groupName);
        $this->fontGroupRepository->deleteExistingFonts($groupId);
        $this->fontGroupRepository->mapFontsToGroup($groupId, $fonts);

        return [
            'status' => 'success',
            'message' => 'Font group updated successfully',
            'fonts' => $fonts
        ];
    }
}

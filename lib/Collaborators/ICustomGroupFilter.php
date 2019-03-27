<?php

namespace OCA\ReciaCustom\Collaborators;

use OCP\IGroup;

/**
 * Service interface that allows custom filtering on collaborators through groups.
 */
interface ICustomGroupFilter
{
    /**
     * @param IGroup[] $groups
     * @param string|null $search
     * @return IGroup[]
     */
    public function filterGroups(array $groups, $search = null);
}

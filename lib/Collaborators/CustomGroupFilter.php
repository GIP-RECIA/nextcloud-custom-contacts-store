<?php

namespace OCA\ReciaCustom\Collaborators;

use OCP\IConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUserSession;

/**
 * Default implementation for collaborator groups custom filtering.
 */
class CustomGroupFilter implements ICustomGroupFilter
{
    /**
     * CustomGroupFilter constructor.
     *
     * @param IConfig $config
     * @param IGroupManager $groupManager
     * @param IUserSession $userSession
     */
    public function __construct(IConfig $config, IGroupManager $groupManager, IUserSession $userSession)
    {
        $this->config = $config;
        $this->groupManager = $groupManager;
        $this->userSession = $userSession;
    }

    /**
     * @param IGroup[] $groups
     * @param string|null $search
     * @return IGroup[]
     */
    public function filterGroups(array $groups, $search = null)
    {
        return array_filter($groups, function (IGroup $group) use ($search) {
            if ($search && $group->getDisplayName() &&
                strpos(strtolower($group->getDisplayName()), strtolower($search)) === false) {
                return false;
            }

            if ($group->getGID() === 'admin') {
                return false;
            }

            return true;
        });
    }
}
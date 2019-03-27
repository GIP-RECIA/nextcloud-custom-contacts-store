<?php


namespace OCA\ReciaCustom\Collaborators;


use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;

/**
 * Default implementation for collaborators user custom filtering.
 */
class CustomUserFilter implements ICustomUserFilter
{
    /** @var IConfig */
    private $config;

    /** @var IUserManager */

    private $userManager;

    /** @var IGroupManager */
    private $groupManager;

    /** @var IUserSession */
    private $userSession;

    /**
     * CustomUserFilter constructor.
     *
     * @param IConfig $config
     * @param IUserManager $userManager
     * @param IGroupManager $groupManager
     * @param IUserSession $userSession
     */
    public function __construct(IConfig $config, IUserManager $userManager, IGroupManager $groupManager, IUserSession $userSession)
    {
        $this->config = $config;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->userSession = $userSession;
    }

    /**
     * @param IUser[]|string[] $users
     * @param string|null $search
     * @return IUser[]|string[]
     */
    public function filterUsers(array $users, $search = null)
    {
        return array_filter($users, function ($user) {
            if ($user instanceof IUser) {
                $displayName = $user->getDisplayName();
            } else {
                $displayName = $user;
            }

            if ($displayName === 'admin') {
                return false;
            }

            return true;
        });
    }
}
<?php

namespace OCA\ReciaCustom\Collaborators;

use OC\Collaboration\Collaborators\Search;
use OC\Collaboration\Collaborators\UserPlugin;
use OCP\Collaboration\Collaborators\ISearchPlugin;
use OCP\Collaboration\Collaborators\ISearchResult;
use OCP\Collaboration\Collaborators\SearchResultType;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Share;

/**
 * Fork of default collaborators search UserPlugin
 *
 * @see UserPlugin
 * @see Search
 */
class CustomUserPlugin implements ISearchPlugin
{
    /* @var bool */
    protected $shareWithGroupOnly;
    protected $shareeEnumeration;

    /** @var IConfig */
    private $config;
    /** @var IGroupManager */
    private $groupManager;
    /** @var IUserSession */
    private $userSession;
    /** @var IUserManager */
    private $userManager;
    /** @var ICustomGroupFilter */
    private $customGroupFilter;
    /** @var ICustomUserFilter */
    private $customUserFilter;

    public function __construct(IConfig $config, IUserManager $userManager, IGroupManager $groupManager, IUserSession $userSession, ICustomGroupFilter $customGroupFilter, ICustomUserFilter $customUserFilter)
    {
        $this->config = $config;

        $this->groupManager = $groupManager;
        $this->userSession = $userSession;
        $this->userManager = $userManager;
        $this->customGroupFilter = $customGroupFilter;
        $this->customUserFilter = $customUserFilter;

        $this->shareWithGroupOnly = $this->config->getAppValue('core', 'shareapi_only_share_with_group_members', 'no') === 'yes';
        $this->shareeEnumeration = $this->config->getAppValue('core', 'shareapi_allow_share_dialog_user_enumeration', 'yes') === 'yes';
    }

    public function search($search, $limit, $offset, ISearchResult $searchResult)
    {
        $result = ['wide' => [], 'exact' => []];
        $users = [];
        $hasMoreResults = false;

        $userGroups = [];
        if ($this->shareWithGroupOnly) {
            // Search in all the groups this user is part of
            $userGroups = $this->groupManager->getUserGroups($this->userSession->getUser());
            $userGroups = $this->customGroupFilter->filterGroups($userGroups, $search);

            foreach ($userGroups as $userGroup) {
                $usersTmp = $this->groupManager->displayNamesInGroup($userGroup->getGID(), $search, $limit, $offset);
                foreach ($usersTmp as $uid => $userDisplayName) {
                    $users[$uid] = $userDisplayName;
                }
            }
        } else {
            // Search in all users
            $usersTmp = $this->userManager->searchDisplayName($search, $limit, $offset);

            foreach ($usersTmp as $user) {
                if ($user->isEnabled()) { // Don't keep deactivated users
                    $users[$user->getUID()] = $user->getDisplayName();
                }
            }
        }
        $users = $this->customUserFilter->filterUsers($users, $search);

        $this->takeOutCurrentUser($users);

        if (!$this->shareeEnumeration || count($users) < $limit) {
            $hasMoreResults = true;
        }

        $foundUserById = false;
        $lowerSearch = strtolower($search);
        foreach ($users as $uid => $userDisplayName) {
            if (strtolower($uid) === $lowerSearch || strtolower($userDisplayName) === $lowerSearch) {
                if (strtolower($uid) === $lowerSearch) {
                    $foundUserById = true;
                }
                $result['exact'][] = [
                    'label' => $userDisplayName,
                    'value' => [
                        'shareType' => Share::SHARE_TYPE_USER,
                        'shareWith' => $uid,
                    ],
                ];
            } else {
                $result['wide'][] = [
                    'label' => $userDisplayName,
                    'value' => [
                        'shareType' => Share::SHARE_TYPE_USER,
                        'shareWith' => $uid,
                    ],
                ];
            }
        }

        if ($offset === 0 && !$foundUserById) {
            // On page one we try if the search result has a direct hit on the
            // user id and if so, we add that to the exact match list
            $user = $this->userManager->get($search);
            if ($user && count($this->customUserFilter->filterUsers([$user])) === 0) {
                $user = null;
            }
            if ($user instanceof IUser) {
                $addUser = true;

                if ($this->shareWithGroupOnly) {
                    // Only add, if we have a common group
                    $commonGroups = array_intersect($userGroups, $this->groupManager->getUserGroupIds($user));
                    $addUser = !empty($commonGroups);
                }

                if ($addUser) {
                    $result['exact'][] = [
                        'label' => $user->getDisplayName(),
                        'value' => [
                            'shareType' => Share::SHARE_TYPE_USER,
                            'shareWith' => $user->getUID(),
                        ],
                    ];
                }
            }
        }

        if (!$this->shareeEnumeration) {
            $result['wide'] = [];
        }

        $type = new SearchResultType('users');
        $searchResult->addResultSet($type, $result['wide'], $result['exact']);

        return $hasMoreResults;
    }

    public function takeOutCurrentUser(array &$users)
    {
        $currentUser = $this->userSession->getUser();
        if (!is_null($currentUser)) {
            if (isset($users[$currentUser->getUID()])) {
                unset($users[$currentUser->getUID()]);
            }
        }
    }
}
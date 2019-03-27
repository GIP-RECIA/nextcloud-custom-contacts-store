<?php

namespace OCA\ReciaCustom\Collaborators;

use OCP\IUser;

/**
 * Service interface that allows custom filtering on collaborators through users.
 */
interface ICustomUserFilter
{
    /**
     * @param IUser[]|string[] $users
     * @param string|null $search
     * @return IUser[]|string[]
     */
    public function filterUsers(array $users, $search = null);
}

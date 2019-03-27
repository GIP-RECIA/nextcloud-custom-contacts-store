<?php

namespace OCA\ReciaCustom\LDAP;

use OCA\User_LDAP\ILDAPGroupPlugin;
use OCP\GroupInterface;

/**
 * LDAPGroupPlugin that adds displayName to groups
 */
class LDAPGroupPlugin implements ILDAPGroupPlugin
{

    /**
     * Check if plugin implements actions
     * @return int
     *
     * Returns the supported actions as int to be
     * compared with OC_GROUP_BACKEND_CREATE_GROUP etc.
     */
    public function respondToActions()
    {
        return GroupInterface::GROUP_DETAILS;
    }

    /**
     * @param string $gid
     * @return string|null The group DN if group creation was successful.
     */
    public function createGroup($gid)
    {
    }

    /**
     * delete a group
     * @param string $gid gid of the group to delete
     * @return bool
     */
    public function deleteGroup($gid)
    {
    }

    /**
     * Add a user to a group
     * @param string $uid Name of the user to add to group
     * @param string $gid Name of the group in which add the user
     * @return bool
     *
     * Adds a user to a group.
     */
    public function addToGroup($uid, $gid)
    {
    }

    /**
     * Removes a user from a group
     * @param string $uid Name of the user to remove from group
     * @param string $gid Name of the group from which remove the user
     * @return bool
     *
     * removes the user from a group.
     */
    public function removeFromGroup($uid, $gid)
    {
    }

    /**
     * get the number of all users matching the search string in a group
     * @param string $gid
     * @param string $search
     * @return int|false
     */
    public function countUsersInGroup($gid, $search = '')
    {
    }

    /**
     * get an array with group details
     * @param string $gid
     * @return array|false
     */
    public function getGroupDetails($gid)
    {
        $group_parts = explode(':', $gid);
        if (count($group_parts) >= 3 && $group_parts[0] === 'esco' && $group_parts[1] === 'Etablissements') {
            $etab = $group_parts[2];
            if (count($group_parts) < 4) {
                return ['displayName' => "$etab"];
            }
            $type = $group_parts[3];
            if (count($group_parts) < 5) {
                return ['displayName' => "$type ($etab)"];
            }
            $categorie = $group_parts[4];
            if (count($group_parts) < 6) {
                return ['displayName' => "$categorie ($etab)"];
            }
        }
        return null;
    }
}


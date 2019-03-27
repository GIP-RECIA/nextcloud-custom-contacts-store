<?php

namespace OCA\ReciaCustom\Service;

use OC\Contacts\ContactsMenu\ContactsStore;
use OCP\Contacts\ContactsMenu\IContactsStore;
use OCP\Contacts\ContactsMenu\IEntry;
use OCP\IUser;
use OCP\IUserSession;

/**
 * A custom implementation of IContactsStore that supports more filtering capabilities.
 *
 * @see ContactsStore
 */
class CustomContactsStore implements IContactsStore
{
    /**
     * @var IContactsStore Default contact store that is used to implement delegation pattern.
     */
    private $defaultContactStore;

    /**
     * CustomContactStore constructor.
     * @param IContactsStore $defaultContactStore Default contact store that is used to implement delegation pattern.
     */
    public function __construct(IContactsStore $defaultContactStore, IUserSession $userSession)
    {
        $this->defaultContactStore = $defaultContactStore;
        $this->userSession = $userSession;
    }

    public function getContacts(IUser $user, $filter)
    {
        $entries = $this->defaultContactStore->getContacts($user, $filter);
        $entries = $this->customFilterContacts($user, $entries);
        return $entries;
    }

    public function findOne(IUser $user, $shareType, $shareWith)
    {
        $entry = $this->defaultContactStore->findOne($user, $shareType, $shareWith);
        if ($entry) {
            $entries = $this->customFilterContacts($user, [$entry]);
            if (count($entries) > 0) {
                $entry = $entries[0];
            } else {
                $entry = NULL;
            }
        }
        return $entry;
    }

    /**
     * Filter contact entries based on logged in user.
     *
     * @param IUser $user
     * @param IEntry[] $entries
     * @return IEntry[]
     */
    private function customFilterContacts(IUser $user, array $entries)
    {
        return array_values(array_filter($entries, function (IEntry $entry) use ($user) {
            return strpos(strtolower($entry->getFullName()), "cor") >= 0;
        }));
    }
}
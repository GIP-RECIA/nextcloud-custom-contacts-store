<?php

use OC\Server;
use OCA\ReciaCustom\Collaborators\CustomGroupPlugin;
use OCA\ReciaCustom\Collaborators\CustomUserPlugin;
use OCA\ReciaCustom\LDAP\LDAPGroupPlugin;
use OCA\ReciaCustom\Service\CustomContactsStore;
use OCA\User_LDAP\GroupPluginManager;
use OCP\AppFramework\QueryException;
use OCP\Contacts\ContactsMenu\IContactsStore;

/**
 * Use decorator pattern to implement custom features on internal services managing contacts and users.
 *
 * This currently use internal container API feature from Pimple container "Modifying services after definition",
 * https://pimple.symfony.com/#modifying-services-after-definition, but a pull request is open to add this feature
 * in NextCloud API and this should land in NextCloud 17.
 *
 * When migrating to NextCloud 17, the new API should be used.
 *
 * @see https://github.com/nextcloud/server/pull/14800
 */

function registerLdapGroupPlugin()
{
    /** @var GroupPluginManager $groupManager */
    $groupManager = \OC::$server->query('LDAPGroupPluginManager');
    $groupManager->register(new LDAPGroupPlugin());
}

try {
    registerLdapGroupPlugin();
} catch (QueryException $e) {
    // If LDAPGroupPluginManager service is still not registered, wait for an event dispatched by LDAP plugin.
    OC::$server->getEventDispatcher()->addListener('OCA\\User_LDAP\\User\\User::postLDAPBackendAdded', function () {
        registerLdapGroupPlugin();
    });
}


\OC::$server->extend(IContactsStore::class, function ($defaultContactsStore, Server $c) {
    return new CustomContactsStore(
        $defaultContactsStore,
        $c->query(\OCP\IUserSession::class)
    );
});

\OC::$server->registerService(\OCP\Collaboration\Collaborators\ISearch::class, function (Server $c) {
    $search = new \OC\Collaboration\Collaborators\Search($c);

    $search->registerPlugin(['shareType' => 'SHARE_TYPE_USER', 'class' => CustomUserPlugin::class]);
    $search->registerPlugin(['shareType' => 'SHARE_TYPE_GROUP', 'class' => CustomGroupPlugin::class]);

    return $search;
});

\OC::$server->registerService(\OCA\ReciaCustom\Collaborators\ICustomGroupFilter::class, function (Server $c) {
    return $c->resolve(\OCA\ReciaCustom\Collaborators\CustomGroupFilter::class);
});

\OC::$server->registerService(\OCA\ReciaCustom\Collaborators\ICustomUserFilter::class, function (Server $c) {
    return $c->resolve(\OCA\ReciaCustom\Collaborators\CustomUserFilter::class);
});


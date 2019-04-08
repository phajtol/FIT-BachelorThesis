<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MyACL
 *
 * @author JOHNi
 */

namespace App\Model;

use Nette\Security\Permission;

class Acl extends Permission implements \App\Interfaces\IRoleTranslator {

    /** @var array */
    protected $rolesAvailable;

    /**
     * Acl constructor.
     */
    public function __construct() {

        $this->rolesAvailable = [
                "admin"                 =>  "Admin",
                "reader"                =>  "Publication reader",
                "submitter"             =>  "Publication submitter",
                "conference-user"       =>  "Conference user",
                "conference-moderator"  =>  "Conference moderator"
        ];

        // ROLES
        $this->addRole('guest');
        $this->addRole('reader', 'guest');
        $this->addRole('submitter', 'reader');

        $this->addRole('conference-user', 'guest');
        $this->addRole('conference-moderator', 'conference-user');

        $this->addRole('admin', ['submitter', 'conference-moderator']);


        // RESOURCES

        // conference-user resources
        $this->addResource('CU');
        $this->addResource('PU');

        // Admin presenter
        $this->addResource('Admin');
        $this->addResource('Admin:default');
        $this->addResource('Admin:showunconfirmed');
        $this->addResource('Admin:reference');
        $this->addResource('Admin:settings');

        // Attribute presenter
        $this->addResource('Attribute');
        $this->addResource('Attribute:default');
        $this->addResource('Attribute:showall');
        // Author presenter
        $this->addResource('Author');
        $this->addResource('Author:default');
        $this->addResource('Author:showall');
        $this->addResource('Author:detail');


        $this->addResource('AcmCategory');
        $this->addResource('AcmCategory:default');
        $this->addResource('AcmCategory:showall');

        $this->addResource('Conference');
        $this->addResource('Conference:showall');
        $this->addResource('Conference:show');

        $this->addResource('DocumentIndex');
        $this->addResource('DocumentIndex:showall');

        $this->addResource('ConferenceAdmin');
        $this->addResource('ConferenceAdmin:default');
        $this->addResource('ConferenceAdmin:showall');

        $this->addResource('ConferenceCategory');
        $this->addResource('ConferenceCategory:default');
        $this->addResource('ConferenceCategory:showall');

        $this->addResource('CuGroup');
        $this->addResource('CuGroup:default');
        $this->addResource('CuGroup:showall');

        // Group presenter
        $this->addResource('Group');
        $this->addResource('Group:default');
        $this->addResource('Group:showall');

        $this->addResource('PublicationCategory');
        $this->addResource('PublicationCategory:default');
        $this->addResource('PublicationCategory:showall');


        // Homepage presenter
        $this->addResource('Homepage');
        $this->addResource('Homepage:default');
        $this->addResource('Homepage:searchresults');
        $this->addResource('Homepage:about');
        // Journal presenter
        $this->addResource('Journal');
        $this->addResource('Journal:default');
        $this->addResource('Journal:showall');
        // Publication presenter
        $this->addResource('Publication');
        $this->addResource('Publication:default');
        $this->addResource('Publication:showall');
        $this->addResource('Publication:showpub');
        $this->addResource('Publication:addnew');
        $this->addResource('Publication:search');
        // Reference presenter
        $this->addResource('Reference:addlist');
        // Publisher presenter
        $this->addResource('Publisher');
        $this->addResource('Publisher:default');
        $this->addResource('Publisher:showall');
        // User presenter
        $this->addResource('User');
        $this->addResource('User:default');
        $this->addResource('User:showall');
        $this->addResource('User:show');

        $this->addResource('Publi');
        $this->addResource('Publi:showall');
        $this->addResource('Publi:default');

        // PRIVILEGES

        // READER reader
        $this->allow('reader', 'Homepage');
        $this->allow('reader', 'Homepage:default');
        $this->allow('reader', 'Homepage:searchresults');
        $this->allow('reader', 'Homepage:about');

        $this->allow('reader', 'User');
        $this->allow('reader', 'User:default');
        $this->allow('reader', 'User:show');
        $this->allow('conference-user', 'User:show');

        $this->allow('reader', 'Publication');
        $this->allow('reader', 'Publication:default');
        $this->allow('reader', 'Publication:showpub');
        $this->allow('reader', 'Publication:search');

        $this->allow('reader', 'Author:detail');
        $this->allow('reader', 'Author:showall');

        // SUBMITTER submitter
        $this->allow('submitter', 'Attribute');
        $this->allow('submitter', 'Attribute:default');
        $this->allow('submitter', 'Attribute:showall');

        $this->allow('submitter', 'Author');
        $this->allow('submitter', 'Author:default');

        $this->allow('submitter', 'PublicationCategory');
        $this->allow('submitter', 'PublicationCategory:default');
        $this->allow('submitter', 'PublicationCategory:showall');

        $this->allow('submitter', 'Group');
        $this->allow('submitter', 'Group:default');
        $this->allow('submitter', 'Group:showall');

        $this->allow('submitter', 'Journal');
        $this->allow('submitter', 'Journal:default');
        $this->allow('submitter', 'Journal:showall');

        $this->allow('submitter', 'Publication:showall');
        $this->allow('submitter', 'Publication:addnew');

        $this->allow('submitter', 'Reference:addlist');

        $this->allow('submitter', 'Publisher');
        $this->allow('submitter', 'Publisher:default');
        $this->allow('submitter', 'Publisher:showall');

        $this->allow('conference-moderator', 'ConferenceCategory');
        $this->allow('conference-moderator', 'ConferenceCategory:default');
        $this->allow('conference-moderator', 'ConferenceCategory:showall');

        $this->allow('conference-moderator', 'AcmCategory');
        $this->allow('conference-moderator', 'AcmCategory:default');
        $this->allow('conference-moderator', 'AcmCategory:showall');

        $this->allow('conference-moderator', 'CuGroup');
        $this->allow('conference-moderator', 'CuGroup:default');
        $this->allow('conference-moderator', 'CuGroup:showall');

        $this->allow('submitter', 'ConferenceAdmin');
        $this->allow('submitter', 'ConferenceAdmin:default');
        $this->allow('submitter', 'ConferenceAdmin:showall');
        $this->allow('conference-moderator', 'ConferenceAdmin');
        $this->allow('conference-moderator', 'ConferenceAdmin:default');
        $this->allow('conference-moderator', 'ConferenceAdmin:showall');

        $this->allow('conference-moderator', 'DocumentIndex');
        $this->allow('conference-moderator', 'DocumentIndex:showall');


        $this->allow('conference-user', 'Conference');
        $this->allow('conference-user', 'Conference:showall');
        $this->allow('conference-user', 'Conference:show');


        $this->allow('conference-user', 'CU');
        $this->allow('reader', 'PU');


        // ADMIN admin
        $this->allow('admin', 'Admin');
        $this->allow('admin', 'Admin:default');
        $this->allow('admin', 'Admin:showunconfirmed');
        $this->allow('admin', 'Admin:reference');
        $this->allow('admin', 'Admin:settings');

        $this->allow('admin', 'User:showall');

        // $this->allow('admin', Permission::ALL, Permission::ALL);
        // akci logs atp
    }

    /**
     * @param string $roleId
     * @return string
     */
    public function translateRole(string $roleId): string
    {
        if (isset($this->rolesAvailable[$roleId])) {
            return $this->rolesAvailable[$roleId];
        } else {
            return $roleId;
        }
    }

    /**
     * @return array
     */
    public function getAvailableRoles(): array
    {
        return $this->rolesAvailable;
    }


}

?>

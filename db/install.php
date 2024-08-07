<?php

defined('MOODLE_INTERNAL') || die();


/**
 * Function to upgrade auth_ldap.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_core_facades_install()
{
    global $CFG, $DB;       
    
    $dbman = $DB->get_manager();

    $table = new xmldb_table('user');

    $field = new xmldb_field('created_at', XMLDB_TYPE_INTEGER, 19, true, null, null, null);
    if(!$dbman->field_exists($table,$field)) {
        $dbman->add_field($table,$field);
    }
    $field = new xmldb_field('created_by', XMLDB_TYPE_INTEGER, 19, true, null, null, null);
    if(!$dbman->field_exists($table,$field)) {
        $dbman->add_field($table,$field);
    }
    $field = new xmldb_field('updated_at', XMLDB_TYPE_INTEGER, 19, true, null, null, null);
    if(!$dbman->field_exists($table,$field)) {
        $dbman->add_field($table,$field);
    }
    $field = new xmldb_field('updated_by', XMLDB_TYPE_INTEGER, 19, true, null, null, null);
    if(!$dbman->field_exists($table,$field)) {
        $dbman->add_field($table,$field);
    }
    $field = new xmldb_field('deleted_at', XMLDB_TYPE_INTEGER, 19, true, null, null, null);
    if(!$dbman->field_exists($table,$field)) {
        $dbman->add_field($table,$field);
    }
    $field = new xmldb_field('deleted_by', XMLDB_TYPE_INTEGER, 19, true, null, null, null);
    if(!$dbman->field_exists($table,$field)) {
        $dbman->add_field($table,$field);
    }

    return true;
}
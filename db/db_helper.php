<?php 

function getRole(string $shortname): ?\stdClass
{
    $sql = "SELECT * FROM mdl_role where shortname = :shortname ";
    GLOBAL $DB;
    $data = $DB->get_record_sql($sql,['shortname'=>$shortname]);
    if($data) {
        return $data;
    }
    return null;
}

function generateStamps($table)
{
    $table->add_field('deleted', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
    $table->add_field('created_at', XMLDB_TYPE_DATETIME, null, null, null, null, null);
    $table->add_field('created_by', XMLDB_TYPE_INTEGER, 19, XMLDB_UNSIGNED, null, null, null);
    $table->add_field('updated_at', XMLDB_TYPE_DATETIME, null, null, null, null, null);
    $table->add_field('updated_by', XMLDB_TYPE_INTEGER, 19, XMLDB_UNSIGNED, null, null, null);
    $table->add_field('deleted_at', XMLDB_TYPE_DATETIME, null, null, null, null, null);
    $table->add_field('deleted_by', XMLDB_TYPE_INTEGER, 19, XMLDB_UNSIGNED, null, null, null);
    return $table;
}
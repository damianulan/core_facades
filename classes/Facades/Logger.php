<?php

    namespace local_core_facades\Facades;

    use Exception;
    use local_core_facades\App\Model;
    use stdClass;
    class Logger 
    {
        const TABLENAME = 'core_facades_module_log';

        public static function insertTable(): bool
        {
            global $DB;
            $dbman = $DB->get_manager();

            $table = new \xmldb_table(self::TABLENAME);

            if (!$dbman->table_exists($table)) {
                $table->add_field('id', XMLDB_TYPE_INTEGER, 19, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
                $table->add_field('modulename', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);
                $table->add_field('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);
                $table->add_field('type', XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL, null, null);
                $table->add_field('causer_id', XMLDB_TYPE_INTEGER, 19, null, null, null, null);
                $table->add_field('target_id', XMLDB_TYPE_INTEGER, 19, null, null, null, null);
                $table->add_field('entity', XMLDB_TYPE_TEXT, null, null, null, null, null);
                $table->add_field('datas', XMLDB_TYPE_TEXT, null, null, null, null, null);

                $table->add_field('created_at', XMLDB_TYPE_DATETIME, null,null, null, null, null);
            
                $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
                $table->add_index('causer_id',XMLDB_INDEX_NOTUNIQUE,array('causer_id'));
                $table->add_index('target_id',XMLDB_INDEX_NOTUNIQUE,array('target_id'));

                $dbman->create_table($table);
            }
                        
            return true;
        }

        public static function create(string $name, $instance): bool
        {
            return self::log($name, __FUNCTION__, $instance, false);
        }

        public static function update(string $name, $instance): bool
        {
            return self::log($name, __FUNCTION__, $instance);
        }

        public static function delete(string $name, $instance): bool
        {
            return self::log($name, __FUNCTION__, $instance);
        }

        private static function log(string $name, string $type, $instance, bool $storeDatas = true): bool
        {
            global $DB;

            $modulename = request()->core_module;
            if(!empty($modulename) && $instance){
                self::insertTable();

                $personstamp = $instance->getPersonstamps()[$type];
                $log = new stdClass();
                $log->modulename = $modulename;
                $log->name = $name;
                $log->type = $type;
                $log->causer_id = $instance->$personstamp;
                $log->target_id = $instance->id;
                $log->entity = $instance::class;
                if($storeDatas){
                    $datas['original'] = $instance->getOriginal();
                    $datas['dirty'] = $instance->getVars();
                    $log->datas = json_encode($datas);
                }
                $log->created_at = date('Y-m-d H:i:s');

                $result = $DB->insert_record(self::TABLENAME, $log);
                if($result){
                    return true;
                }
            }

            return false;
        }
    }

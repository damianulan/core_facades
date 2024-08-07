<?php

    namespace local_core_facades\App\Traits;

    Trait Authenticable
    {

        public function suspend()
        {
            $this->suspended = 1;
            $this->update();
        }

        public function unsuspend()
        {
            $this->suspended = 0;
            $this->update();
        }

        public function confirm()
        {
            $this->confirmed = 1;
            $this->update();
        }

        public function reject()
        {
            $this->confirmed = 0;
            $this->update();
        }

        public static function allButMe(): array
        {
            global $DB, $USER;

            $model = static::getInstance();
            $collection = array();
            $where = null;
            if($model->softDeletes){
                $where = " AND u.deleted = 0";
            }

            $table = static::$table;
            $sql = "SELECT u.* FROM mdl_$table u WHERE u.id != :uid $where";

            $results = $DB->get_records_sql($sql, ['uid' => $USER->id]);
            
            if(!empty($results)){
                foreach ($results as $record){
                    $model = static::getInstance();
                    $instance = self::populateModel($record, $model);
                    if(!$instance->isEmpty()){
                        $collection[$instance->id] = $instance;
                    }
                }
            }

            return $collection;
        }

    }
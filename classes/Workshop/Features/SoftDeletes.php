<?php

    namespace local_core_facades\Workshop\Features;

    use Exception;

    trait SoftDeletes
    {
        public int $deleted;
        public $deleted_at;
        public $deleted_by;

        /**
         * Zwraca instancję modelu na podstawie klucza głównego. Przeszukuje także rekordy oznaczone jako usunięte.
         */
        public static function getDeleted(int $id): static
        {
            global $DB;

            $model = static::getInstance();
            $params = array();
            $params['id'] = $id;

            $record = $DB->get_record(static::$table, $params);
            
            if(!empty($record)){
                $instance = self::populateModel($record, $model);
                if(!$instance->isEmpty()){
                    return $instance;
                }
            }

            return null;
        }
        
        /**
         * Zwraca wszystkie instancje modelu, wraz z usuniętymi rekordami.
         */
        public static function allWithDeletes(): array
        {
            global $DB;

            $results = $DB->get_records(static::$table);
            return self::populateCollection($results);
        }

        /**
         * Przywraca usunięty wcześniej rekord.
         */
        public function restore()
        {
            if(!$this->isEmpty()){
                $this->deleted = 0;
                $timestamp = $this->timestamps['delete'];
                if(!is_null($timestamp)){
                    if($this->dateType === 'unix'){
                        $this->$timestamp = 0;
                    } elseif($this->dateType === 'datetime'){
                        $this->$timestamp = NULL;
                    }
                }
                $personstamp = $this->personstamps['delete'];
                if(!is_null($personstamp)){
                    $this->$personstamp = NULL;
                }
                return $this->update();
            }
            return false;
        }

        /**
         * Usuwa miękko instancję obiektu.
         */
        public function delete(): ?self
        {
            global $DB, $USER;

            if(static::$table && (int)$this->id > 0){
                $this->deleted = 1;
                $timestamp = $this->timestamps['delete'];
                if(!is_null($timestamp)){
                    $this->$timestamp = $this->getDateTime();
                }
                $personstamp = $this->personstamps['delete'];
                if(!is_null($personstamp)){
                    $this->$personstamp = $USER->id;
                }
                $obj = $this->cleanBeforeDB();
                $result = $DB->update_record(static::$table, $obj);
                if($result){
                    $this->fireEvent('deleted');
                    return $this;
                }
            } else {
                throw new Exception('$table static member is required but not set!');
            }
            return null;
        }

    }
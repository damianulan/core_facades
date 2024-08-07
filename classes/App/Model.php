<?php

    namespace local_core_facades\App;

    use Exception;
    use local_core_facades\Facades\Relationships\Siamese;
    use local_core_facades\Workshop\Features\Core\EventfulModel;
    use local_core_facades\Workshop\Features\SoftDeletes;
    use local_core_facades\Workshop\Features\Core\Castable;

    class Model
    {
        use Siamese, EventfulModel, Castable;

        protected static $_instance;
        protected $checksum;

        protected static $table;
        public int $id;
        protected string $dateType = 'datetime';

        protected array $timestamps = [
            'create' => 'created_at',
            'update' => 'updated_at',
            'delete' => 'deleted_at',
        ];

        protected array $personstamps = [
            'create' => 'created_by',
            'update' => 'updated_by',
            'delete' => 'deleted_by',
        ];

        protected bool $validated = false;
        protected bool $verified = false;
        private bool $booted = false;
        private bool $is_dirty = false;
        protected $original;
        protected $attributes = [];
        protected $fillable = [];
        protected $defaults = [];
        protected $exclude = [];

        protected static $traitsBooted = [];

        public function __construct(array $attributes = [])
        {
            $this->bootIfNotBooted();

            $this->fillWith($attributes, true);
        }

        final protected function bootIfNotBooted()
        {
            if(!$this->booted){
                
                static::booting();

                $this->setAttributes();
                static::boot();
                
                static::booted();

                $this->booted = true;
            }
        }

        protected static function booting()
        {
            //
        }

        protected static function boot()
        {
            static::bootTraits();
        }

        protected static function booted()
        {
            //
        }

        public function isBooted(): bool
        {
            return $this->booted;
        }

        protected static function bootTraits()
        {
            $class = static::class;
    
            $booted = [];
            static::$traitsBooted = class_uses_recursive($class);
        
            foreach (static::$traitsBooted as $trait) {
                $method = 'boot'.class_basename($trait);
    
                if (method_exists($class, $method) && ! in_array($method, $booted)) {
                    forward_static_call([$class, $method]);
    
                    $booted[] = $method;
                }
            }
        }

        public static function getInstance(): static
        {
            if(!isset(static::$_instance)){
                static::$_instance = new static();
            }
            return static::$_instance;
        }

        public static function getAlias(): string
        {
            $partials = explode('_',snake_case(static::class));
            $alias = null;
            foreach($partials as $val){
                if($val === 'local' || $val === 'models'){
                    continue;
                }
                $alias .= $val . '_';
            }
            return trim($alias, '_');
        }

        public static function getTableName(): string
        {
            return static::$table;
        }

        public static function query(array $params = [], bool $with_relations = false): string
        {
            global $CFG;
            $prefix = $CFG->prefix;
            $alias = static::getAlias();
            $table = $prefix . static::getTableName();
            $id = $params['id'] ?? null;
            $where = "0=0";

            if($id){
                $where = "$alias.id = $id";                
            }

            $joins = null;
            if(static::usesTrait(SoftDeletes::class)){
                $where .= " AND $alias.deleted = 0";
            }

            if($with_relations){
                $joins = static::getJoins();
            }

            $query = "SELECT $alias.* 
                    FROM $table $alias 
                    $joins 
                    WHERE $where 
                    ";


            $group_by = $params['group_by'] ?? null;
            $sort = $params['sort'] ?? null;
            $dir = $params['dir'] ?? null;
            $pagination = $params['pagination'] ?? null;
            $page = $params['page'] ?? null;

            // Groupping
            if($group_by){
                $group_by = " GROUP BY $group_by ";
            }
            
            // Ordering
            $orderby = '';
            if($sort && $dir){
                $orderby = " ORDER BY ".$sort .' ' .$dir;
            }

            // SQL limit
            $limit = ''; 
            if($page && $pagination){
                $start = ($page - 1) * $pagination;
                $rows = $pagination;
                $limit = " LIMIT $start, $rows";
            }

            $q = $query .PHP_EOL.$group_by.PHP_EOL.$orderby.PHP_EOL.$limit;

            return $q;
        }

        public function getQuery()
        {
            return static::query([
                'id' => $this->id,
            ]);
        }

        public static function usesTrait($trait)
        {
            return isset(static::$traitsBooted[$trait]);
        }

        /**
         * Zwraca wszystkie instancje modelu.
         */
        public static function all(): array
        {
            global $DB;

            $params = array();
            if(static::usesTrait(SoftDeletes::class)){
                $params['deleted'] = 0;
            }

            $results = $DB->get_records(static::$table, $params);            
            return self::populateCollection($results);
        }


        /**
         * Zwraca instancję modelu na podstawie klucza głównego.
         */
        public static function get(int $id): ?static
        {
            global $DB;

            $model = static::getInstance();
            $params = array();
            if(static::usesTrait(SoftDeletes::class)){
                $params['deleted'] = 0;
            }
            $params['id'] = $id;

            $record = $DB->get_record(static::$table, $params);
            
            if(!empty($record)){
                $instance = self::populateModel($record, $model);
                if(!$instance->isEmpty()){
                    $instance->fireEvent('retrieved');
                    return $instance;
                }
            }

            return null;
        }

        /**
         * Zwraca kolekcję instancji modeli na podstawie parametrów. Przekaż parametr 'deleted' => 1, aby przeszukać usunięte wartości.
         */
        public static function where(array $params): array
        {
            global $DB;

            $model = static::getInstance();

            if(static::usesTrait(SoftDeletes::class) && !array_key_exists('deleted', $params)){
                $params['deleted'] = 0;
            }

            $results = $DB->get_records(static::$table, $params);
            return self::populateCollection($results);
        }

        // TODO
        public static function whereIn(string $column, array $values, array $params = []): array
        {
            global $DB;

            $model = static::getInstance();
            if(empty($values)){
                return array();
            }

            [$insql, $params_sql] = $DB->get_in_or_equal($values, SQL_PARAMS_NAMED);
            $where = null;
            if(static::usesTrait(SoftDeletes::class) && !array_key_exists('deleted', $params)){
                $params['deleted'] = 0;
                $where = " AND d.deleted = :deleted ";
            }

            $t = static::$table;
            $sql = "SELECT d.* 
                    FROM mdl_$t d 
                    WHERE d.$column {$insql} $where
                    ";

            $results = $DB->get_records_sql($sql, array_merge($params, $params_sql));

            return self::populateCollection($results);
        }

        /**
         * Zwraca kolekcję instancji modeli na podstawie polecenia SQL oraz jego przekazywanych parametrów.
         */
        public static function queryRaw(string $sql, array $params): array
        {
            global $DB;
            $results = $DB->get_records_sql($sql, $params);

            return self::populateCollection($results);
        }

        final public static function populateCollection(array $records): array
        {
            $collection = array();
            
            if(!empty($records)){
                foreach ($records as $record){
                    $model = static::getInstance();
                    $instance = self::populateModel($record, $model);
                    if(!$instance->isEmpty()){
                        $instance->fireEvent('retrieved');
                        $collection[$instance->id] = $instance;
                    }
                }
            }

            return $collection;
        }

        /**
         * Wypełnia własności obiektu danymi z bazy. Jeśli stosowne wartości nie zostały zainicjowane w obiekcie, metoda sama je utworzy.
         */
        final protected static function populateModel($record, $instance, $with_defaults = false, $all = false): static
        {
            if(!empty($record) && $instance instanceof Model){

                $r = (array) $record;
                if($instance->isBooted()){
                    $r = array_filter($r, fn($key) => in_array($key, $instance->getAttributes()), ARRAY_FILTER_USE_KEY);
                } else {
                    $rc = new \ReflectionClass(static::class);
                    $rc_props = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);
                    $rc_props = array_filter($rc_props, fn($p) => !$p->isStatic());
                    $props = array_map(fn($attribute) => $attribute->getName(), $rc_props);
                    
                    $r = array_filter($r, fn($key) => in_array($key, $props), ARRAY_FILTER_USE_KEY);
                }

                foreach($r as $property => $value){
                    $skip = false;
                    if(!empty($instance->exclude) && !$all){
                        if(array_key_exists($property, $instance->exclude) && $instance->exclude[$property] == $value){
                            $skip = true;
                        }
                    }
                    if($property === 'id' && empty($value)){
                        $skip = true;
                    }

                    if(!$skip){
                        $instance->assignValue($property, $value);
                    }
                }

                if($with_defaults && !empty($instance->defaults)){
                    foreach($instance->defaults as $property => $value){
                        if(!isset($instance->$property)){
                            $instance->$property = $value;
                        }
                    }
                }

                $instance->verified = true;
                $instance = static::setCleanState($instance);
            }
            return $instance;
        }

        public function fillWith(array $collection, bool $with_defaults = false): static
        {
            return self::populateModel($collection, $this, $with_defaults);
        }

        private static function setCleanState($instance): static
        {
            $vars = $instance->getVars();
            $instance->original = $vars;
            $instance->checksum = md5(serialize($vars));
            return $instance;
        }

        public function getVars(): array
        {
            $r = (array) $this;
            return array_filter($r, fn($key) => in_array($key, $this->attributes), ARRAY_FILTER_USE_KEY);
        }

        private function setAttributes()
        {
            if(empty($this->attributes) || empty($this->fillable)){
                $rc = new \ReflectionClass(static::class);
                $rc_props = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);
                $props = array_filter($rc_props, fn($p) => !$p->isStatic());
                if(empty($this->attributes)){
                    $this->attributes = array_map(fn($attribute) => $attribute->getName(), $props);
                }
                if(empty($this->fillable)){
                    $this->fillable = array_map(fn($attribute) => $attribute->getName(), $props);
                }
            }
        }

        final public function getAttributes(): array
        {
            return $this->attributes;
        }

        /**
         * Tworzy nową instancję obiektu. Można stosować zamiennie z $this->update().
         */
        public function save(): ?self
        {
            global $DB, $USER;
            if(static::$table){
                if(isset($this->id) && (int)$this->id > 0){
                    return $this->update();
                } else {
                    $timestamp = $this->timestamps['create'];
                    if(!is_null($timestamp)){
                        $this->$timestamp = $this->getDateTime();
                    }
                    $personstamp = $this->personstamps['create'];
                    if(!is_null($personstamp)){
                        $this->$personstamp = $USER->id;
                    }

                    $obj = $this->cleanBeforeDB();
                    $result = $DB->insert_record(static::$table, $obj);
                    if($result){
                        $this->id = $result;
                        $this->fireEvent('created');
                        return $this;
                    }
                }
            } else {
                throw new Exception('$table static member is required but not set!');
            }
            return null;
        }

        /**
         * Aktualizuje instancję obiektu. Można stosować zamiennie z $this->save().
         */
        public function update(): ?self
        {
            global $DB, $USER;
            if(static::$table){
                if((int)$this->id > 0){
                    if(!$this->isDirty()){
                        return $this->id;
                    }
                    $timestamp = $this->timestamps['update'];
                    if(!is_null($timestamp)){
                        $this->$timestamp = $this->getDateTime();
                    }
                    $personstamp = $this->personstamps['update'];
                    if(!is_null($personstamp)){
                        $this->$personstamp = $USER->id;
                    }

                    $obj = $this->cleanBeforeDB();
                    $result = $DB->update_record(static::$table, $obj);
                    if($result){
                        $this->fireEvent('updated');
                        return $this;
                    }
                } else {
                    return $this->save();
                }
            } else {
                throw new Exception('$table static member is required but not set!');
            }
            return null;
        }

        /**
         * Usuwa instancję obiektu.
         */
        public function delete(): ?self
        {
            global $DB;

            if(static::$table && (int)$this->id > 0){
                $result = $DB->delete_records(static::$table, ['id' => $this->id]);
                if($result){
                    $this->fireEvent('deleted');
                    return $this;
                }
            } else {
                throw new Exception('$table static member is required but not set!');
            }
            return null;
        }

        protected function cleanBeforeDB(): object
        {
            return (object)array_filter((array)$this, fn($key) => in_array($key, $this->fillable), ARRAY_FILTER_USE_KEY);
        }

        /**
         * Usuwa obiekt oraz pozostałe powiązane z nim relacjami, na podstawie listy metod w $this->relationships. Jeśli jest ona pusta, po prostu usuwa deklarowaną pojedynczą instancję.
         */
        public function cascadeDelete()
        {
            if(!$this->isEmpty()){
                if($this->delete()){
                    if(count($this->relationships)){
                        foreach($this->relationships as $method){
                            $results = $this->$method();
                            if(is_array($results) && !empty($results)){
                                foreach($results as $record){
                                    $record->cascadeDelete();
                                }
                            }
                        }
                    }
                    return true;
                }
            }
            return false;
        }

        /**
         * Aktualizuje instancję wg danych pobranych z bazy, na podstawie identyfikatora (klucza głównego).
         */
        public function refresh(): static
        {
            return static::get($this->id);
        }

        /**
         * Jeśli jest to możliwe wymusza reinstatację obiektu. Przydatne jeśli w pętli zapisujemy wiele podobnych do siebie instancji tego samego obiektu.
         */
        public function replicate(): static
        {
            if(!$this->isEmpty()){
                unset($this->id);
            }
            return $this;
        }

        /**
         * Zwraca aktualną datę i czas zgodnie z typem przypisanym do modelu.
         */
        public function getDateTime(): ?string
        {
            $result = null;
            if($this->dateType === 'unix'){
                $result = time();
            } elseif($this->dateType === 'datetime'){
                $result = date('Y-m-d H:i:s', time());
            }
            return $result;
        }

        /**
         * Sprawdź czy instancja modelu jest pustym obiektem.
         */
        public function isEmpty(): bool
        {
            return isset($this->id) && $this->id > 0 ? false:true;
        }

        /**
         * Sprawdź czy instancja modelu nie jest pustym obiektem.
         */
        public function isFilled(): bool
        {
            return isset($this->id) && $this->id > 0 ? true:false;
        }

        /**
         * Sprawdź czy instancja modelu ma swoje odzwierciedlenie w bazie danych.
         */
        public function exists(): bool
        {
            global $DB;
            if(!$this->isEmpty()){
                $params['id'] = $this->id;
                if(static::usesTrait(SoftDeletes::class)){
                    $params['deleted'] = 0;
                }
                $record = $DB->get_record(static::$table, $params);
                if($record){
                    $this->verified = true;
                    return true;
                }
            }

            $this->verified = false;
            return false;
        }

        /**
         * Sprawdza czy ta instancja została już zwalidowana.
         */
        public function validated(): bool 
        {
            return $this->validated;
        }

        /**
         * Sprawdza czy na tej instancji naniesiono zmiany we własnościach.
         */
        public function isDirty(): bool
        {
            if ( $this->is_dirty === true )
            {
                return true;
            }
      
            $previous = $this->checksum;
      
            $temp = get_object_vars($this);
            unset( $temp['checksum'] );
            $this->checksum = md5( serialize( $temp ) );
            $this->is_dirty = $previous !== $this->checksum;

            return $this->is_dirty;
        }

        public function renderDate(string $property, string $format = 'Y-m-d'): ?string
        {
            if(isset($this->$property) && !empty($this->$property)){
                if(isValidTimestamp($this->$property)){
                    return date($format, (int)$this->$property);
                }
            }
            return null;
        }

        /**
         * Do formatu daty dodaje godzinę początku, lub końca.
         */
        public static function dateAddSuffix(string $date, string $type = 'start'): string
        {
            $suffix = '00:00:00';
            if($type === 'end'){
                $suffix = '23:59:59';
            }
            return $date . ' ' . $suffix;
        }

        public function getOriginal(): array
        {
            if($this->original){
                return $this->original;
            }
            return null;
        }

        public function getPersonstamps(): array
        {
            return $this->personstamps;
        }

        public function clone()
        {

        }

        public function dump()
        {

        }
    }
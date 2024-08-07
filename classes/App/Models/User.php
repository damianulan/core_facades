<?php

    namespace local_core_facades\App\Models;

    use local_core_facades\App\Model;
    use local_core_facades\App\Traits\Authenticable;
    use local_core_facades\App\Traits\RolesAndPermissions;

    class User extends Model
    {
        use Authenticable, RolesAndPermissions;
        // now use traits to specific modules. Modify if needed.
        use \local_core_blended\Traits\BlendedUser;

        protected static $table = 'user';

        protected $exclude = [
            'id' => 1,
        ];
        protected array $timestamps = [
            'create' => 'timecreated',
            'update' => 'timemodified',
            'delete' => 'deleted_at',
        ];

        protected array $personstamps = [
            'create' => 'created_by',
            'update' => 'updated_by',
            'delete' => 'deleted_by',
        ];
        protected string $dateType = 'unix';

        public $firstname;
        public $lastname;
        public $username;
        public $email;
        public $deleted;

        public function name(): string
        {
            return $this->firstname . ' ' . $this->lastname;
        }
        
        public function setAttributes(){

        }
    }
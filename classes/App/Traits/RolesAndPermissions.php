<?php

    namespace local_core_facades\App\Traits;

    use local_core_facades\App\UserRoles;
    Trait RolesAndPermissions
    {

        public function roles()
        {

        }

        public function permissions()
        {

        }

        public function hasRole (...$slug): bool
        {
            foreach($slug as $role){
                if(!UserRoles::hasRoleAssignment($this->id, $role)){
                    return false;
                }
            }
            
            return true;
        }

        public function hasPermission (...$slug): bool
        {
            foreach($slug as $permission){
                if(!has_capability($permission, \context_system::instance(), $this->id)){
                    return false;
                    break;
                }
            }

            return true;
        }
    }
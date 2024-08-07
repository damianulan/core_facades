<?php

namespace local_core_facades\App;

use local_core_facades\App\Models\User;

class UserRoles
{
    public static function hasRole(string $role, int $user_id = null): bool
    {
        GLOBAL $USER;

        if($user_id) {
            $uid = $user_id;
        } else {
            $uid = $USER->id;
        }

        $access = is_siteadmin();
        if(!$access && self::hasRoleAssignment($uid, $role)) {
            $access = true;
        }
        return $access;
    }

    public static function isSuperAdmin(int $user_id = null): bool
    {
        GLOBAL $USER;

        if($user_id) {
            $uid = $user_id;
        } else {
            $uid = $USER->id;
        }

        $admin = false;
        $role_id = self::getRoleId('superadmin');
        if(is_siteadmin() || ( $role_id && user_has_role_assignment($uid, $role_id))) {
            $admin = true;
        }
        return $admin;
    }

    public static function isSuperior(int $user_id = null): bool
    {
        GLOBAL $USER;

        $superior_role_id = self::getRoleId('superior');

        if($user_id) {
            $uid = $user_id;
        } else {
            $uid = $USER->id;
        }

        if($superior_role_id && user_has_role_assignment($uid, $superior_role_id, 1)) {
            return true;
        }

        return false;
    }

    public static function isAdminBlended(int $user_id = null): bool
    {
        GLOBAL $USER;

        if($user_id) {
            $uid = $user_id;
        } else {
            $uid = $USER->id;
        }

        $admin = false;
        if(self::hasRoleAssignment($uid, 'admin_blended')) {
            $admin = true;
        }
        return $admin;
    }

    public static function isAdminBlendedMaterials(int $user_id = null): bool
    {
        GLOBAL $USER;

        if($user_id) {
            $uid = $user_id;
        } else {
            $uid = $USER->id;
        }

        $role_id = self::getRoleId('admin_blended_materials');
        if($role_id && user_has_role_assignment($USER->id, $role_id)) {
            return true;
        }
        return false;
    }

    public static function isAdminBlendedMaterialsForCourse($courseid): bool
    {
        GLOBAL $USER;
        $role_id = self::getRoleId('admin_blended_materials');
        $contextid = \context_course::instance($courseid)->id;
        if($role_id && user_has_role_assignment($USER->id, $role_id, $contextid)) {
            return true;
        }
        return false;
    }

    public static function isAdminElearning(int $user_id = null): bool
    {
        GLOBAL $USER;

        if($user_id) {
            $uid = $user_id;
        } else {
            $uid = $USER->id;
        }

        $admin = false;
        if(self::hasRoleAssignment($uid, 'admin_elearning')) {
            $admin = true;
        }
        return $admin;
    }

    public static function getRoleId(string $shortname): ?int
    {
        GLOBAL $DB;
        $data = $DB->get_record_select('role', 'shortname=:shortname', ['shortname'=>$shortname]);
        if($data) {
            return $data->id;
        }
        return null;
    }

    public static function getUser(int $id)
    {
        global $DB;
        return $DB->get_record('user', ['id' => $id]);
    }

    public static function assignRole(string $shortrole, int $user_id): bool
    {
        $role_id = self::getRoleId($shortrole);
        $user = self::getUser($user_id);
        if($role_id && $user && $user->deleted == 0 && !user_has_role_assignment($user_id, $role_id)) {
            role_assign($role_id, $user_id, 1);
        }
        return true;
    }

    public static function unassignRole(string $shortrole, int $user_id): bool
    {
        $role_id = self::getRoleId($shortrole);
        $user = self::getUser($user_id);
        if($role_id && $user && $user->deleted == 0) {
            role_unassign($role_id, $user_id, 1);
        }
        return true;
    }

    public static function unassignRoleIfNeeded(string $shortrole, int $user_id): bool
    {
        $unassign = false;
        switch ($shortrole) {
            case 'admin_blended':
                if(empty(self::getCategoriesForAdminBlended())){
                    $unassign = true;
                }
                break;
            case 'admin_obszarowy':
                if(empty(self::getCategoriesForAdminObszarowy())){
                    $unassign = true;
                }
                break;
            default:
                break;
        }

        if($unassign){
            return self::unassignRole($shortrole, $user_id);
        }
        return false;
    }

    public static function hasRoleAssignment(int $user_id, string $shortname): bool
    {
        $role_id = self::getRoleId($shortname);
        if(user_has_role_assignment($user_id, $role_id)){
            switch ($shortname) {
                case 'admin_blended':
                    if(empty(self::getCategoriesForAdminBlended())){
                        return false;
                    }
                    break;
                case 'admin_obszarowy':
                    if(empty(self::getCategoriesForAdminObszarowy())){
                        return false;
                    }
                    break;
                default:
                    break;
            }
            return true;
        }
        return false;
    }

    public static function getCategoriesForAdminBlended(): array
    {
        GLOBAL $DB, $USER;

        $array = [];
        $sql = "SELECT DISTINCT(c.category_id) AS id
                FROM mdl_core_trainings_admin c
                WHERE c.deleted = 0 AND c.user_id = :uid
        ";
        $params['uid'] = $USER->id;
        $categories = $DB->get_records_sql($sql,$params);
        if(count($categories)) {
            foreach($categories as $cat) {
                $array[] = $cat->id;
            }
        }

        return $array;
    }

    public static function getCategoriesForAdminObszarowy(): array
    {
        GLOBAL $DB, $USER;

        $array = [];
        $sql = "SELECT DISTINCT(c.category_id) AS id
                FROM mdl_core_cat_admins c
                WHERE c.deleted = 0 AND c.user_id = :uid
        ";
        $params['uid'] = $USER->id;
        $categories = $DB->get_records_sql($sql,$params);
        if(count($categories)) {
            foreach($categories as $cat) {
                $array[] = $cat->id;
            }
        }

        return $array;
    }

    public static function getCoursesForAdminObszarowy(): array
    {
        GLOBAL $DB;

        $array = [];

        $categories = self::getCategoriesForAdminObszarowy();
        if(count($categories)) {
            list($in_sql,$in_params) = $DB->get_in_or_equal($categories,SQL_PARAMS_NAMED,'cat');
            $sql = "SELECT c.id FROM mdl_course c
                    WHERE c.id > 1
                        AND c.category $in_sql
                    ";
            $courses = $DB->get_records_sql($sql,$in_params);
            if(count($courses)) {
                foreach($courses as $course) {
                    $array[] = $course->id;
                }
            }
        }

        return $array;
    }   

    public static function getCoursesForAdminBlended(): array
    {
        GLOBAL $DB;

        $array = [];

        $trainings = self::getTrainingsForBlendedAdmin();
        if(count($trainings)) {
            foreach ($trainings as $training){
                $sql = "SELECT c.id FROM mdl_course c
                WHERE c.shortname = 'blended_".$training."'";
                $course = $DB->get_record_sql($sql);
                $array[] = $course->id;
            }
        }
        return $array;
    } 

    public static function getTrainingsForBlendedAdmin(): array
    {
        GLOBAL $DB;

        $array = [];

        $categories = self::getCategoriesForAdminBlended();
        if(count($categories)) {
            list($in_sql,$in_params) = $DB->get_in_or_equal($categories,SQL_PARAMS_NAMED,'cat');
            $sql = "SELECT t.id FROM mdl_core_trainings t
                    WHERE t.category_course $in_sql
                    ";
            $trainings = $DB->get_records_sql($sql,$in_params);
            if(count($trainings)) {
                foreach($trainings as $training) {
                    $array[] = $training->id;
                }
            }
        }
        return $array;
    }

}



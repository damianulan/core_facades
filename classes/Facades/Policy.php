<?php

    namespace local_core_facades\Facades;

    use local_core_facades\App\Models\User;
    abstract class Policy 
    {

        public $_instance;
        public int $id;
        public User $user;

        abstract public function view(): bool;
        abstract public function create(): bool;
        abstract public function update(): bool;
        abstract public function delete(): bool;
    }
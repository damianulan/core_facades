<?php

    namespace local_core_facades\Facades\Relationships;

    use local_core_facades\Workshop\Features\SoftDeletes;

    class HasMany extends Relationlibs
    {

        public function getJoin(): string
        {
            $foreign_table = $this->prefix . $this->foreign_class::getTableName();
            $foreign_alias = $this->foreign_class::getAlias();

            $local_alias = $this->class::getAlias();
            //var_dump($local_alias, $foreign_alias);die;

            $foreign_key = $this->foreign_key;
            $local_key = $this->local_key;
            $where = null;
            if($this->foreign_class::usesTrait(SoftDeletes::class)){
                $where = " AND $foreign_alias.deleted = 0 ";
            }

            $join = " LEFT JOIN $foreign_table $foreign_alias ON $foreign_alias.$foreign_key = $local_alias.$local_key $where";

            return $join;
        }
    }
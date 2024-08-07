<?php

    namespace local_core_facades\Facades\Relationships;

    class BelongsTo extends Relationlibs
    {

        // TODO
        public function getJoin(): string
        {
            $foreign_table = $this->prefix . $this->foreign_class::getTableName();
            $foreign_alias = $this->foreign_class::getAlias();

            $local_alias = $this->class::getAlias();

            $foreign_key = $this->foreign_key;
            $local_key = $this->local_key;
            $where = null;
            if($this->foreign_class::usesTrait(SoftDeletes::class)){
                $where = " AND $foreign_alias.deleted = 0 ";
            }

            $join = " JOIN $foreign_table $foreign_alias ON $foreign_alias.$local_key = $local_alias.$foreign_key $where";

            return $join;
        }
    }
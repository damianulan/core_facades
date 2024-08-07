<?php

    namespace local_core_facades\Facades\Filters\Components;

    use Exception;
    use local_core_facades\Facades\Filters\FilterType;

    abstract class FilterSelect extends Component
    {
        public $type = FilterType::SELECT;
        public $multiple = false;
        public $chosen = true;
        public array $selected = [];

        /**
         * 
         */
        abstract public function contentQuery(): string;

        public function options(): array
        {
            global $DB;

            return $DB->get_records_sql($this->contentQuery());
        }

    }
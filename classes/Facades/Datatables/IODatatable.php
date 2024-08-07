<?php

    namespace local_core_facades\Facades\Datatables;

    interface IODatatable 
    {
        /**
         * Zwraca polecenie SQL w formacie string.
         * Jako aliasów używaj pełnych nazw tabel pozbawionych prefiksów.
         * Przykł. mdl_core_blended_categories => categories.
         */
        public function query(): string;
        public function params(): ?array;
        public function headers(): array;
    }

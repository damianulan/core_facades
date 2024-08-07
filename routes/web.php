<?php

use local_core_facades\Http\Routing\Route;

return [
    Route::get('datatable_ajax', 'local_core_facades\Facades\Datatables\Datatable@renderAjax'),
    Route::get('process_filters', 'local_core_facades\Facades\Filters\FilterEngine@process'),
];
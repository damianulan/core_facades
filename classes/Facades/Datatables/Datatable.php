<?php

    namespace local_core_facades\Facades\Datatables;

    use Exception;
    use local_core_facades\Facades\Datatables\IODatatable;
    use local_core_facades\Support\View;
    use local_core_facades\Http\Routing\Request;
    use local_core_facades\Facades\Filters\FilterBuilder;

    class Datatable implements IODatatable
    {
        protected $model;
        private $uuid;
        private $query;
        private $params = [];
        private $headers;
        private $records;
        private $count;
        private $page;
        private $pagination;
        private $pages_to_show;
        private $sort = null;
        private $dir = 'ASC';
        private $collation = 'utf8mb4_general_ci';
        private $group_by = null;
        private $having = null;

        private $defaultPagination = 20;
        private $defaultPagesToShow = 4;
        private $paginationSelect = [
            20,50,100,200
        ];
        protected $populate = false; 
        protected $paginate = true;

        private $filters;
        private ?FilterBuilder $filterBuilder = null;

        protected function setPagination(int $pagination)
        {
            $this->defaultPagination = $pagination;
            return $this;
        }

        protected function setPagesToShow(int $pages_to_show)
        {
            $this->defaultPagesToShow = $pages_to_show;
            return $this;
        }

        protected function setSortingKey(string $sorting_key, string $dir = 'ASC')
        {
            $this->sort = $sorting_key;
            $dir = strtoupper($dir);
            if($dir === 'ASC' || $dir === 'DESC'){
                $this->dir = $dir;
            }
            return $this;
        }

        protected function setCollation(string $collation)
        {
            $this->collation = $collation;
            return $this;
        }

        protected function setPaginationSelect(array $paginationSelect): self
        {
            $this->paginationSelect = $paginationSelect;
            return $this;
        }

        public function boot(Request $request)
        {
            $this->filters = $this->filters();
            $this->headers = $this->headers();
            $parameters = $request->parameters;

            $this->uuid = isset($parameters['datatable_uuid']) ? $parameters['datatable_uuid']:uuid();
            $this->page = isset($parameters['page']) ? (int)$parameters['page']:1;
            $this->pagination = isset($parameters['pagination']) ? (int)$parameters['pagination']:$this->defaultPagination;
            $this->pages_to_show = $this->defaultPagesToShow;

            if(!empty($this->filters)){
                $builder = $this->makeFilters();
                $func = 'datatableLoad("'.$this->uuid.'");';
                $builder->onFilter($func);
                $this->filterBuilder = $builder;
            }

            $this->processQuery();
        }

        protected function processQuery()
        {
            global $DB;

            $this->query = $this->query();
            $this->params = $this->params();
            $this->group_by = $this->groupBy();
            $this->having = $this->having();

            if(empty($this->headers) || empty($this->query)){
                throw new Exception("Nie udało się wykonać podmodułu Datatables. Przekazane polecenie SQL, lub kolumny są puste!");
                die;
            }
            
            // Filters
            $filter_params = array();
            $filter_where = null;
            $filter_having = null;
            if($this->filterBuilder){
                $this->filterBuilder->loadConditions();
                $filter_where = $this->filterBuilder->getWhere();
                $filter_having = $this->filterBuilder->getHaving();
                $filter_params = $this->filterBuilder->getParams();
            }

            // Where
            $this->query .= $filter_where;

            // Groupping
            $group_by = '';
            if(!empty($this->group_by)){
                $group_by = " GROUP BY $this->group_by ";
            }

            // Having
            $having = '';
            if(!empty($this->having)){
                $having = " HAVING 0=0 $this->having $filter_having";
            }

            // Ordering
            $orderby = '';
            if(!empty($this->sort) && !empty($this->dir)){
                $orderby = " ORDER BY ".$this->sort . ' COLLATE '.$this->collation. ' '. $this->dir;
            }

            // SQL limit
            $limit = ''; 
            if($this->paginate){
                $start = ($this->page - 1) * $this->pagination;
                $rows = $this->pagination;
                $limit = " LIMIT $start, $rows";
            }

            $q = $this->query .PHP_EOL.$group_by.PHP_EOL.$having.PHP_EOL.$orderby.PHP_EOL.$limit;

            $params = array_merge($this->params, $filter_params);

            $this->records = $DB->get_records_sql($q, $params);
            if($this->populate){
                $model = $this->model;
                $this->records = $model::populateCollection($this->records);
            }

            if(count($this->records)){
                $this->count = count($DB->get_records_sql($this->query, $params));
            } else {
                $this->count = 0;
            }
        }

        public function render()
        {
            global $SESSION;
            $this->boot(request());
            $SESSION->core_datatables[$this->uuid] = $this;
            $view = $this->getView($this);
            if($view){
                echo $view;
            }
        }

        public function renderAjax(Request $request)
        {
            global $SESSION;
            $status = 'error';
            $message = 'error';
            $view = null;

            if(isset($request->parameters['datatable_uuid'])){
                $uuid = $request->parameters['datatable_uuid'];
                if(isset($SESSION->core_datatables[$uuid])){
                    $instance = $SESSION->core_datatables[$uuid];
                    $instance->page = isset($request->parameters['page']) ? (int)$request->parameters['page']:1;
                    $instance->pagination = isset($request->parameters['pagination']) ? (int)$request->parameters['pagination']:$this->defaultPagination;
                    $instance->processQuery();
                    $view = $this->getView($instance, true);
                    if($view){
                        $status = 'ok';
                        $message = 'ok';
                    }
                }

            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                'view' => $view,
            ]);
        }

        public function scripts()
        {
            echo View::load('facades.datatables.scripts', [
                'filterBuilder' => $this->filterBuilder,
                'defaultPagination' => $this->defaultPagination,
            ], 'core_facades');
        }

        public function getPagination(): array
        {
            $pagination = $this->paginationSelect;
            if(!in_array($this->defaultPagination, $pagination)){
                $pagination[] = $this->defaultPagination;
            }

            return $pagination;
        }

        private function getView(Datatable $instance, bool $ajax = false)
        {
            $output = [
                'uuid' => $instance->uuid,
                'headers' => $instance->headers,
                'count' => $instance->count,
                'records' => $instance->records,
                'current_page' => $instance->page,
                'pagination' => $instance->pagination,
                'pages_to_show' => $instance->pages_to_show,
                'min_pagination' => min($instance->paginationSelect),
                'datatable' => $instance,
            ];

            if(!$ajax){
                $output['filterBuilder'] = $instance->filterBuilder;
            }

            return View::load('facades.datatables.table', $output, 'core_facades');
        }

        public function query(): string
        {
            return '';
        }

        public function headers(): array
        {
            return array();
        }

        public function filters(): array
        {
            return array();
        }

        public function params(): array
        {
            return array();
        }

        public function groupBy(): ?string
        {
            return null;
        }

        public function having(): ?string
        {
            return null;
        }

        private function makeFilters(): FilterBuilder
        {
            return FilterBuilder::boot($this->filters, $this->uuid);
        }
    }

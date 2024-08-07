<?php

    namespace local_core_facades\App;

    use Exception;
    use local_core_facades\Workshop\Features\Doctrine;
    use local_core_facades\Workshop\Features\Stalker;
    use local_core_facades\Workshop\Features\SoftDeletes;

    class AggregatedModel extends Model
    {

        use SoftDeletes, Stalker, Doctrine;
    }
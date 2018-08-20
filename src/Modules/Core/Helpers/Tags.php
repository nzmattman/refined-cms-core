<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Response;

class Tags {

    public function getTypes()
    {
        $types = \DB::table('tags')
                    ->select('type')
                    ->groupBy('type')
                    ->orderBy('type')
                    ->get();

        $data = [0 => 'Please Select'];
        if ($types && $types->count()) {
            foreach ($types as $type) {
                $data[$type->type] = $type->type;
            }
        }

        return $data;
    }
}
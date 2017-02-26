<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProcedureTypes extends Model
{
    public $timestamps = false;

    /**
     * @return array
     */
    public function getAllProceduresArray()
    {
        $procedures = self::all()->sortBy("procedure_name");
        foreach ($procedures as $procedure) {
            $result[$procedure->id] = $procedure->procedure_name;
        }
        return !empty($result) ? $result : [];
    }
}

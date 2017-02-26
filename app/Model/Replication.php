<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class Replication extends Model
{
    protected $connection = 'replica';
    protected $fillable = [
        'id',
        'entity_id',
        'entity_type',
        'access_token',
    ];

    public static function record($entity)
    {

        if ($entity->access_token != '') {
            try {
                $replication = Replication::where('access_token', $entity->access_token)->get();

            }catch (InvalidArgumentException $e){
                $fp = fopen('../storage/replica.sqlite', "w");
                Schema::connection('replica')->create('replications', function($table)
                {
                    $table->increments('id');
                    $table->integer('entity_id');
                    $table->string('entity_type',999);
                    $table->string('access_token');
                    $table->timestamps();
                });
                fclose($fp);
            }
            if ($replication->count() == 0) {
                $data = [
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'access_token' => $entity->access_token,
                ];
                $replication = new Replication($data);
                $replication->save();
            }
        }
    }
}

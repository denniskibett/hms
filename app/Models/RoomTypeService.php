<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeService extends Model
{
    use HasFactory;

    protected $table = 'room_type_services';

    protected $fillable = [
        'room_type_id',
        'service_name',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public $timestamps = false;

    /**
     * Room type this service belongs to.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CapacitacionModulo;
use App\Models\EmpleadoCapacitacion;

class Capacitacion extends Model
{
    protected $dateFormat = 'Ymd H:i:s';
    use HasFactory;

    protected $table = 'capacitacion';
    protected $primaryKey = 'id_capacitacion';
    public $timestamps = true; //la tabla capacitacion lleva created_at y updated_at

    protected $fillable = [
        'capacitacion',
        'codigo',
        'descripcion',
        'objetivo_general',
        'ruta_portada',
        'horas_estimadas',
        'porcentaje_aprobacion',
        'dias_vigencia',
        'obligatoria',
        'permite_autogestion',
        'estado',
        'created_by',
        'id_instructor',
        'id_capacitacion_instructor',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id_capacitacion' => 'integer',
        'horas_estimadas' => 'integer',
        'porcentaje_aprobacion' => 'decimal:2',
        'dias_vigencia' => 'integer',
        'obligatoria' => 'integer',
        'permite_autogestion' => 'integer',
        'estado' => 'integer',
        'created_by' => 'integer',
        'id_instructor' => 'integer',
        'id_capacitacion_instructor' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function instructor()
    {
        return $this->belongsTo(InstructorRrhh::class, 'id_instructor', 'id_instructor');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function areas()
    {
        return $this->hasMany(CapacitacionArea::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function modulos()
    {
        return $this->hasMany(CapacitacionModulo::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function puestosCapacitacion()
    {
        return $this->hasMany(PuestosCapacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function departamentosCapacitacion()
    {
        return $this->hasMany(DepartamentosCapacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function empleadosCapacitacionDirecta()
    {
        return $this->hasMany(EmpleadosCapacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function empleadosCapacitacion()
    {
        return $this->hasMany(EmpleadoCapacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function capacitacionModulos()
    {
        return $this->hasMany(CapacitacionModulo::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function empleadoCapacitaciones()
    {
        return $this->hasMany(EmpleadoCapacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }
}

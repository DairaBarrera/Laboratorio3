<?php

namespace App\Models;

use CodeIgniter\Model;

class InscripcionModel extends Model
{
    protected $table            = 'inscripciones';
    protected $primaryKey       = 'id_inscripcion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $allowedFields    = [
        'id_estudiante',
        'encargado_id',
        'ciclo_escolar',
        'grado',
        'seccion',
        'jornada',
        'fecha_inscripcion',
        'estado',
        'observaciones',
        'nombre_encargado',
        'apellido_encargado',
        'parentesco_encargado',
        'telefono_encargado',
        'dpi_encargado',
        'direccion_encargado',
        'foto_inscripcion'
    ];

    // Función con JOIN a estudiantes y padres, ordenada por grado
    public function obtenerInscripcionesConNombres()
    {
        return $this->select('inscripciones.*, 
                             estudiantes.nombres, 
                             estudiantes.apellidos, 
                             estudiantes.fotografia, 
                             padres.nombres as nombre_encargado, 
                             padres.apellidos as apellido_encargado')
                    ->join('estudiantes', 'estudiantes.id_estudiante = inscripciones.id_estudiante', 'left')
                    ->join('padres', 'padres.id_padre = inscripciones.encargado_id', 'left')
                    ->orderBy('inscripciones.grado', 'ASC')
                    ->findAll();
    }
}
<?php

namespace App\Controllers;

use App\Models\InscripcionModel;
use App\Models\EstudianteModel;
use App\Models\PadreModel;

class InscripcionController extends BaseController
{
    // Muestra la lista de todas las inscripciones
    public function index()
    {
        $inscripcionModel = new InscripcionModel();
        $data['inscripciones'] = $inscripcionModel->obtenerInscripcionesConNombres();

        return view('inscripciones/index', $data);
    }

    // Carga los datos para el formulario
    public function nueva()
    {
        $estudianteModel = new EstudianteModel();
        $padreModel = new PadreModel();

        $data['estudiantes'] = $estudianteModel->findAll();
        $data['padres'] = $padreModel->findAll();

        return view('inscripciones/nueva', $data);
    }

    // Guarda los datos con validación y la foto de la cámara web
    public function registrar()
    {
        $inscripcionModel = new InscripcionModel();

        // 1. Reglas de validación para asegurar la integridad de los datos
        $rules = [
            'id_estudiante'      => 'required',
            'encargado_id'       => 'required',
            'ciclo_escolar'      => 'required|exact_length[4]',
            'grado'              => 'required',
            'telefono_encargado' => 'permit_empty|numeric'
        ];

        // 2. Si la validación falla, regresa al formulario con los errores
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 3. Procesar la fotografía (Cámara Base64 o archivo tradicional)
        $nombreFoto = null;
        $fotoBase64 = $this->request->getPost('foto_base64'); // Nombre correcto del input oculto en la vista

        if (!empty($fotoBase64)) {
            // Decodificar la cadena Base64 generada por la cámara
            $imageParts = explode(';base64,', $fotoBase64);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imagePooled = base64_decode($imageParts[1]);

                $nombreFoto = 'inscripcion_' . time() . '.' . $imageType;
                $uploadPath = FCPATH . 'uploads/inscripciones/';

                // Crear la carpeta si no existe
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                file_put_contents($uploadPath . $nombreFoto, $imagePooled);
            }
        } else {
            // Si subieron la imagen mediante el input file tradicional
            $file = $this->request->getFile('foto');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $nombreFoto = $file->getRandomName();
                $file->move(FCPATH . 'uploads/inscripciones/', $nombreFoto);
            }
        }

        // 4. Preparación de datos (incluyendo la foto procesada)
        $data = [
            'id_estudiante'        => $this->request->getPost('id_estudiante'),
            'encargado_id'         => $this->request->getPost('encargado_id'),
            'ciclo_escolar'        => $this->request->getPost('ciclo_escolar'),
            'grado'                => $this->request->getPost('grado'),
            'seccion'              => $this->request->getPost('seccion'),
            'jornada'              => $this->request->getPost('jornada'),
            'fecha_inscripcion'    => $this->request->getPost('fecha_inscripcion') ?? date('Y-m-d'),
            'estado'               => $this->request->getPost('estado') ?? 'Inscrito',
            'observaciones'        => $this->request->getPost('observaciones'),
            
            // Datos del encargado
            'nombre_encargado'     => $this->request->getPost('nombre_encargado'),
            'apellido_encargado'   => $this->request->getPost('apellido_encargado'),
            'parentesco_encargado' => $this->request->getPost('parentesco_encargado'),
            'telefono_encargado'   => $this->request->getPost('telefono_encargado'),
            'dpi_encargado'        => $this->request->getPost('dpi_encargado'),
            'direccion_encargado'  => $this->request->getPost('direccion_encargado'),
            
            // Foto de inscripción (Nombre del archivo guardado en el servidor)
            'foto_inscripcion'     => $nombreFoto
        ];

        // 5. Intento de guardado
        if ($inscripcionModel->save($data)) {
            return redirect()->to(site_url('inscripciones'))->with('mensaje', 'Inscripción realizada con éxito.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al guardar en la base de datos.');
        }
    }

    // Reporte general
    public function imprimirReporteGeneral()
    {
        $inscripcionModel = new InscripcionModel();
        $data['inscripciones'] = $inscripcionModel->obtenerInscripcionesConNombres();

        return view('inscripciones/reporte_general', $data);
    }

    // PDF individual
    public function descargarPdf($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('inscripciones');
        $builder->select('inscripciones.*, estudiantes.nombres, estudiantes.apellidos, estudiantes.codigo, estudiantes.telefono as telefono_estudiante');
        $builder->join('estudiantes', 'estudiantes.id_estudiante = inscripciones.id_estudiante', 'left');
        $builder->where('inscripciones.id_inscripcion', $id);
        $data['inscripcion'] = $builder->get()->getRowArray();

        if (!$data['inscripcion']) {
            return redirect()->to(site_url('inscripciones'))->with('mensaje', 'Inscripción no encontrada.');
        }

        return view('inscripciones/comprobante_pdf', $data);
    }
}
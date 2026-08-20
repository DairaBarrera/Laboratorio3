<?php

namespace App\Controllers;
use App\Models\EstudianteModel;

class Estudiantes extends BaseController
{
    public function index()
    {
        $model = new EstudianteModel();
        // Usamos la función personalizada para traer los datos del padre/encargado
        $data['estudiantes'] = $model->obtenerEstudiantesConPadre();
        return view('estudiantes/index', $data);
    }

    public function nuevo()
    {
        return view('estudiantes/nuevo');
    }

    public function store()
    {
        $model = new EstudianteModel();
        
        $data = [
            'codigo' => $this->request->getPost('codigo'),
            'nombres' => $this->request->getPost('nombres'),
            'apellidos' => $this->request->getPost('apellidos'),
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento'),
            'genero' => $this->request->getPost('genero'),
            'lugar_nacimiento' => $this->request->getPost('lugar_nacimiento'),
            'nacionalidad' => $this->request->getPost('nacionalidad'),
            'direccion' => $this->request->getPost('direccion'),
            'telefono' => $this->request->getPost('telefono'),
            'email' => $this->request->getPost('email'),
            'grado' => $this->request->getPost('grado'),
            'seccion' => $this->request->getPost('seccion'),
            'jornada' => $this->request->getPost('jornada'),
            'anio_escolar' => $this->request->getPost('anio_escolar'),
            'tipo_sangre' => $this->request->getPost('tipo_sangre'),
            'alergias' => $this->request->getPost('alergias'),
            'enfermedades' => $this->request->getPost('enfermedades'),
            'medicamentos' => $this->request->getPost('medicamentos'),
            'contacto_emergencia' => $this->request->getPost('contacto_emergencia'),
            'estado' => $this->request->getPost('estado') ?? 'Activo'
        ];

        // Procesar la fotografía en el registro (store)
        $img = $this->request->getFile('fotografia');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $nombreAleatorio = $img->getRandomName();
            $img->move(ROOTPATH . 'public/uploads/estudiantes', $nombreAleatorio);
            $data['fotografia'] = $nombreAleatorio;
        }

        $model->save($data);
        return redirect()->to(site_url('estudiantes'))->with('mensaje', 'Estudiante registrado con éxito.');
    }

    public function editar($id)
    {
        $model = new EstudianteModel();
        $data['estudiante'] = $model->find($id);
        return view('estudiantes/editar', $data);
    }

    public function actualizar($id)
    {
        $model = new EstudianteModel();
        
        // Recoger todos los datos enviados por POST
        $data = $this->request->getPost();

        // Procesar la fotografía si se subió una nueva al editar
        $img = $this->request->getFile('fotografia');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $nombreAleatorio = $img->getRandomName();
            $img->move(ROOTPATH . 'public/uploads/estudiantes', $nombreAleatorio);
            $data['fotografia'] = $nombreAleatorio;

            // Opcional: Podrías borrar la foto anterior de la carpeta si deseas ahorrar espacio
            $estudianteAntiguo = $model->find($id);
            if (!empty($estudianteAntiguo['fotografia']) && file_exists(ROOTPATH . 'public/uploads/estudiantes/' . $estudianteAntiguo['fotografia'])) {
                @unlink(ROOTPATH . 'public/uploads/estudiantes/' . $estudianteAntiguo['fotografia']);
            }
        }

        $model->update($id, $data);
        return redirect()->to(site_url('estudiantes'))->with('mensaje', 'Estudiante actualizado con éxito.');
    }

    public function eliminar($id)
    {
        $model = new EstudianteModel();
        
        // Opcional: Borrar la foto física cuando se elimina el estudiante
        $estudiante = $model->find($id);
        if (!empty($estudiante['fotografia']) && file_exists(ROOTPATH . 'public/uploads/estudiantes/' . $estudiante['fotografia'])) {
            @unlink(ROOTPATH . 'public/uploads/estudiantes/' . $estudiante['fotografia']);
        }

        $model->delete($id);
        return redirect()->to(site_url('estudiantes'));
    }

    // Método para autocompletar en el módulo de Inscripciones
    public function json($id)
    {
        $model = new EstudianteModel();
        $estudiante = $model->find($id);
        
        if (!empty($estudiante['fecha_nacimiento'])) {
            $fnac = new \DateTime($estudiante['fecha_nacimiento']);
            $hoy = new \DateTime();
            $estudiante['edad'] = $hoy->diff($fnac)->y;
        } else {
            $estudiante['edad'] = 'N/D';
        }

        return $this->response->setJSON($estudiante);
    }
}
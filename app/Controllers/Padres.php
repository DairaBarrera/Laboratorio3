<?php

namespace App\Controllers;
use App\Models\PadreModel;

class Padres extends BaseController
{
    public function index()
    {
        $model = new PadreModel();
        // Mantenemos tu conexión específica
        $data['padres'] = $model->obtenerPadresConEstudiante();
        return view('padres/index', $data);
    }

    public function nuevo()
    {
        // Seguridad: Solo admin puede crear
        if (session('id_rol') != 1) {
            return redirect()->to(site_url('padres'));
        }
        return view('padres/nuevo');
    }

    public function store()
    {
        if (session('id_rol') != 1) {
            return redirect()->to(site_url('padres'));
        }

        $model = new PadreModel();
        
        $data = [
            'nombres'              => $this->request->getPost('nombres'),
            'apellidos'            => $this->request->getPost('apellidos'),
            'parentesco'           => $this->request->getPost('parentesco'),
            'dpi'                  => $this->request->getPost('dpi'),
            'fecha_nacimiento'     => $this->request->getPost('fecha_nacimiento'),
            'telefono'             => $this->request->getPost('telefono'),
            'telefono_alternativo' => $this->request->getPost('telefono_alternativo'),
            'correo'               => $this->request->getPost('correo'),
            'direccion'            => $this->request->getPost('direccion'),
            'ocupacion'            => $this->request->getPost('ocupacion'),
            'es_principal'         => $this->request->getPost('es_principal') ? 1 : 0,
            'autorizado_recoger'   => $this->request->getPost('autorizado_recoger') ? 1 : 0,
            'observaciones'        => $this->request->getPost('observaciones')
        ];

        $model->save($data);
        return redirect()->to(site_url('padres'))->with('mensaje', 'Encargado registrado con éxito.');
    }

    public function editar($id)
    {
        if (session('id_rol') != 1) {
            return redirect()->to(site_url('padres'));
        }

        $model = new PadreModel();
        $data['padre'] = $model->find($id);

        if (!$data['padre']) {
            return redirect()->to(site_url('padres'))->with('error', 'Encargado no encontrado.');
        }

        return view('padres/editar', $data);
    }

    public function actualizar($id)
    {
        if (session('id_rol') != 1) {
            return redirect()->to(site_url('padres'));
        }

        $model = new PadreModel();

        $data = [
            'nombres'              => $this->request->getPost('nombres'),
            'apellidos'            => $this->request->getPost('apellidos'),
            'parentesco'           => $this->request->getPost('parentesco'),
            'dpi'                  => $this->request->getPost('dpi'),
            'fecha_nacimiento'     => $this->request->getPost('fecha_nacimiento'),
            'telefono'             => $this->request->getPost('telefono'),
            'telefono_alternativo' => $this->request->getPost('telefono_alternativo'),
            'correo'               => $this->request->getPost('correo'),
            'direccion'            => $this->request->getPost('direccion'),
            'ocupacion'            => $this->request->getPost('ocupacion'),
            'es_principal'         => $this->request->getPost('es_principal') ? 1 : 0,
            'autorizado_recoger'   => $this->request->getPost('autorizado_recoger') ? 1 : 0,
            'observaciones'        => $this->request->getPost('observaciones')
        ];

        $model->update($id, $data);
        return redirect()->to(site_url('padres'))->with('mensaje', 'Encargado actualizado con éxito.');
    }

    public function eliminar($id)
    {
        if (session('id_rol') != 1) {
            return redirect()->to(site_url('padres'));
        }

        $model = new PadreModel();
        $model->delete($id);

        return redirect()->to(site_url('padres'))->with('mensaje', 'Encargado eliminado con éxito.');
    }

    public function json($id)
    {
        $model = new PadreModel();
        $encargado = $model->find($id);

        if (!$encargado) {
            return $this->response->setJSON([]);
        }

        $data = [
            'nombres'    => $encargado['nombres'] ?? $encargado['nombre'] ?? '',
            'apellidos'  => $encargado['apellidos'] ?? $encargado['apellido'] ?? '',
            'parentesco' => $encargado['parentesco'] ?? '',
            'dpi'        => $encargado['dpi'] ?? '',
            'telefono'   => $encargado['telefono'] ?? '',
            'direccion'  => $encargado['direccion'] ?? ''
        ];

        return $this->response->setJSON($data);
    }
}
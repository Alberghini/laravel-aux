<?php

namespace LaravelAux;

use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class BaseController
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var BaseService
     */
    protected $service;

    /**
     * @var FormRequest
     */
    protected $request;

    /**
     * BaseController constructor.
     *
     * @param Model $model
     * @param BaseService $service
     * @param FormRequest $request
     */
    public function __construct(Model $model, BaseService $service, FormRequest $request)
    {
        $this->model = $model;
        $this->service = $service;
        $this->request = $request;
    }

    /**
     * Method to get Model Objects
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index(Request $request)
    {
        return $this->service->get('*', $request);
    }

    /**
     * Method to Create Model Object
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->validation();
        $condition = $this->service->create($request->all());
        if ($condition['status'] === '00') {
            return response()->json('Registro criado com sucesso', 200);
        }
        return response()->json($condition['message'], 500);
    }

    /**
     * Method to Update Model Object
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request, int $id)
    {
        $this->validation('PUT');
        $condition = $this->service->update($request->all(), $id);
        if ($condition['status'] === '00') {
            return response()->json('Registro atualizado com sucesso', 200);
        }
        return response()->json($condition['message'], 500);
    }

    /**
     * Method to get Model Object
     *
     * @param $id
     * @param Request $request
     * @return BaseService[]|\Illuminate\Database\Eloquent\Collection
     */
    public function show(int $id, Request $request)
    {
        $request->merge(['id' => $id]);
        return $this->service->get('*', $request);
    }

    /**
     * Method to delete Model Object
     *
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function destroy($id)
    {
        if ($this->service->delete($id)) {
            return response()->json('Registro removido com sucesso', 200);
        }
        return response()->json('Não foi possível remover o registro', 500);
    }

    /**
     * Method to validate Request
     *
     * @param $method
     * @return array|void
     */
    protected function validation($method = null)
    {
        $validator = Validator::make(request()->all(), $this->request->rules($method), $this->request->messages(), $this->request->attributes());
        if ($validator->fails()) {
            throw new HttpResponseException(response()->json($validator->errors()->toArray(), 500));
        }
    }
}
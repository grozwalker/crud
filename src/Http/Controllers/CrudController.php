<?php

namespace Grozwalker\Crud\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrudController extends Controller
{
    protected $name;
    protected $title;
    protected $rules = [];



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return $this->view("dashboard.{$this->name}.index", [camel_case(str_plural($this->name)) => $this->getList()])->with($this->indexParameters());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $modelName = $this->getModel();
        return $this->view("dashboard.{$this->name}.show", [camel_case($this->name) => new $modelName])->with($this->viewParameters());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Eloquent|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $modelName = $this->getModel();
        $fields = $this->prepareEntityFields($request);
        $entity = $modelName::create($fields);

        $this->afterSave($entity, $request);

        if ($request->ajax()) {
            return $entity;
        } else {
            return $this->redirectAfterStore($entity)->with(['alert-success' => 'Запись создана']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $modelName = $this->getModel();
        return $this->view("dashboard.{$this->name}.show", [camel_case($this->name) => $modelName::findOrFail($id)])->with($this->viewParameters());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $modelName = $this->getModel();
        return $this->view("dashboard.{$this->name}.show", [camel_case($this->name) => $modelName::findOrFail($id)])->with($this->viewParameters());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Eloquent|\Illuminate\Http\Response|[]
     */
    public function update(Request $request, $id)
    {
        $modelName = $this->getModel();
        $entity = $modelName::find($id);
        $fields = $this->prepareEntityFields($request);
        $entity->update($fields);

        $this->afterSave($entity, $request);

        if ($request->ajax()) {
            return $entity;
        } else {
            return $this->redirectAfterStore($entity)->with(['alert-success' => 'Запись обновлена']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modelName = $this->getModel();
        $result = $modelName::destroy($id);
        $messages = $result === 0 ? ['alert-danger' => 'Нет такой записи'] : ['alert-success' => 'Удалено'];

        return $this->redirectAfterDestroy()->with($messages);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function getList()
    {
        $modelName = $this->getModel();

        return $modelName::all();
    }

    /**
     * @return \Eloquent|string
     */
    protected function getModel()
    {
        return '\App\Models\\' . studly_case($this->name);
    }

    /**
     * @return string
     */
    protected function getRouteName()
    {
        return $this->name;
    }

    protected function viewParameters()
    {
        return [];
    }

    protected function indexParameters()
    {
        return [];
    }

    /**
     * @param $name
     * @param $params
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function view($name, $params)
    {
        return view($name, $params, ['name' => $this->name, 'title' => $this->title ?: $this->name]);
    }

    protected function redirectAfterStore($entity)
    {
        return redirect(route(".{$this->getRouteName()}.show", [$this->name => $entity->id]));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function redirectAfterDestroy()
    {
        return redirect(route(".{$this->getRouteName()}.index"));
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  array $rules
     * @param  array $messages
     * @param  array $customAttributes
     * @return void
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $request->session()->flash('alert-danger', 'Проверьте правильность заполнения полей');
            $this->throwValidationException($request, $validator);
        }
    }

    protected function prepareEntityFields(Request $request)
    {
        $this->validate($request, $this->rules);
        return $request->except(['_token', '_method']);
    }

    protected function fixEmptyFields($fields, $columns)
    {
        foreach ($columns as $column) {
            $fields[$column] = empty($fields[$column]) ? NULL : $fields[$column];
        }
        return $fields;
    }

    protected function afterSave($entity, Request $request)
    {

    }
}
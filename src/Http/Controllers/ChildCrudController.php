<?php

namespace App\Http\Controllers\Dashboard;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChildCrudController extends Controller
{
    protected $name;
    protected $parentName;

    protected $parentProperty;

    protected $title;
    protected $rules = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($parentId)
    {
        $modelName = $this->getModel();

        return $modelName::where($this->parentProperty, $parentId)->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $modelName = $this->getModel();
        return $this->view("dashboard.{$this->name}.show", [$this->name => new $modelName]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Eloquent|\Illuminate\Http\Response
     */
    public function store(Request $request, $parentId)
    {
        $this->validate($request, $this->rules);
        $modelName = $this->getModel();

        $data = $request->except(['_token', '_method']);
        $data[$this->parentProperty] = $parentId;

        $entity = $modelName::create($data);
        if ($request->ajax()) {
            return $entity;
        } else {
            return redirect(route("dashboard.{$this->getRouteName()}.show", [$this->name => $entity]))->with(['alert-success' => 'Запись создана']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelName = $this->getModel();
        return $this->view("dashboard.{$this->name}.show", [$this->name => $modelName::find($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $modelName = $this->getModel();
        return $this->view("dashboard.{$this->name}.show", [$this->name => $modelName::find($id)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $parentId
     * @param  int $id
     * @return \Eloquent|\Illuminate\Http\Response
     */
    public function update(Request $request, $parentId, $id)
    {
        $this->validate($request, $this->rules);
        $modelName = $this->getModel();
        $entity = $modelName::find($id);
        $entity->update($request->except(['_token', '_method']));

        if ($request->ajax()) {
            return $entity;
        } else {
            return redirect(route("dashboard.{$this->getRouteName()}.show", [$this->name => $id]))->with(['alert-success' => 'Запись обновлена']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $parentId
     * @param  int $id
     * @return array|\Illuminate\Http\Response
     */
    public function destroy(Request $request, $parentId, $id)
    {
        $modelName = $this->getModel();
        $result = $modelName::destroy($id);

        $messages = $result === 0 ? ['alert-danger' => 'Нет такой записи'] : ['alert-success' => 'Удалено'];

        if ($request->ajax()) {
            return ['success' => true, 'messages' => $messages];
        } else {
            return redirect(route("dashboard.{$this->getRouteName()}.index"))->with($messages);
        }
    }

    /**
     * @return \Eloquent|string
     */
    protected function getModel()
    {
        return '\App\\Entities\\' . studly_case($this->name);
    }

    /**
     * @return \Eloquent|string
     */
    protected function getParentModel()
    {
        return '\App\\' . studly_case($this->parentName);
    }

    protected function getRouteName()
    {
        return str_replace('_', '', $this->name);
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
        Validator::make($request->all(), $rules, $messages, $customAttributes)->validate();
    }
}

<?php

namespace Grozwalker\Crud;


use Illuminate\Support\ServiceProvider;

class CrudServiceProvaider extends ServiceProvider
{
    public function boot(){
 
    }

    public function register()
    {
        $this->app->singleton('crud', function(){
           return new Crud;
        });
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Requests\Request as FormRequest;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

class RequestServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configureFormRequests();
    }

    protected function configureFormRequests()
    {

        $this->app->afterResolving(function (ValidatesWhenResolved $resolved) {
            $resolved->validate();
        });

        $this->app->resolving(function (FormRequest $request, $app) {
            $this->initializeRequest($request, $app['request']);
        });
    }

    /**
     * Initialize the form request with data from the given request.
     *
     * @param  \App\Http\Requests\FormRequest  $form
     * @param  \Symfony\Component\HttpFoundation\Request  $current
     * @return void
     */
    protected function initializeRequest(FormRequest $form, Request $current)
    {
        $files = $current->files->all();

        $files = is_array($files) ? array_filter($files) : $files;

        $form->initialize(
            $current->query->all(), $current->request->all(), $current->attributes->all(),
            $current->cookies->all(), $files, $current->server->all(), $current->getContent()
        );

        $form->setContainer($this->app);

    }
}

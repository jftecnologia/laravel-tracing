<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelTracing extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JuniorFontenele\LaravelTracing\LaravelTracing::class;
    }
}

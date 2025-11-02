<?php

namespace App\Contracts\Actions;

interface ActionInterface
{
    public function execute(...$args);
}

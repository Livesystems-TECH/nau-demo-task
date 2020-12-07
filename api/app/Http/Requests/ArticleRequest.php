<?php

namespace App\Http\Requests;

use LaravelMethodFormRequest\FormRequest;

class ArticleRequest extends FormRequest
{
    /**
     * POST request validation rules.
     *
     * @return array
     */
    public function createRules(): array
    {
        return [
            'title' => 'required|string',
        ];
    }

    /**
     * PUT/PATCH request validation rules.
     *
     * @return array
     */
    protected function updateRules(): array
    {
        return [
            'title' => 'string',
        ];
    }
}

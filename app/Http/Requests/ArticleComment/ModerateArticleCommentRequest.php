<?php

namespace App\Http\Requests\ArticleComment;

use Illuminate\Foundation\Http\FormRequest;

class ModerateArticleCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_approved' => ['required', 'boolean'],
        ];
    }
}

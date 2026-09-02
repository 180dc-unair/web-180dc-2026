<?php

namespace App\Http\Requests\ArticleComment;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:20000'],
            'parent_id' => ['nullable', 'uuid', 'exists:article_comments,id'],
        ];
    }
}

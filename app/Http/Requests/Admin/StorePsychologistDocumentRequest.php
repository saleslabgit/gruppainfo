<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Domain\User\UserDocumentType;
use App\Models\User;
use App\Models\UserDocument;
use App\Support\UploadedDocumentMime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

final class StorePsychologistDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $psychologist = $this->route('psychologist');

        return $psychologist instanceof User && ($this->user()?->can('create', [UserDocument::class, $psychologist]) ?? false);
    }

    public function rules(): array
    {
        $fileRule = File::types(['pdf', 'jpg', 'jpeg', 'png']);
        $maximum = config('documents.max_upload_kb');

        if (is_int($maximum) && $maximum > 0) {
            $fileRule->max($maximum);
        }

        return [
            'type' => ['required', Rule::enum(UserDocumentType::class)],
            'document' => [
                'required',
                $fileRule,
                'mimetypes:application/pdf,image/jpeg,image/png',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $file = $this->file('document');

            if ($file instanceof UploadedFile && ! in_array(UploadedDocumentMime::detect($file), UploadedDocumentMime::ALLOWED, true)) {
                $validator->errors()->add('document', 'Допустимы только PDF, JPEG и PNG файлы.');
            }
        });
    }
}

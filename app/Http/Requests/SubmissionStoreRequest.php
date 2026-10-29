<?php

namespace App\Http\Requests;

use App\Http\Controllers\SubmissionController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class SubmissionStoreRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'required_without:code', 'file', 'max:200', 'extensions:'.implode(',', SubmissionController::EXTENSIONS)],
            'code' => ['nullable', 'required_without:file', 'string', 'max:200000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $code = $this->code();

                if (!mb_check_encoding($code, 'UTF-8') || trim($code) === '') {
                    $this->uploadedFile() instanceof UploadedFile
                        ? $validator->errors()->add('file', 'Dit bestand bevat geen leesbare code.')
                        : $validator->errors()->add('code', 'Plak hier leesbare code.');
                }
            },
        ];
    }

    /**
     * The code from the uploaded file, or else the pasted code.
     */
    public function code(): string
    {
        $file = $this->uploadedFile();

        return $file instanceof UploadedFile ? (string) $file->get() : $this->string('code')->toString();
    }

    public function filename(): ?string
    {
        $file = $this->uploadedFile();

        return $file instanceof UploadedFile ? Str::limit(basename($file->getClientOriginalName()), 255, '') : null;
    }

    private function uploadedFile(): ?UploadedFile
    {
        $file = $this->file('file');

        return $file instanceof UploadedFile ? $file : null;
    }
}

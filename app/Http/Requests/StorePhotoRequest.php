<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $maxMb      = (int) \App\Models\Setting::get('max_upload_size_mb', 10);
        $maxKb      = $maxMb * 1024;
        $mimes      = \App\Models\Setting::get('allowed_file_types', 'jpg,jpeg,png,webp,gif');

        return [
            'image'   => 'required|array',
            'image.*' => "image|mimes:{$mimes}|max:{$maxKb}",
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'tags'        => 'nullable|string|max:500',
            'board_id'    => 'nullable|exists:boards,id',
        ];
    }

    public function messages(): array
    {
        $maxMb = \App\Models\Setting::get('max_upload_size_mb', 10);
        $mimes = \App\Models\Setting::get('allowed_file_types', 'jpg,jpeg,png,webp,gif');

        return [
            'image.required'  => 'Silakan pilih foto untuk diupload.',
            'image.*.image'   => 'File harus berupa gambar.',
            'image.*.mimes'   => "Format yang didukung: {$mimes}.",
            'image.*.max'     => "Ukuran foto maksimal {$maxMb}MB.",
            'title.max'       => 'Judul maksimal 255 karakter.',
        ];
    }
}

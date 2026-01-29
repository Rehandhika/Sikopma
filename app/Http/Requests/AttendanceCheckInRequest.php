<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceCheckInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedule_assignment_id' => [
                'required',
                'integer',
                Rule::exists('schedule_assignments', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id())
                        ->where('date', today())
                        ->where('status', 'scheduled');
                }),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
            'photo_proof' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'schedule_assignment_id.required' => 'ID jadwal diperlukan.',
            'schedule_assignment_id.exists' => 'Jadwal tidak valid atau tidak ditemukan.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
            'photo_proof.image' => 'Bukti foto harus berupa gambar.',
            'photo_proof.mimes' => 'Format foto harus jpeg, png, atau jpg.',
            'photo_proof.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }

    /**
     * Get validated and sanitized data
     */
    public function getValidatedData(): array
    {
        $data = $this->validated();

        // Sanitize notes to prevent XSS
        if (isset($data['notes'])) {
            $data['notes'] = strip_tags($data['notes']);
            $data['notes'] = trim($data['notes']);
        }

        return $data;
    }
}

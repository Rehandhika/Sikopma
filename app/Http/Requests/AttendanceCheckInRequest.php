<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
                })
            ],
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
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
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'schedule_assignment_id.required' => 'ID jadwal diperlukan.',
            'schedule_assignment_id.exists' => 'Jadwal tidak valid atau tidak ditemukan.',
            'latitude.required' => 'Lokasi diperlukan untuk check-in.',
            'latitude.numeric' => 'Koordinat latitude harus berupa angka.',
            'latitude.between' => 'Koordinat latitude tidak valid.',
            'longitude.required' => 'Lokasi diperlukan untuk check-in.',
            'longitude.numeric' => 'Koordinat longitude harus berupa angka.',
            'longitude.between' => 'Koordinat longitude tidak valid.',
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

    /**
     * Validate geofence after basic validation
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (config('sikopma.attendance.require_geolocation', true)) {
                $latitude = $this->input('latitude');
                $longitude = $this->input('longitude');

                if ($latitude && $longitude && !is_within_geofence($latitude, $longitude)) {
                    $validator->errors()->add(
                        'location',
                        'Anda berada di luar area yang diizinkan untuk check-in.'
                    );
                }
            }
        });
    }
}

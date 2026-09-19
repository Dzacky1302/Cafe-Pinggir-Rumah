<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Aturan checkout berbeda menurut tipe pesanan:
 *  - dine_in  : wajib nomor meja, tanpa nomor HP
 *  - take_away: wajib nomor HP + jam pengambilan
 *
 * Aturan ini sebelumnya ditulis inline di dalam controller.
 */
class StoreCheckoutRequest extends FormRequest
{
    private const PICKUP_SLOTS = [
        'now', '10:00', '11:00', '12:00', '13:00', '14:00',
        '15:00', '16:00', '17:00', '18:00', '19:00', '20:00',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $rules = [
            'order_type' => ['required', 'in:dine_in,take_away'],
            'customer_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->input('order_type') === 'dine_in') {
            $rules['table_number'] = ['required', 'string', 'max:10'];
        } else {
            $rules['phone'] = ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'];
            $rules['pickup_time'] = ['required', 'string', 'in:'.implode(',', self::PICKUP_SLOTS)];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'table_number.required' => 'Nomor meja wajib diisi untuk pesanan dine in.',
            'phone.required' => 'Nomor HP wajib diisi untuk pesanan take away.',
            'phone.regex' => 'Nomor HP hanya boleh berisi angka, spasi, tanda + dan -.',
            'pickup_time.required' => 'Jam pengambilan wajib dipilih.',
        ];
    }
}
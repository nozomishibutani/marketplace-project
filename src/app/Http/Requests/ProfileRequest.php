<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => ['required', 'string','max:20',
                            Rule::unique('users', 'username')
                                ->ignore($this->user()->id)
                                ->whereNull('deleted_at')
                            ],
            'postcode' => ['required', 'string','regex:/^\d{3}-\d{4}$/'],
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable','image','mimes:jpeg,jpg,png'],
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'お名前を入力してください',
            'username.max' => 'お名前は20文字以内で入力してください',
            'username.string' => 'お名前は文字列で入力してください',
            'username.unique' => 'このお名前は使用できません',
            'postcode.required' => '郵便番号を入力してください',
            'postcode.regex' => '郵便番号は半角数字3桁-4桁の形式（例: 123-4567）で入力してください',
            'postcode.string' => '郵便番号は文字列で入力してください',
            'address.required' => '住所を入力してください',
            'address.max' => '住所は255文字以内で入力してください',
            'address.string' => '住所は文字列で入力してください',
            'building.string' => '建物名は文字列で入力してください',
            'building.max' => '建物名は255文字以内で入力してください',
            'avatar.image' => '有効な画像ファイルを選択してください。',
            'avatar.mimes' => 'jpeg・jpg・png形式の画像を選択してください。',
        ];
    }
}

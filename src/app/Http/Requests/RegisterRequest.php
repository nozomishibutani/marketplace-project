<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'username' => ['required', 'string','max:20'],
            // 未入力時に required と形式エラーの2つを表示するため email ではなく regex を使用
            'email' => ['required', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', 'max:255','unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed',],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'お名前を入力してください',
            'username.max' => 'お名前は20文字以内で入力してください',
            'username.string' => 'お名前は文字列で入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.max' => 'メールアドレスは255文字以内で入力してください',
            'email.regex' => 'メールアドレスはメール形式で入力してください',
            'email.unique' => 'このメールアドレスは利用できません',
            'password.required' => 'パスワードを入力してください',
            'password.string' => 'パスワードは文字列で入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.max' => 'パスワードは255文字以内で入力してください',
            'password.confirmed' => 'password_confirmed',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->email) {
                $validator->errors()->add('email', 'メールアドレスはメール形式で入力してください');
            }
        });
    }
}

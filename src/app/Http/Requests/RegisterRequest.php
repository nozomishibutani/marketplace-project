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
    public function rules()
    {
        return [
            'username' => ['required', 'string','max:255','unique:users,username'],
            'email' => ['required', 'email', 'max:255','unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255','confirmed',],
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'ユーザー名を入力してください',
            'username.max' => 'ユーザー名は255文字以内で入力してください',
            'username.string' => 'ユーザー名は文字列で入力してください',
            'username.unique' => 'このユーザー名は使用できません',
            'email.required' => 'メールアドレスを入力してください',
            'email.max' => 'メールアドレスは255文字以内で入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            'email.unique' => 'このメールアドレスは利用できません',
            'password.required' => 'パスワードを入力してください',
            'password.string' => 'パスワードは文字列で入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.max' => 'パスワードは255文字以内で入力してください',
            'password.confirmed' => 'パスワードと一致しません',
        ];
    }
}

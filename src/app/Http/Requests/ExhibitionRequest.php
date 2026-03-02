<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'img' => ['required','image','mimes:jpeg,jpg,png'],
            'categories' => ['required', 'array'],
            'categories.*' => ['exists:categories,id'],
            'condition' => ['required', Rule::in(array_keys(Item::CONDITIONS))],
            'description' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:1', 'max:2147483647'],
        ];
    }
    public function messages()
    {
        return [
            'name.required' => '商品名を入力してください',
            'name.string' => '商品名は文字列で入力してください',
            'name.max' => '商品名は255文字以内で入力してください',
            'brand_name.max' => 'ブランドネームは255文字以内で入力してください',
            'brand_name.string' => 'ブランドネームは文字列で入力してください',
            'img.required' => '画像をアップロードしてください。',
            'img.image' => '有効な画像ファイルを選択してください。',
            'img.mimes' => 'jpeg・jpg・png形式の画像を選択してください。',
            'categories.array' => '商品カテゴリーの選択が正しくありません',
            'categories.required' => '商品カテゴリーを選択してください',
            'categories.*.exists' => '商品カテゴリーの選択が正しくありません。',
            'condition.required' => '商品の状態を選択してください',
            'condition.in' => '商品の状態を選択してください',
            'description.max' => '商説明は255文字以内で入力してください',
            'description.required' => '商品説明を入力してください',
            'description.string' => '商品説明は文字列で入力してください',
            'price.integer' => '商品価格は半角数字で入力してください',
            'price.required' => '商品価格を入力してください',
            'price.min' => '商品価格は0円以上を入力してください',
            'price.max' => '商品価格は2,147,483,647円以内で入力してください',
        ];
    }
}

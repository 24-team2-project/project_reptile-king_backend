<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ReptileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 인증은 미들웨어에서 진행하고 있다
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nickname'  => ['required', 'string', 'max:255'],
            'species'   => ['required'],
            'gender'    => ['required', 'max:1', 'in:M,F'],
            'birth'       => [ 'nullable'],
            'memo'      => [ 'string', 'nullable'],
            'images'    => [ 'array', 'nullable'],
        ];
    }

    public function messages(){
        return [
            'nickname.required' => '닉네임은 필수 항목입니다.',
            'nickname.string'   => '닉네임은 문자열이어야 합니다.',
            'nickname.max'      => '닉네임은 최대 255자까지 입력 가능합니다.',
            'species.required'  => '종 입력은 필수 항목입니다.',
            'gender.required'   => '성별은 필수 항목입니다.',
            'gender.max'   => '성별은 최대 1자까지 입력 가능합니다.',
            'gender.in'   => '성별은 M or F 중 하나이어야 합니다.',
            'memo.string'   => '메모는 문자열이어야 합니다.'
        ];
    }

    // Controller에서 catch문을 사용하기 위해서
    protected function failedValidation(Validator $validator)
    {
        
    }

}

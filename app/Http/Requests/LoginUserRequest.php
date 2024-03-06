<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class LoginUserRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:255','email:rfc, strict'],
            // 기본(최소 8자, 알파벳, 숫자 포함) + 대소문자, 특수문자
            'password' => ['required', Rules\Password::defaults()->mixedCase()->symbols() ],
        ];
    }

    public function messages(){
        return [
            'email.required' => '이메일은 필수 항목입니다.',
            'email.string' => '이메일은 문자열이어야 합니다.',
            'email.max' => '이메일은 최대 255자까지 입력 가능합니다.',
            'email.email' => '유효한 이메일 주소를 입력해주세요.',
            'password.required' => '비밀번호는 필수 항목입니다.',
            'password.password' => '비밀번호는 최소 8자, 알파벳, 숫자, 특수문자, 대소문자를 포함해야 합니다.',
        ];
    }

    // Controller에서 catch문을 사용하기 위해서
    protected function failedValidation(Validator $validator) {}

}

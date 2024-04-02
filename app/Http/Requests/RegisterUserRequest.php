<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    protected $stopOnFirstFailure = true; // 유효성검사에서 실패하면 그 이후는 중단

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 이 값은 현재 유저가 저장이 가능한 지 검사하는 역할
        // 지금은 유저의 권한 개념이 없으므로 true
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
            'name' => ['required', 'string','max:255'],

            // 테스트용 : rfc, strict 사용
            // 'email' => ['required', 'string', 'max:255', 'unique:users,email', 'email:rfc,dns,strict,spoof'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email','email:rfc,strict'],
            
            // 최소 8자, 알파벳, 숫자 포함 + 특수문자, 대소문자
            'password' => ['required', 'confirmed',Rules\Password::defaults()->mixedCase()->symbols() ],
            
            'nickname' => ['required', 'string', 'max:255', 'unique:users,nickname'],
            // 'address' => ['string', 'nullable'],
            'phone' => ['string', 'nullable'],
        ];
    }

    public function messages(){
        return [
            'name.required' => '이름은 필수 항목입니다.',
            'name.string' => '이름은 문자열이어야 합니다.',
            'name.max' => '이름은 최대 255자까지 입력 가능합니다.',
            'email.required' => '이메일은 필수 항목입니다.',
            'email.string' => '이메일은 문자열이어야 합니다.',
            'email.max' => '이메일은 최대 255자까지 입력 가능합니다.',
            'email.unique' => '이미 사용 중인 이메일입니다.',
            'email.email' => '유효한 이메일 주소를 입력해주세요.',
            'password.required' => '비밀번호는 필수 항목입니다.',
            'password.password' => '비밀번호는 최소 8자, 알파벳, 숫자, 특수문자, 대소문자를 포함해야 합니다.',
            'nickname.required' => '닉네임은 필수 항목입니다.',
            'nickname.string' => '닉네임은 문자열이어야 합니다.',
            'nickname.max' => '닉네임은 최대 255자까지 입력 가능합니다.',
            'nickname.unique' => '이미 사용 중인 닉네임입니다.',
            // 'address.string' => '주소는 문자열이어야 합니다.',
            'phone.string' => '전화번호는 문자열이어야 합니다.',
        ];
    }

    // Controller에서 catch문을 사용하기 위해서
    protected function failedValidation(Validator $validator) {}

}

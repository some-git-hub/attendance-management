<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'clock_in'   => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'clock_out'  => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],

            'rest_in'    => ['array'],
            'rest_in.*'  => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],

            'rest_out'   => ['array'],
            'rest_out.*' => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],

            'remark'     => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'clock_in'   => '出勤時間',
            'clock_out'  => '退勤時間',
            'rest_in.*'  => '休憩開始時間',
            'rest_out.*' => '休憩終了時間',
            'remark'     => '備考',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.regex'   => ':attributeは「HH:MM」形式で入力してください（例：09:00）',
            'clock_out.regex'  => ':attributeは「HH:MM」形式で入力してください（例：18:00）',
            'rest_in.*.regex'  => ':attributeは「HH:MM」形式で入力してください（例：12:00）',
            'rest_out.*.regex' => ':attributeは「HH:MM」形式で入力してください（例：13:00）',
            'remark.required'  => ':attributeを記入してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $timeRegex = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';

            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            $restIns  = $this->input('rest_in', []);
            $restOuts = $this->input('rest_out', []);

            // 出退勤時間の前後関係チェック
            if ($clockIn && $clockOut) {
                if (preg_match($timeRegex, $clockIn) && preg_match($timeRegex, $clockOut)) {
                    if (strtotime($clockIn) >= strtotime($clockOut)) {
                        $validator->errors()->add(
                            'clock_in',
                            '出勤時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }

            foreach ($restIns as $index => $restIn) {
                $restOut = $restOuts[$index] ?? null;

                $hasRestIn  = filled($restIn);
                $hasRestOut = filled($restOut);

                $restInValid  = $hasRestIn  && preg_match($timeRegex, $restIn);
                $restOutValid = $hasRestOut && preg_match($timeRegex, $restOut);

                // 休憩開始時間が未入力
                if (!$hasRestIn && $hasRestOut) {
                    $validator->errors()->add(
                        "rest_in.$index",
                        '休憩開始時間を入力してください'
                    );
                }

                // 休憩終了時間が未入力
                if ($hasRestIn && !$hasRestOut) {
                    $validator->errors()->add(
                        "rest_out.$index",
                        '休憩終了時間を入力してください'
                    );
                }

                // 休憩時間の前後関係チェック
                if ($hasRestIn && $hasRestOut && $restInValid && $restOutValid) {
                    if (strtotime($restIn) >= strtotime($restOut)) {
                        $validator->errors()->add(
                            "rest_in.$index",
                            '休憩時間が不適切な値です'
                        );
                    }
                }

                // 休憩開始時間と出勤時間の前後関係チェック
                if ($hasRestIn && $clockIn && strtotime($restIn) < strtotime($clockIn)) {
                    $validator->errors()->add(
                        "rest_in.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                // 休憩開始時間と退勤時間の前後関係チェック
                if ($hasRestIn && $clockOut && strtotime($restIn) > strtotime($clockOut)) {
                    $validator->errors()->add(
                        "rest_in.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                // 休憩終了時間と退勤時間の前後関係チェック
                if ($hasRestOut && $clockOut && strtotime($restOut) > strtotime($clockOut)) {
                    $validator->errors()->add(
                        "rest_in.$index",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}

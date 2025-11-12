<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * 名前が未入力の場合はバリデーションメッセージが表示される
     */
    public function test_it_shows_error_when_name_is_empty()
    {
        $email = 'user'.uniqid().'@example.com';
        $response = $this->post('/register', [
            'name' => '',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }


    /**
     * メールアドレスが未入力の場合はバリデーションメッセージが表示される
     */
    public function test_it_shows_error_when_email_is_empty()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }


    /**
     * パスワードが未入力の場合はバリデーションメッセージが表示される
     */
    public function test_it_shows_error_when_password_is_empty()
    {
        $email = 'user'.uniqid().'@example.com';
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => $email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }


    /**
     * パスワードが8文字未満の場合はバリデーションメッセージが表示される
     */
    public function test_it_shows_error_when_password_is_too_short()
    {
        $email = 'user'.uniqid().'@example.com';
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => $email,
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);
    }


    /**
     * パスワードが確認用と一致しない場合はバリデーションメッセージが表示される
     */
    public function test_it_shows_error_when_password_confirmation_does_not_match()
    {
        $email = 'user'.uniqid().'@example.com';
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません'
        ]);
    }


    /**
     * 全て正しく入力した場合はユーザー情報が登録される
     */
    public function test_it_registers_user_when_all_fields_are_valid()
    {
        $email = 'user'.uniqid().'@example.com';
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);

        $response->assertRedirect('/email/verify');
    }
}

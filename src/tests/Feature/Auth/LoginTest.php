<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class LoginTest extends TestCase
{
    /**
     * メールアドレスが未入力の場合はバリデーションメッセージが表示される(一般ユーザー)
     */
    public function test_user_login_shows_error_when_email_is_empty()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }


    /**
     * パスワードが未入力の場合はバリデーションメッセージが表示される(一般ユーザー)
     */
    public function test_user_login_shows_error_when_password_is_empty()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }


    /**
     * 入力情報が登録されていない場合はバリデーションメッセージが表示される(一般ユーザー)
     */
    public function test_user_login_shows_error_when_credentials_are_invalid()
    {
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }


    // ------------------------< 以降は独自テスト >---------------------------


    /**
     * 正しい情報が入力された場合はログイン処理が実行される(一般ユーザー)
     */
    public function test_it_logs_in_user_when_credentials_are_valid()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.create'));
        $this->assertAuthenticatedAs($user);
    }


    /**
     * ログアウト処理を実行できる(一般ユーザー)
     */
    public function test_it_logs_out_user()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->post(route('logout'));

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}

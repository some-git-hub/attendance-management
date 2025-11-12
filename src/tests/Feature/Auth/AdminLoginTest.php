<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AdminLoginTest extends TestCase
{
    /**
     * メールアドレスが未入力の場合はバリデーションメッセージが表示される
     */
    public function test_admin_login_shows_error_when_email_is_empty()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'adminpass',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }


    /**
     * パスワードが未入力の場合はバリデーションメッセージが表示される
     */
    public function test_admin_login_shows_error_when_password_is_empty()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }


    /**
     * 入力情報が登録されていない場合はバリデーションメッセージが表示される
     */
    public function test_admin_login_shows_error_when_credentials_are_invalid()
    {
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong_adminpass',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }


    // ------------------------< 以降は独自テスト >---------------------------



    /**
     * 正しい情報が入力された場合はログイン処理が実行される
     */
    public function test_it_logs_in_admin_when_credentials_are_valid()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpass',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertStatus(302);
        $response->assertRedirect(route('admin.attendance.list'));
        $this->assertAuthenticatedAs($admin);
    }


    /**
     * ログアウト処理を実行できる
     */
    public function test_it_logs_out_admin()
    {
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.logout'));

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}

<?php

namespace Tests\Feature\Auth;


use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Testing\Fakes\EventFake;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use Tests\TestCase;

class VerifyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::swap(new EventFake(app('events')));
        Notification::fake();
    }


    /**
     * ユーザー登録時に認証メールが送信される
     */
    public function test_user_registration_sends_verification_email()
    {
        $email = 'user'.uniqid().'@example.com';
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(302);

        $user = User::where('email', $email)->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }


    /**
     * 「認証はこちらから」を押すと認証サイトに遷移する
     */
    public function test_verification_link_redirects_to_mailhog()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $this->actingAs($user);

        $response = $this->get('/email/verify')->assertStatus(200);
        $crawler = new Crawler($response->getContent());
        $link = $crawler->filter('.verify-email__link-verification')->link();

        $this->assertEquals('http://localhost:8025/', $link->getUri());
    }


    /**
     * メール認証を完了すると勤怠登録画面に遷移する
     */
    public function test_email_verification_redirects_to_attendance_create_page()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('attendance.create'));
    }
}

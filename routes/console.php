<?php

use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('send-mail {to? : Email tujuan (default: MAIL_FROM_ADDRESS)}', function (?string $to = null) {
    $apiKey = (string) config('services.mailtrap-sdk.apiKey');
    $fromAddress = (string) config('mail.from.address');
    $fromName = (string) config('mail.from.name');
    $recipient = $to ?: $fromAddress;

    if ($apiKey === '') {
        $this->error('MAILTRAP_API_KEY belum diisi.');

        return self::FAILURE;
    }

    if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $this->error('Alamat email tujuan tidak valid.');

        return self::FAILURE;
    }

    if (! filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
        $this->error('MAIL_FROM_ADDRESS tidak valid.');

        return self::FAILURE;
    }

    $email = (new MailtrapEmail())
        ->from(new Address($fromAddress, $fromName ?: 'SurveyCenter'))
        ->to(new Address($recipient))
        ->subject('SurveyCenter Mailtrap Sending API Test')
        ->category('Production Integration Test')
        ->text('Test email berhasil dikirim melalui Mailtrap Sending API (production).')
    ;

    $response = MailtrapClient::initSendingEmails(
        apiKey: $apiKey
    )->send($email);

    $this->info('Email terkirim ke: '.$recipient);
    $this->line(json_encode(ResponseHelper::toArray($response), JSON_PRETTY_PRINT));
})->purpose('Send Mail');

use Illuminate\Support\Facades\Schedule;
Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
Schedule::command('sitemap:generate')->dailyAt('02:00')->withoutOverlapping();

// Auto-deactivate surveys when deadline has passed
Schedule::call(function () {
    \App\Models\Survey::where('status', \App\Models\Survey::STATUS_ACTIVE)
        ->whereNotNull('deadline')
        ->where('deadline', '<', now())
        ->update(['status' => \App\Models\Survey::STATUS_DRAFT]);
})->everyFiveMinutes()->name('auto-deactivate-expired-surveys')->withoutOverlapping();

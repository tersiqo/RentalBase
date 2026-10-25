<?php

namespace App\Mail;

use App\Models\ClientRegistration;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;
    public $user;
    public $businessName;
    public $adminName;
    public $subdomain;
    public $planName;
    public $loginUrl;
    public $storeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(ClientRegistration|User $registrationOrUser, ?User $user = null)
    {
        if ($registrationOrUser instanceof ClientRegistration) {
            $this->registration = $registrationOrUser;
            $this->user = $user ?? User::where('email', $registrationOrUser->admin_email)->first();
            $this->businessName = $registrationOrUser->business_name;
            $this->adminName = $registrationOrUser->admin_name;
            $this->subdomain = $registrationOrUser->subdomain;
            $this->planName = $registrationOrUser->plan_name;
        } else {
            $this->user = $registrationOrUser;
            $this->registration = ClientRegistration::where('admin_email', $registrationOrUser->email)->latest()->first();
            $this->businessName = $this->registration?->business_name ?? $registrationOrUser->client?->business_name ?? 'Toko Anda';
            $this->adminName = $registrationOrUser->name;
            $this->subdomain = $this->registration?->subdomain ?? $registrationOrUser->client?->subdomain ?? '';
            $this->planName = $this->registration?->plan_name ?? 'Starter';
        }

        $this->loginUrl = route('login');
        $this->storeUrl = !empty($this->subdomain) ? route('customer.home', ['subdomain' => $this->subdomain]) : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Toko ' . ($this->businessName ?: 'Tenant') . ' Telah Disetujui — RentalBase',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-approved',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('identifier');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('short_codes')->nullable();
            $table->string('email_type')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 inactive, 1 active');
            $table->timestamps();
        });

        $now  = now();

        $data = [
            [
                'subject'     => 'Email Confirmation',
                'identifier'  => 'Email Confirmation',
                'title'       => 'email_confirmation',
                'short_codes' => '{name},{email},{site_name},{confirmation_link}',
                'body'        => '<p>Hi {name},</p><p>Please confirm your email by clicking the link below:</p><p>{confirmation_link}</p><p><br></p><p>Thanks</p><p>{site_name}</p>',
                'email_type'  => 'email_confirmation',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'subject'     => 'Welcome Email',
                'identifier'  => 'Welcome Email',
                'title'       => 'welcome_email',
                'short_codes' => '{name},{email},{site_name},{login_link}',
                'body'        => 'Welcome to {site_name}',
                'email_type'  => 'welcome_email',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'subject'     => 'Password Reset Mail',
                'identifier'  => 'Password Reset Mail',
                'title'       => 'password_reset',
                'short_codes' => '{name},{email},{site_name},{reset_link},{otp}',
                'body'        => '<p>Hi {name}</p><p>Reset your password using below link: {reset_link}</p>',
                'email_type'  => 'password_reset',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'subject'     => 'Recovery Successful Mail',
                'identifier'  => 'Recovery Successful Mail',
                'title'       => 'recovery_success_mail',
                'short_codes' => '{name},{email},{site_name},{login_link}',
                'body'        => '<p>Hi {name}</p><p>Reset your password successful, Now Login: {login_link}</p>',
                'email_type'  => 'recovery_success_mail',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        EmailTemplate::insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_templates');
    }
};

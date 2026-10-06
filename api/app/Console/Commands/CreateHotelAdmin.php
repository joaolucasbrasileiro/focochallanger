<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

#[Signature('users:create-admin
    {hotel : ID interno do hotel}
    {email : E-mail do usuário}
    {--name= : Nome exigido ao criar um novo usuário}
    {--password= : Senha opcional; se omitida, será solicitada de forma segura}')]
#[Description('Cria ou vincula um usuário administrador a um hotel.')]
class CreateHotelAdmin extends Command
{
    public function handle(): int
    {
        $hotel = Hotel::query()->find($this->argument('hotel'));

        if ($hotel === null) {
            $this->error('O hotel informado não foi encontrado.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->where('email', $email)->first();
        $name = null;
        $password = null;

        if ($user === null) {
            $name = trim((string) ($this->option('name') ?: $this->ask('Nome do usuário')));
            $password = (string) ($this->option('password') ?: $this->secret('Senha do usuário'));
            $validator = Validator::make([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ], [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $this->error($message);
                }

                return self::FAILURE;
            }
        }

        $membership = DB::transaction(function () use ($email, $hotel, $name, $password, $user): HotelMembership {
            if ($user === null) {
                $user = User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'email_verified_at' => now(),
                ]);
            }

            return HotelMembership::query()->updateOrCreate(
                [
                    'hotel_id' => $hotel->id,
                    'user_id' => $user->id,
                ],
                ['role' => UserRole::Admin],
            );
        });

        $this->info("O usuário {$membership->user_id} agora é administrador do hotel {$hotel->id}.");

        return self::SUCCESS;
    }
}

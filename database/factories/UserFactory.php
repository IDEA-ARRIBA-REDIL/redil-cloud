<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // --- Campos Personalizados Requeridos ---
            'primer_nombre' => fake()->firstName(),
            'primer_apellido' => fake()->lastName(),
            'email' => 'student.test.' . fake()->unique()->uuid() . '@example.com',
            'password' => '12345678',
            'activo' => true,
            'tipo_usuario_id' => 1, // Asumimos 1 como un tipo de usuario 'alumno' o genérico. Ajústalo si es necesario.
            'foto' => 'default-m.png',

            // --- Campos Nulos o con Valor por Defecto ---
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'fecha_nacimiento' => fake()->dateTimeBetween('-40 years', '-18 years'),
            'genero' => fake()->numberBetween(0, 1),
            'telefono_movil' => fake()->e164PhoneNumber(),
            'sede_id' => 1, // Asignamos una sede por defecto
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
